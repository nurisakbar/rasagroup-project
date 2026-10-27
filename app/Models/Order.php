<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    const TYPE_REGULAR = 'regular';
    const TYPE_DISTRIBUTOR = 'distributor';
    const TYPE_POS = 'pos';
    const DISTRIBUTOR_POINTS_PER_ITEM = 5000;

    protected $keyType = 'string';
    public $incrementing = false;

    public bool $auto_approved_at_creation = false;

    protected $fillable = [
        'order_number',
        'user_id',
        'order_type',
        'address_id',
        'expedition_id',
        'expedition_service',
        'source_warehouse_id',
        'source_qad_location_code',
        'subtotal',
        'shipping_cost',
        'pakai_ppn',
        'total_amount',
        'shipping_address',
        'payment_method',
        'company',
        'payment_status',
        'paid_at',
        'order_status',
        'tracking_number',
        'ekspedisiku_shipment_id',
        'ekspedisiku_booking_attempt',
        'ekspedisiku_booking_reference',
        'ekspedisiku_booking_created_at',
        'ekspedisiku_booking_status',
        'ekspedisiku_booking_last_error',
        'ekspedisiku_pickup_requested_at',
        'ekspedisiku_pickup_status',
        'ekspedisiku_pickup_last_error',
        'shipped_at',
        'pickup_ready_at',
        'received_at',
        'pickup_note',
        'notes',
        'purchase_order_number',
        'purchase_order_document',
        'points_earned',
        'points_credited',
        'affiliate_id',
        'affiliate_points',
        'discount_percent',
        'discount_amount',
        'preferred_shipping_date',
        'payment_proof',
        'payment_submit_note',
        'payment_submitted_at',
        'finance_approved',
        'finance_approved_at',
        'finance_approved_by',
        'qad_so_number',
        'qid_sales_order_number',
        'jubelio_salesorder_id',
        'jubelio_salesorder_no',
        'sales_code',
        'qad_sync_history',
        'wms_so_status',
        'wms_so_synced_at',
        'wms_so_failure_reason',
        'zoho_salesorder_id',
        'zoho_invoice_id',
        'zoho_payment_id',
        'edms_notification_status',
        'edms_notified_at',
        'edms_notification_response',
        'edms_notification_error',
    ];

    protected $hidden = [
        'qad_sync_history',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'pakai_ppn' => 'boolean',
        'total_amount' => 'decimal:2',
        'points_credited' => 'boolean',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'pickup_ready_at' => 'datetime',
        'received_at' => 'datetime',
        'ekspedisiku_booking_created_at' => 'datetime',
        'ekspedisiku_pickup_requested_at' => 'datetime',
        'preferred_shipping_date' => 'date',
        'payment_submitted_at' => 'datetime',
        'finance_approved' => 'boolean',
        'finance_approved_at' => 'datetime',
        'edms_notified_at' => 'datetime',
        'qad_sync_history' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            // Pembayaran lunas → otomatis finance approved (1)
            if ($order->isDirty('payment_status') && $order->payment_status === 'paid' && !$order->finance_approved) {
                $order->finance_approved = true;
                if (empty($order->finance_approved_at)) {
                    $order->finance_approved_at = now();
                }
            }
        });

        static::creating(function (Order $order) {
            // Auto approve TOP jika AR Outstanding + TOP existing + order baru masih di bawah limit kredit
            if ($order->payment_method === 'term_of_payment') {
                if ($order->finance_approved) {
                    $order->payment_status = 'term_of_payment';
                    $order->auto_approved_at_creation = true;
                } else {
                    $user = $order->user ?? \App\Models\User::find($order->user_id);
                    if ($user && $user->credit_limit !== null) {
                        $accumulatedDebt = $user->getAccumulatedTopDebt();
                        if (($accumulatedDebt + (float)$order->total_amount) <= (float)$user->credit_limit) {
                            $order->finance_approved = true;
                            $order->finance_approved_at = now();
                            $order->finance_approved_by = null;
                            $order->payment_status = 'term_of_payment';
                            $order->auto_approved_at_creation = true; // Flag for after-creation notification
                        }
                    }
                }
            }
        });

        static::created(function (Order $order) {
            // Notifikasi eksternal otomatis untuk transaksi yang butuh approval finance
            if ($order->isAwaitingFinanceApproval() && config('services.finance_approval.enabled', true)) {
                \App\Jobs\NotifyExternalFinanceApprovalNeeded::dispatch($order);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function financeApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_approved_by');
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_code', 'sales_code')
                    ->where('role', User::ROLE_SALES ?? 'sales');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class)->withTrashed();
    }

    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->order_status) {
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'default',
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            'term_of_payment' => 'primary',
            default => 'default',
        };
    }

    /**
     * Check if the order payment time has expired (30 minutes).
     */
    public function isPaymentExpired(): bool
    {
        if ($this->payment_method === 'term_of_payment') {
            return false;
        }

        return $this->payment_status === 'pending' && $this->created_at->addMinutes(30)->isPast();
    }

    public function getBuyerStatusLabelAttribute(): string
    {
        if ($this->order_status === 'pending') {
            if ($this->payment_method === 'term_of_payment') {
                return $this->finance_approved ? 'Menunggu Diproses' : 'Menunggu Konfirmasi Finance';
            }

            if ($this->payment_status === 'paid') {
                return 'Menunggu Diproses';
            }

            return $this->payment_proof ? 'Menunggu Pembayaran Diverifikasi' : 'Menunggu Pembayaran';
        }

        return match ($this->order_status) {
            'processing' => 'Sedang Diproses',
            'shipped' => 'Dalam Pengiriman',
            'delivered', 'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->order_status),
        };
    }

    public function getBuyerStatusClassAttribute(): string
    {
        if ($this->order_status === 'pending') {
            if ($this->payment_method === 'term_of_payment') {
                return $this->finance_approved ? 'bg-info' : 'bg-warning';
            }

            return $this->payment_status === 'paid' ? 'bg-info' : 'bg-warning';
        }

        return match ($this->order_status) {
            'processing' => 'bg-info',
            'shipped' => 'bg-primary',
            'delivered', 'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getBuyerPaymentStatusLabelAttribute(): string
    {
        if ($this->payment_method === 'term_of_payment') {
            if ($this->payment_status === 'paid') {
                return 'Lunas';
            }
            return $this->finance_approved ? 'Disetujui Finance' : 'Menunggu Konfirmasi Finance';
        }

        return match ($this->payment_status) {
            'paid' => 'Lunas',
            'pending' => $this->payment_proof ? 'Verifikasi Pembayaran' : 'Menunggu Pembayaran',
            'failed' => 'Gagal',
            'refunded' => 'Refund',
            default => ucwords(str_replace('_', ' ', (string) $this->payment_status)),
        };
    }

    public function getBuyerPaymentStatusClassAttribute(): string
    {
        if ($this->payment_method === 'term_of_payment') {
            if ($this->payment_status === 'paid') {
                return 'bg-success';
            }
            return $this->finance_approved ? 'bg-success' : 'bg-warning';
        }

        return match ($this->payment_status) {
            'paid' => 'bg-success',
            'pending' => 'bg-warning',
            'failed' => 'bg-danger',
            'refunded' => 'bg-info',
            default => 'bg-secondary',
        };
    }

    /**
     * TOP order menunggu persetujuan finance (finance_approved = 0).
     */
    public function isAwaitingFinanceApproval(): bool
    {
        return $this->payment_method === 'term_of_payment' && !$this->finance_approved;
    }

    /**
     * Order sudah boleh diproses hub (finance_approved = 1).
     */
    public function isReleasedToHub(): bool
    {
        return (bool) $this->finance_approved;
    }

    /**
     * Label status approval finance (0 / 1).
     */
    public function financeApprovalLabel(): string
    {
        return $this->finance_approved ? '1' : '0';
    }

    /**
     * Cek apakah ekspedisi pesanan adalah Ambil di Tempat (Self Pickup)
     */
    public function isSelfPickup(): bool
    {
        $code = strtolower($this->expedition?->code ?? '');
        $name = strtolower($this->expedition?->name ?? '');
        $service = strtolower($this->expedition_service ?? '');

        return in_array($code, ['self_pickup', 'pickup'], true)
            || str_contains($name, 'ambil')
            || str_contains($service, 'ambil')
            || str_contains($name, 'pickup')
            || str_contains($service, 'pickup');
    }

    /**
     * Cek apakah ekspedisi pesanan adalah Antar di Tempat (Kurir Toko)
     */
    public function isKurirToko(): bool
    {
        $code = strtolower($this->expedition?->code ?? '');
        $name = strtolower($this->expedition?->name ?? '');
        $service = strtolower($this->expedition_service ?? '');

        return in_array($code, ['kurir_toko', 'antar_tempat', 'internal'], true)
            || str_contains($name, 'antar')
            || str_contains($service, 'antar');
    }

    /**
     * Cek apakah status pembayaran sudah lunas atau TOP sudah diterima / disetujui finance
     */
    public function isPaidOrTopApproved(): bool
    {
        if ($this->payment_status === 'paid') {
            return true;
        }

        if ($this->payment_method === 'term_of_payment' && (bool) $this->finance_approved) {
            return true;
        }

        if ($this->payment_status === 'term_of_payment' && (bool) $this->finance_approved) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah pembeli dapat melakukan konfirmasi barang diterima
     */
    public function canConfirmReceipt(): bool
    {
        if (in_array($this->order_status, ['completed', 'cancelled'], true)) {
            return false;
        }

        // Jika expedisi ambil di tempat atau antar di tempat, dan pembayaran sudah bayar atau TOP sudah diterima
        if (($this->isSelfPickup() || $this->isKurirToko()) && $this->isPaidOrTopApproved()) {
            return true;
        }

        // Default: untuk kurir umum jika sudah dikirim atau status shipped / delivered
        return !empty($this->shipped_at) || in_array($this->order_status, ['shipped', 'delivered'], true);
    }

    public function getFormattedPaymentMethodAttribute()
    {
        if (empty($this->payment_method)) return '-';
        
        if (str_starts_with($this->payment_method, 'faspay_')) {
            $method = str_replace('faspay_', '', $this->payment_method);
            if (str_ends_with($method, '_va')) {
                $bank = strtoupper(str_replace('_va', '', $method));
                if (in_array($bank, ['MANDIRI', 'DANAMON', 'SINARMAS', 'MAYBANK', 'PERMATA'])) {
                    $bank = ucfirst(strtolower($bank));
                }
                if ($bank == 'CIMB') {
                    $bank = 'CIMB Niaga';
                }
                return $bank . ' Virtual Account';
            } elseif ($method === 'qris') {
                return 'QRIS';
            } elseif ($method === 'direct_debit') {
                return 'Direct Debit';
            }
        }
        
        if ($this->payment_method === 'term_of_payment') {
            return 'Term Of Payment (TOP)';
        }
        
        return ucwords(str_replace('_', ' ', $this->payment_method));
    }

    /**
     * Resolve the direct image URL for QRIS payment (e.g. from Faspay).
     */
    public function getQrisImageUrlAttribute(): ?string
    {
        if ($this->payment_method !== 'faspay_qris') {
            return null;
        }

        $url = $this->virtual_account_no;
        if (empty($url)) {
            return null;
        }

        // Already a direct image PNG/JPG URL
        if (str_contains($url, '__assets/qr/') || preg_match('/\.(png|jpg|jpeg|svg|webp)(\?.*)?$/i', $url)) {
            return $url;
        }

        $parts = parse_url($url);

        // 1. Check query parameters: ?trx_id=...&merchant_id=...
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
            $merchantId = $query['merchant_id'] ?? null;
            $trxId = $query['trx_id'] ?? null;
            if ($merchantId && $trxId) {
                return "https://web.faspay.co.id/__assets/qr/linkaja/{$merchantId}-{$trxId}.png";
            }
        }

        // 2. Check base64 encoded JSON in the path (e.g. /eyJ0cnhfaWQi...)
        if (!empty($parts['path'])) {
            $segments = explode('/', trim($parts['path'], '/'));
            $lastSegment = end($segments);
            if ($lastSegment) {
                $decoded = json_decode(@base64_decode($lastSegment), true);
                if (is_array($decoded) && !empty($decoded['merchant_id']) && !empty($decoded['trx_id'])) {
                    return "https://web.faspay.co.id/__assets/qr/linkaja/{$decoded['merchant_id']}-{$decoded['trx_id']}.png";
                }
            }
        }

        return $url;
    }

    /**
     * Approve finance (set finance_approved = 1) and release order to hub.
     *
     * @return array{success: bool, message: string}
     */
    public function approveFinanceBy(?User $approver = null): array
    {
        if ($this->finance_approved) {
            return [
                'success' => false,
                'message' => 'Transaksi sudah di-approve finance sebelumnya.',
            ];
        }

        $updateData = [
            'finance_approved' => true,
            'finance_approved_at' => now(),
            'finance_approved_by' => $approver?->id,
        ];

        // TOP: set payment_status ke term_of_payment saat approve
        if ($this->payment_method === 'term_of_payment' && $this->payment_status === 'pending') {
            $updateData['payment_status'] = 'term_of_payment';
        }

        $this->update($updateData);
        $this->notifyHubAfterFinanceApproval();

        return [
            'success' => true,
            'message' => 'Finance approval berhasil. Transaksi siap diproses hub.',
        ];
    }

    /**
     * Notifikasi hub + sync setelah finance approve.
     */
    public function notifyHubAfterFinanceApproval(): void
    {
        \App\Jobs\SendWhatsAppNotification::dispatch($this, 'warehouse_notification');

        $this->loadMissing('sourceWarehouse.users');

        if ($this->sourceWarehouse) {
            $staffMembers = $this->sourceWarehouse->users;
            if ($staffMembers && $staffMembers->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send(
                    $staffMembers,
                    new \App\Notifications\Orders\NewTopOrderNotification($this)
                );
            }
        }

        $this->syncSalesOrdersToQadAndWms();
    }

    /**
     * Buat sales order di QAD dulu, lalu kirim ke WMS.
     * Dijalankan langsung (sync) agar tidak bergantung queue worker.
     */
    public function syncSalesOrdersToQadAndWms(): void
    {
        $this->refresh();

        try {
            if (\App\Support\QadIntegration::isConfigured() && $this->shouldSyncToQad()) {
                \App\Jobs\SyncOrderToQad::dispatchSync($this);
                $this->refresh();
            } else {
                \Illuminate\Support\Facades\Log::info('Order sync: skip QAD SO', [
                    'order_id' => $this->id,
                    'order_type' => $this->order_type,
                    'qad_configured' => \App\Support\QadIntegration::isConfigured(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Order sync: QAD SO failed', [
                'order_id' => $this->id,
                'order_number' => $this->order_number,
                'message' => $e->getMessage(),
            ]);
        }

        try {
            \App\Jobs\SendSalesOrderToWmsJob::dispatch($this);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Order sync: WMS SO failed', [
                'order_id' => $this->id,
                'order_number' => $this->order_number,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if order is online (regular order).
     */
    public function isOnline(): bool
    {
        return $this->order_type === self::TYPE_REGULAR;
    }

    /**
     * Check if order is offline (POS order).
     */
    public function isOffline(): bool
    {
        return $this->order_type === self::TYPE_POS;
    }

    /**
     * Hub sumber kode lokasi FG… → sales order ke QAD.
     */
    public function sourceHubSendsSalesOrdersToQad(): bool
    {
        $this->loadMissing('sourceWarehouse');

        return (bool) $this->sourceWarehouse?->hasQadLocationCode();
    }

    /**
     * Yang dikirim ke Jubelio hanya pesanan dari gudang Ecommerce dan Hub Bekasi, selain itu tidak.
     */
    public function shouldSyncToJubelio(): bool
    {
        $this->loadMissing('sourceWarehouse');
        $warehouse = $this->sourceWarehouse;

        if (! $warehouse) {
            return false;
        }

        $isBekasi = stripos($warehouse->name, 'bekasi') !== false
            || $warehouse->qad_location_code === 'FG008'
            || $warehouse->kode_hub === 'WHRG002'
            || $warehouse->kode_hub === 'FG008';

        $isEcomm = stripos($warehouse->name, 'ecomm') !== false
            || stripos($warehouse->name, 'ecommerce') !== false
            || stripos((string) $warehouse->kode_hub, 'ecomm') !== false
            || in_array('ecommerce', (array) ($warehouse->target_role ?? []), true);

        return $isBekasi || $isEcomm;
    }

    /**
     * Hub kode lokasi FG… → sales order ke QAD.
     */
    public function shouldSyncToQad(): bool
    {
        return $this->sourceHubSendsSalesOrdersToQad();
    }

    /**
     * Resolves the customer code used in QAD for this order.
     * Orders fulfilled by Hub Bekasi (FG008) use the generic customer code HUB00002.
     */
    public function resolveQadCustomerCode(): ?string
    {
        $warehouse = $this->sourceWarehouse;
        if ($warehouse && (stripos($warehouse->name, 'bekasi') !== false || $warehouse->qad_location_code === 'FG008')) {
            return 'HUB00002';
        }

        return $this->user?->qad_customer_code ?: null;
    }

    /**
     * SO QAD yang belum terbentuk setelah order dilepas ke hub.
     */
    public function scopeNeedingQadSalesOrderRetry($query)
    {
        return $query
            ->where('finance_approved', true)
            ->where('order_status', '!=', 'cancelled')
            ->whereNull('qad_so_number')
            ->whereHas('sourceWarehouse', function ($q) {
                $q->whereRaw("UPPER(TRIM(COALESCE(qad_location_code, ''))) LIKE 'FG%'");
            })
            ->where(function ($q) {
                $q->whereNull('qad_sync_history')
                  // Satu kali job jalan bisa mencatat 1-2 log failed.
                  // Batas < 10 artinya maksimal sekitar 5-6 kali job retry sebelum dihentikan.
                  ->orWhereRaw("JSON_LENGTH(qad_sync_history) < 10");
            });
    }

    /**
     * SO WMS yang gagal atau belum pernah terkirim setelah order dilepas ke hub.
     */
    public function scopeNeedingWmsSalesOrderRetry($query)
    {
        return $query
            ->where('finance_approved', true)
            ->where('order_status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereNull('wms_so_status')
                    ->orWhereRaw('UPPER(wms_so_status) = ?', ['FAILED']);
            });
    }

    /**
     * Snapshot PPN pesanan: YA → isTaxable true di QAD.
     */
    public function usesPpn(): bool
    {
        return $this->pakai_ppn !== false;
    }

    /**
     * Get order type label.
     */
    public function getOrderTypeLabelAttribute(): string
    {
        return match ($this->order_type) {
            self::TYPE_REGULAR => 'Online',
            self::TYPE_POS => 'Offline (POS)',
            self::TYPE_DISTRIBUTOR => 'Distributor',
            default => ucfirst($this->order_type),
        };
    }

    /**
     * Get order type badge class.
     */
    public function getOrderTypeBadgeClassAttribute(): string
    {
        return match ($this->order_type) {
            self::TYPE_REGULAR => 'label-primary',
            self::TYPE_POS => 'label-info',
            self::TYPE_DISTRIBUTOR => 'label-warning',
            default => 'label-default',
        };
    }
    /**
     * Credit earned points to the buyer (DRiiPPreneur) and the affiliate.
     */
    public function creditPoints()
    {
        if ($this->points_credited) {
            return false;
        }

        $credited = false;

        // 1. Credit DRiiPPreneur points (for regular orders)
        if ($this->order_type === self::TYPE_REGULAR && $this->points_earned > 0) {
            $user = $this->user;
            if ($user && $user->isDriippreneurApproved()) {
                $user->increment('points', $this->points_earned);
                $credited = true;
            }
        }

        // 2. Credit Distributor points (for distributor orders)
        if ($this->order_type === self::TYPE_DISTRIBUTOR && $this->points_earned > 0) {
            $user = $this->user;
            if ($user) {
                $user->increment('points', $this->points_earned);
                $credited = true;
            }
        }

        // 3. Credit Affiliate points
        if ($this->affiliate_id && $this->affiliate_points > 0) {
            $affiliate = User::find($this->affiliate_id);
            if ($affiliate) {
                $affiliate->increment('points', $this->affiliate_points);
                $credited = true;
            }
        }

        if ($credited || ($this->points_earned == 0 && $this->affiliate_points == 0)) {
            $this->update(['points_credited' => true]);
            return true;
        }

        return false;
    }
}
