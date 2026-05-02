<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Bandingkan payload create SO dari sync order dengan format referensi (qid:test-so-format).
 */
class CompareQidSalesOrderPayload extends Command
{
    protected $signature = 'qid:compare-so-payload
        {actual_json_file : File JSON berisi object payload (satu root object)}
        {--customer= : bill/sold/ship (default ZH35531 atau config test_so_customer)}
        {--item= : Kode item (default FDA010-GV01 atau config test_so_item)}
        {--ws= : salesOrderNumber}
        {--date= : Tanggal order Y-m-d}
        {--po= : purchaseOrderNumber}
        {--remarks= : remarks header}
        {--reference-uom=PK : unitOfMeasure baris referensi (uji standar pakai PK)}';

    protected $description = 'Diff payload sync vs referensi terbukti (struktur TestQidSalesOrderFormat)';

    public function handle(): int
    {
        $path = $this->argument('actual_json_file');
        if (! is_readable($path)) {
            $this->error("File tidak ada atau tidak bisa dibaca: {$path}");

            return self::FAILURE;
        }

        $raw = file_get_contents($path);
        $actual = json_decode($raw, true);
        if (! is_array($actual)) {
            $this->error('JSON tidak valid atau bukan object/array.');

            return self::FAILURE;
        }

        $customer = (string) ($this->option('customer') ?: 'ZH35531');
        $item = (string) ($this->option('item') ?: 'FDA010-GV01');
        $ws = (string) ($this->option('ws') ?: 'WS000005');
        $day = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : Carbon::parse('2026-05-02')->startOfDay();
        $dateIso = $day->format('Y-m-d') . 'T00:00:00.000Z';
        $lineDueIso = $day->copy()->addDays(7)->format('Y-m-d') . 'T00:00:00.000Z';
        $remarks = (string) ($this->option('remarks') ?: 'WS000005');
        $po = (string) ($this->option('po') ?: '2605022198');
        $refUom = (string) ($this->option('reference-uom') ?: 'PK');

        $reference = [
            'domainCode' => 'MCR',
            'salesOrderNumber' => $ws,
            'billToCustomerCode' => $customer,
            'soldToCustomerCode' => $customer,
            'shipToCustomerCode' => $customer,
            'orderDate' => $dateIso,
            'dueDate' => $dateIso,
            'requiredDate' => $dateIso,
            'shipDate' => $dateIso,
            'promiseDate' => $dateIso,
            'creditTermsCode' => 'CIA',
            'remarks' => $remarks,
            'purchaseOrderNumber' => $po,
            'taxClass' => 'PPN',
            'isTaxable' => true,
            'salespersonCode_01' => 'SLS00001',
            'isSelfBillingEnabled' => true,
            'salesOrderLines' => [
                [
                    'salesOrderNumber' => $ws,
                    'salesOrderLine' => 1,
                    'itemCode' => $item,
                    'quantityOrdered' => 1,
                    'unitOfMeasure' => $refUom,
                    'listPrice' => 111000,
                    'discountPercent' => 0,
                    'netPrice' => 111000,
                    'dueDate' => $lineDueIso,
                    'isTaxable' => true,
                    'salesAcct' => '41101',
                    'salesCC' => '',
                    'discountAcct' => '41101',
                    'discountCC' => '',
                ],
            ],
        ];

        $this->info('Referensi = format qid:test-so-format (proven minimal).');
        $this->newLine();
        $this->line('<fg=cyan>--- REFERENCE ---</>');
        $this->line(json_encode($reference, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->line('<fg=cyan>--- ACTUAL (file) ---</>');
        $this->line(json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $diffs = $this->deepDiff($reference, $actual, '');
        $this->newLine();
        if ($diffs === []) {
            $this->info('Tidak ada perbedaan struktur/nilai antara referensi dan actual.');

            return self::SUCCESS;
        }

        $this->warn('Perbedaan (' . count($diffs) . '):');
        foreach ($diffs as $line) {
            $this->line('  • ' . $line);
        }

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    protected function deepDiff(mixed $ref, mixed $act, string $path): array
    {
        $out = [];

        if (is_array($ref) && $this->isAssoc($ref)) {
            if (! is_array($act) || ! $this->isAssoc($act)) {
                return ["{$path}: type mismatch (expected object, got " . gettype($act) . ')'];
            }
            foreach ($ref as $k => $v) {
                $p = $path === '' ? (string) $k : "{$path}.{$k}";
                if (! array_key_exists($k, $act)) {
                    $out[] = "{$p}: missing in actual";
                    continue;
                }
                $out = array_merge($out, $this->deepDiff($v, $act[$k], $p));
            }
            foreach ($act as $k => $v) {
                if (! array_key_exists($k, $ref)) {
                    $p = $path === '' ? (string) $k : "{$path}.{$k}";
                    $out[] = "{$p}: missing in reference (extra in actual)";
                }
            }

            return $out;
        }

        if (is_array($ref) && array_is_list($ref)) {
            if (! is_array($act) || ! array_is_list($act)) {
                return ["{$path}: type mismatch (expected list, got " . gettype($act) . ')'];
            }
            $n = max(count($ref), count($act));
            for ($i = 0; $i < $n; $i++) {
                $p = "{$path}[{$i}]";
                if (! isset($ref[$i])) {
                    $out[] = "{$p}: missing in reference";
                    continue;
                }
                if (! isset($act[$i])) {
                    $out[] = "{$p}: missing in actual";
                    continue;
                }
                $out = array_merge($out, $this->deepDiff($ref[$i], $act[$i], $p));
            }

            return $out;
        }

        if ($this->scalarEquals($ref, $act)) {
            return [];
        }

        return [
            "{$path}: reference=" . json_encode($ref) . ' actual=' . json_encode($act),
        ];
    }

    protected function isAssoc(array $a): bool
    {
        return array_keys($a) !== range(0, count($a) - 1);
    }

    protected function scalarEquals(mixed $ref, mixed $act): bool
    {
        if ($ref === $act) {
            return true;
        }
        if (is_numeric($ref) && is_numeric($act) && (float) $ref == (float) $act) {
            return true;
        }

        return false;
    }
}
