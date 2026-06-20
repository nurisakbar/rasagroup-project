<?php

namespace App\Support;

class JubelioOrderStatusMapper
{
    /** @var array<string, int> */
    private const STATUS_RANK = [
        'pending' => 0,
        'processing' => 1,
        'shipped' => 2,
        'delivered' => 3,
        'completed' => 4,
    ];

    /**
     * @param  array<string, mixed>  $jubelioOrder
     * @return array{
     *     order_status: ?string,
     *     tracking_number: ?string,
     *     shipped_at: bool,
     *     credit_points: bool,
     *     jubelio_internal_status: ?string,
     *     jubelio_channel_status: ?string
     * }
     */
    public function map(array $jubelioOrder): array
    {
        $internalStatus = strtoupper(trim((string) ($jubelioOrder['internal_status'] ?? '')));
        $channelStatus = trim((string) ($jubelioOrder['channel_status'] ?? ''));
        $isCanceled = (bool) ($jubelioOrder['is_canceled'] ?? false);
        $isShipped = (bool) ($jubelioOrder['is_shipped'] ?? false);
        $markedComplete = filter_var($jubelioOrder['marked_as_complete'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $tracking = $this->extractTrackingNumber($jubelioOrder);
        $receivedDate = $jubelioOrder['received_date'] ?? null;

        $orderStatus = null;
        $shippedAt = false;
        $creditPoints = false;

        if ($isCanceled || str_contains($internalStatus, 'CANCEL')) {
            $orderStatus = 'cancelled';
        } elseif ($markedComplete || str_contains($internalStatus, 'COMPLET')) {
            $orderStatus = 'completed';
            $creditPoints = true;
        } elseif ($receivedDate || str_contains($internalStatus, 'DELIVER')) {
            $orderStatus = 'delivered';
        } elseif ($isShipped || $tracking !== null || str_contains($internalStatus, 'SHIP')) {
            $orderStatus = 'shipped';
            $shippedAt = true;
        } elseif ($internalStatus !== '' || ($jubelioOrder['status'] ?? null)) {
            $orderStatus = 'processing';
        }

        return [
            'order_status' => $orderStatus,
            'tracking_number' => $tracking,
            'shipped_at' => $shippedAt,
            'credit_points' => $creditPoints,
            'jubelio_internal_status' => $internalStatus !== '' ? $internalStatus : null,
            'jubelio_channel_status' => $channelStatus !== '' ? $channelStatus : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $mapped
     */
    public function shouldApplyStatus(string $currentStatus, array $mapped): bool
    {
        $nextStatus = $mapped['order_status'] ?? null;
        if (! $nextStatus) {
            return false;
        }

        if ($nextStatus === 'cancelled') {
            return $currentStatus !== 'cancelled';
        }

        if ($currentStatus === 'cancelled') {
            return false;
        }

        $currentRank = self::STATUS_RANK[$currentStatus] ?? 0;
        $nextRank = self::STATUS_RANK[$nextStatus] ?? 0;

        return $nextRank > $currentRank;
    }

    /**
     * @param  array<string, mixed>  $jubelioOrder
     */
    private function extractTrackingNumber(array $jubelioOrder): ?string
    {
        foreach (['tracking_no', 'tracking_number'] as $key) {
            $value = $jubelioOrder[$key] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $tracking = trim((string) $value);
            if ($tracking !== '' && $tracking !== '0') {
                return $tracking;
            }
        }

        return null;
    }
}
