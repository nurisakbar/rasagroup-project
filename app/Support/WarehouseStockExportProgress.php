<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WarehouseStockExportProgress
{
    private const TTL_SECONDS = 3600; // 1 hour

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly string $id,
        private array $data
    ) {}

    public static function create(string $warehouseId, ?string $userId = null, array $filters = []): self
    {
        $id = (string) Str::uuid();
        $data = [
            'id' => $id,
            'warehouse_id' => $warehouseId,
            'user_id' => $userId,
            'filters' => $filters,
            'status' => 'queued',
            'progress' => 0,
            'message' => 'Menyiapkan proses export...',
            'file_url' => null,
            'file_name' => null,
            'error' => null,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
            'finished_at' => null,
        ];

        Cache::put(self::cacheKey($id), $data, self::TTL_SECONDS);

        return new self($id, $data);
    }

    public static function find(string $id): ?self
    {
        $data = Cache::get(self::cacheKey($id));

        return is_array($data) ? new self($id, $data) : null;
    }

    private static function cacheKey(string $id): string
    {
        return 'warehouse_stock_export_progress:' . $id;
    }

    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function update(int $progress, string $message): void
    {
        $this->persist([
            'status' => 'running',
            'progress' => min(100, max(0, $progress)),
            'message' => $message,
        ]);
    }

    public function complete(string $fileUrl, string $fileName, string $message = 'File Excel siap diunduh!'): void
    {
        $this->persist([
            'status' => 'completed',
            'progress' => 100,
            'message' => $message,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
            'finished_at' => now()->toIso8601String(),
        ]);
    }

    public function fail(string $error): void
    {
        $this->persist([
            'status' => 'failed',
            'message' => 'Export gagal',
            'error' => $error,
            'finished_at' => now()->toIso8601String(),
        ]);
    }

    private function persist(array $updates): void
    {
        $this->data = array_merge($this->data, $updates, [
            'updated_at' => now()->toIso8601String(),
        ]);

        Cache::put(self::cacheKey($this->id), $this->data, self::TTL_SECONDS);
    }
}
