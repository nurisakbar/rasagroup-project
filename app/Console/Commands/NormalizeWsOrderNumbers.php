<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Support\QadWsOrderNumberGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class NormalizeWsOrderNumbers extends Command
{
    protected $signature = 'orders:normalize-ws-numbers
                            {--dry-run : Hanya menampilkan perubahan tanpa menyimpan}
                            {--skip-xendit : Lewati pesanan yang punya xendit_invoice_id (fallback webhook external_id tidak dipakai)}';

    protected $description = 'Ubah semua order_number non-WS ke format WS###### dan selaraskan qid_sales_order_number';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $skipXendit = (bool) $this->option('skip-xendit');

        $seconds = max(120, (int) config('qidapi.ws_order_number_lock_seconds', 15) * 10);

        return (int) Cache::lock(QadWsOrderNumberGenerator::LOCK_KEY, $seconds)->block($seconds, function () use ($dry, $skipXendit): int {
            return $this->runLocked($dry, $skipXendit);
        });
    }

    private function runLocked(bool $dry, bool $skipXendit): int
    {
        $wsPattern = '/^WS\d{6}$/';

        $fixQid = 0;
        $renumber = 0;
        $skipped = 0;

        $m = QadWsOrderNumberGenerator::currentMaxSequence();

        $orders = Order::query()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        foreach ($orders as $order) {
            $num = (string) $order->order_number;
            $isWs = (bool) preg_match($wsPattern, $num);

            if ($isWs) {
                $qid = (string) ($order->qid_sales_order_number ?? '');
                if ($qid === '' || $qid !== $num) {
                    $this->line("[qid] {$order->id} order_number={$num} qid_was=" . ($qid === '' ? '(null)' : $qid));
                    if (! $dry) {
                        $order->update(['qid_sales_order_number' => $num]);
                    }
                    $fixQid++;
                }

                continue;
            }

            if ($skipXendit && $order->xendit_invoice_id) {
                $this->warn("[skip xendit] {$order->id} {$num}");
                $skipped++;

                continue;
            }

            $m++;
            if ($m > 999999) {
                $this->error('Urutan WS melampaui 999999. Sesuaikan data secara manual.');

                return self::FAILURE;
            }

            $new = 'WS' . str_pad((string) $m, 6, '0', STR_PAD_LEFT);
            $this->line("[renumber] {$order->id} {$num} -> {$new}");

            if (! $dry) {
                $order->update([
                    'order_number' => $new,
                    'qid_sales_order_number' => $new,
                ]);
            }
            $renumber++;
        }

        $this->info("Selesai. qid diselaraskan: {$fixQid}, nomor diubah ke WS: {$renumber}, dilewati: {$skipped}");

        if ($skipped > 0) {
            $this->comment('Hapus --skip-xendit untuk menormalisasi sisa pesanan (webhook Xendit tetap cocok lewat xendit_invoice_id).');
        }

        return self::SUCCESS;
    }
}
