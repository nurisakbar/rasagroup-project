<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Support\QadWsOrderNumberGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class NormalizeWsOrderNumbers extends Command
{
    protected $signature = 'orders:normalize-ws-numbers
                            {--dry-run : Lakukan simulasi tanpa update ke database}';

    protected $description = 'Ubah semua order_number non-WS ke format WS###### dan selaraskan qid_sales_order_number';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        // Prevent concurrent execution
        $seconds = 60 * 10;
        return (int) Cache::lock(QadWsOrderNumberGenerator::LOCK_KEY, $seconds)->block($seconds, function () use ($dry): int {
            return $this->runLocked($dry);
        });
    }

    private function runLocked(bool $dry): int
    {
        $wsPattern = '/^WS\d{6}$/';

        $fixQid = 0;
        $renumber = 0;

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

        $this->info("Selesai. qid diselaraskan: {$fixQid}, nomor diubah ke WS: {$renumber}");

        return self::SUCCESS;
    }
}
