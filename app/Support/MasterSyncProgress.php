<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MasterSyncProgress
{
    private const TTL_SECONDS = 86400;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private readonly string $id,
        private array $data
    ) {}

    public static function create(string $type, ?string $userId = null): self
    {
        $id = (string) Str::uuid();
        $data = [
            'id' => $id,
            'type' => $type,
            'status' => 'queued',
            'progress' => 0,
            'message' => 'Menunggu antrian...',
            'summary' => null,
            'error' => null,
            'user_id' => $userId,
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

    public function markRunning(string $message = 'Memulai sinkronisasi...'): void
    {
        $this->persist([
            'status' => 'running',
            'progress' => 0,
            'message' => $message,
        ]);
    }

    public function update(int $progress, string $message): void
    {
        $this->persist([
            'status' => 'running',
            'progress' => min(100, max(0, $progress)),
            'message' => $message,
        ]);
    }

    public function complete(string $summary, string $message = 'Selesai'): void
    {
        $this->persist([
            'status' => 'completed',
            'progress' => 100,
            'message' => $message,
            'summary' => $summary,
            'finished_at' => now()->toIso8601String(),
        ]);
    }

    public function fail(string $error): void
    {
        $this->persist([
            'status' => 'failed',
            'message' => 'Gagal',
            'error' => $error,
            'finished_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private function persist(array $changes): void
    {
        $this->data = array_merge($this->data, $changes, [
            'updated_at' => now()->toIso8601String(),
        ]);

        Cache::put(self::cacheKey($this->id), $this->data, self::TTL_SECONDS);
    }

    private static function cacheKey(string $id): string
    {
        return 'master_sync:' . $id;
    }
}
