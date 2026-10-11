<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalQrisTransaction extends Model
{
    use HasFactory;

    protected $table = 'external_qris_transactions';

    protected $fillable = [
        'reference_no',
        'faspay_reference_no',
        'company',
        'channel_code',
        'amount',
        'status',
        'qr_image_url',
        'qr_url',
        'callback_url',
        'callback_token',
        'callback_status',
        'callback_http_status',
        'callback_response_body',
        'callback_sent_at',
        'callback_attempts',
        'customer_name',
        'customer_phone',
        'description',
        'paid_at',
        'expires_at',
        'raw_generate_response',
        'raw_notification_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_generate_response' => 'array',
        'raw_notification_payload' => 'array',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'callback_sent_at' => 'datetime',
        'callback_attempts' => 'integer',
        'callback_http_status' => 'integer',
    ];

    /**
     * Check if payment is already marked as paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if payment is still pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark transaction as paid and save.
     */
    public function markAsPaid(?array $rawPayload = null): self
    {
        $this->status = 'paid';
        if (!$this->paid_at) {
            $this->paid_at = now();
        }
        if ($rawPayload) {
            $this->raw_notification_payload = $rawPayload;
        }
        $this->save();

        return $this;
    }

    /**
     * Record outcome of webhook dispatch.
     */
    public function recordCallbackResult(bool $success, ?int $httpStatus, ?string $responseBody): self
    {
        $this->callback_status = $success ? 'delivered' : 'failed';
        $this->callback_http_status = $httpStatus;
        $this->callback_response_body = $responseBody ? substr($responseBody, 0, 2000) : null;
        $this->callback_sent_at = now();
        $this->callback_attempts = ($this->callback_attempts ?? 0) + 1;
        $this->save();

        return $this;
    }

    /**
     * Format payload to be sent to 3rd party webhook endpoint.
     */
    public function toWebhookPayload(): array
    {
        return [
            'event' => 'payment.verified',
            'status' => $this->status,
            'reference_no' => $this->reference_no,
            'faspay_reference_no' => $this->faspay_reference_no,
            'amount' => (float) $this->amount,
            'currency' => 'IDR',
            'company' => $this->company,
            'channel_code' => $this->channel_code,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'description' => $this->description,
            'paid_at' => $this->paid_at ? $this->paid_at->toIso8601String() : null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'raw_notification' => $this->raw_notification_payload,
        ];
    }
}
