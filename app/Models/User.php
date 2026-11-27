<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOperationalHours;

class User extends Authenticatable implements MustVerifyEmail
{
    /** Cache per instance untuk hasOwnSalesCode(). */
    protected ?bool $hasOwnSalesCodeCache = null;

    use HasFactory, Notifiable, HasUuids, HasOperationalHours, SoftDeletes;

    const ROLE_ECOM = 'ecom';
    const ROLE_SUBDIST = 'subdist';
    const ROLE_RESELLER = 'reseller';
    const ROLE_AGENT = 'agent';
    const ROLE_WAREHOUSE = 'warehouse';
    const ROLE_DRIIPPRENEUR = 'driippreneur';
    const ROLE_AFFILIATOR = 'affiliator';
    const ROLE_DISTRIBUTOR = 'distributor';
    const DEFAULT_SHELF_LIFE_MONTHS = 9;
    const ROLE_OUTLET = 'outlet';
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_SALES = 'sales';

    const DISTRIBUTOR_SALES_CODE_PREFIX = 'DST';
    const ROLE_ECOMMERCE = 'ecommerce';
    const ROLE_BRAND_MARKETING = 'brand_marketing';
    const ROLE_FINANCE = 'finance';
    const ROLE_CUSTOMER_SERVICE = 'customer_service';
    const ROLE_IT_APPLICATION = 'it_application';
    const ROLE_INVENTORY_MANAGER = 'inventory_manager';

    /**
     * Role yang boleh masuk panel admin (/admin). Akses per modul diatur dinamis lewat menu Hak Akses
     * (tabel admin_modules dan admin_role_permissions); daftar ini hanya menentukan siapa yang bisa login.
     */
    public const ADMIN_PANEL_ROLES = [
        'super_admin', 'ecommerce', 'sales_admin', 'brand_marketing', 'finance',
        'customer_service', 'it_application', 'inventory_manager', 'sales', 'agent',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'phone',
        'date_of_birth',
        'occupation',
        'role',
        'sub_role',
        'is_reseller',
        'reseller_marked_at',
        'warehouse_id',
        'hub_id',
        'no_ktp',
        'no_npwp',
        'distributor_status',
        'distributor_applied_at',
        'distributor_approved_at',
        'distributor_province_id',
        'distributor_regency_id',
        'distributor_address',
        'aturan_minimal_masa_berlaku',
        'pakai_ppn',
        'driippreneur_status',
        'is_eligible',
        'driippreneur_province_id',
        'driippreneur_regency_id',
        'driippreneur_address',
        'driippreneur_applied_at',
        'ktp_file',
        'npwp_file',
        'selfie_file',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'points',
        'price_level_id',
        'referral_code',
        'referred_by_id',
        'google_id',
        'google_token',
        'google_refresh_token',
        'term_of_payment',
        'monthly_target',
        'credit_limit',
        'ar_outstanding',
        'payment_method',
        'wa_verified_at',
        'wa_verification_code',
        'qad_customer_code',
        'sales_code',
        'is_potential_distributor',
        'ar_balance_last_sync_at',
        'zoho_customer_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode($user->name);
            }
        });

        static::saving(function ($user) {
            if ($user->role === self::ROLE_DISTRIBUTOR && $user->isDirty('role') && empty($user->distributor_sales_code)) {
                $user->distributor_sales_code = static::generateDistributorSalesCode();
            }
        });
    }

    /** Prefix kode sales (Data Sales admin dan sales milik distributor), mis. MCR001. */
    public const SALES_CODE_PREFIX = 'MCR';

    /**
     * Kode sales berikutnya dengan format yang sudah ada: MCR + nomor urut minimal 3 digit (MCR001, MCR090, ...).
     * Kode user yang sudah dihapus ikut dihitung agar tidak dipakai ulang. Panggil di dalam transaksi.
     */
    public static function generateSalesCode(): string
    {
        $prefix = self::SALES_CODE_PREFIX;

        $max = static::withTrashed()
            ->where('sales_code', 'regexp', '^' . $prefix . '[0-9]+$')
            ->lockForUpdate()
            ->pluck('sales_code')
            ->map(fn ($code) => (int) substr($code, strlen($prefix)))
            ->max() ?? 0;

        return $prefix . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    public static function generateDistributorSalesCode(): string
    {
        $prefix = self::DISTRIBUTOR_SALES_CODE_PREFIX;

        $lastCode = static::withTrashed()
            ->where('distributor_sales_code', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(distributor_sales_code) DESC')
            ->orderByDesc('distributor_sales_code')
            ->value('distributor_sales_code');

        $next = $lastCode ? ((int) substr($lastCode, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Termasuk distributor nonaktif/terhapus: order lama tetap harus dikenali kode distributornya.
     */
    public static function findDistributorBySalesCode(?string $code): ?self
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return null;
        }

        return static::withTrashed()->where('distributor_sales_code', $code)->first();
    }

    /**
     * Link pendaftaran hanya berlaku untuk distributor yang masih aktif.
     */
    public function canReferOutlets(): bool
    {
        return $this->isDistributor() && $this->is_active && ! $this->trashed();
    }

    /**
     * Kode yang sah sebagai atribusi outlet: milik Sales atau milik Distributor.
     */
    public static function isKnownSalesCode(?string $code): bool
    {
        $code = trim((string) $code);

        if ($code === '') {
            return false;
        }

        return static::where(function ($query) use ($code) {
            $query->where(fn ($q) => $q->where('role', self::ROLE_SALES)->where('sales_code', $code))
                ->orWhere(fn ($q) => $q->where('role', self::ROLE_DISTRIBUTOR)->where('distributor_sales_code', $code));
        })->exists();
    }

    /**
     * Pilihan atribusi untuk form/filter admin: daftar Sales lalu Distributor.
     *
     * @return \Illuminate\Support\Collection<int, object{name: string, sales_code: string}>
     */
    /**
     * Hub milik sales dengan kode ini (Data Sales > Hub). Kode distributor / tidak dikenal: null.
     */
    public static function hubIdForSalesCode(?string $code): ?string
    {
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }

        $hubId = static::where('role', self::ROLE_SALES)->where('sales_code', $code)->value('warehouse_id');

        return $hubId ? (string) $hubId : null;
    }

    /**
     * Pilihan sales untuk satu hub (Admin Hub): hanya sales yang terdaftar di hub tersebut.
     */
    public static function salesCodeOptionsForHub(string $hubId): \Illuminate\Support\Collection
    {
        return static::where('role', self::ROLE_SALES)
            ->where('warehouse_id', $hubId)
            ->whereNotNull('sales_code')
            ->where('sales_code', '!=', '')
            ->orderBy('name')
            ->get(['name', 'sales_code'])
            ->map(fn ($user) => (object) ['name' => $user->name, 'sales_code' => $user->sales_code]);
    }

    public static function salesCodeOptions(): \Illuminate\Support\Collection
    {
        $sales = static::where('role', self::ROLE_SALES)
            ->whereNotNull('sales_code')
            ->where('sales_code', '!=', '')
            ->orderBy('name')
            ->get(['name', 'sales_code'])
            ->map(fn ($user) => (object) ['name' => $user->name, 'sales_code' => $user->sales_code]);

        $distributors = static::where('role', self::ROLE_DISTRIBUTOR)
            ->whereNotNull('distributor_sales_code')
            ->orderBy('name')
            ->get(['name', 'distributor_sales_code'])
            ->map(fn ($user) => (object) ['name' => 'Distributor ' . $user->name, 'sales_code' => $user->distributor_sales_code]);

        return $sales->concat($distributors)->values();
    }

    public static function generateUniqueReferralCode($name = null)
    {
        if (empty($name)) {
            return static::generateRandomReferralCode();
        }

        // Clean name: lowercase, remove spaces and non-alphanumeric characters
        $baseCode = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
        
        // If empty or too short after cleaning, use random
        if (strlen($baseCode) < 3) {
            return static::generateRandomReferralCode();
        }

        // Limit length to 20 characters
        $baseCode = substr($baseCode, 0, 20);
        
        $code = $baseCode;
        $counter = 1;

        while (static::where('referral_code', $code)->exists()) {
            $suffix = (string)$counter;
            // Shorten baseCode if necessary to fit suffix within 20 chars
            $maxBaseLen = 20 - strlen($suffix);
            $code = substr($baseCode, 0, $maxBaseLen) . $suffix;
            $counter++;
        }
        
        return $code;
    }

    private static function generateRandomReferralCode()
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'wa_verified_at' => 'datetime',
            'distributor_applied_at' => 'datetime',
            'distributor_approved_at' => 'datetime',
            'password' => 'hashed',
            'points' => 'integer',
            'driippreneur_applied_at' => 'datetime',
            'date_of_birth' => 'date',
            'pakai_ppn' => 'boolean',
            'is_reseller' => 'boolean',
            'reseller_marked_at' => 'datetime',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** Hub asal outlet/reseller (dari sales saat daftar). */
    public function hub(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'hub_id');
    }

    /**
     * Hub belanja tetap untuk outlet/reseller yang terdaftar di sebuah hub (hub aktif):
     * stok, harga, dan pengiriman toko selalu dari hub ini.
     */
    public function registeredShoppingHub(): ?Warehouse
    {
        // Subdist selalu belanja dari gudang distributornya.
        if ($this->isSubdist()) {
            $warehouse = $this->subdistWarehouse();

            return $warehouse && $warehouse->is_active ? $warehouse : null;
        }

        if (! in_array($this->role, [self::ROLE_OUTLET, self::ROLE_ECOM], true) || ! $this->hub_id) {
            return null;
        }

        $hub = $this->hub;

        return $hub && $hub->is_active ? $hub : null;
    }

    public function priceLevel(): BelongsTo
    {
        return $this->belongsTo(PriceLevel::class);
    }

    /**
     * Provinsi domisili pengajuan distributor (dibaca dari view wilayah; atribut: id, name).
     */
    public function distributorProvince(): BelongsTo
    {
        return $this->belongsTo(WilayahAdministratif::class, 'distributor_province_id', 'province_id')
            ->select('province_id', 'province_id as id', 'province_name as name');
    }

    /**
     * Kabupaten/kota domisili pengajuan distributor (atribut: id, name).
     */
    public function distributorRegency(): BelongsTo
    {
        return $this->belongsTo(WilayahAdministratif::class, 'distributor_regency_id', 'regency_id')
            ->select('regency_id', 'regency_id as id', 'regency_name as name');
    }

    public function distributorWilayah(): BelongsTo
    {
        // Using distributor_regency_id to get WilayahAdministratif because it's more specific, or just map via regency_id
        return $this->belongsTo(WilayahAdministratif::class, 'distributor_regency_id', 'regency_id');
    }

    public function driippreneurWilayah(): BelongsTo
    {
        return $this->belongsTo(WilayahAdministratif::class, 'driippreneur_regency_id', 'regency_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function distributorDocuments(): HasMany
    {
        return $this->hasMany(DistributorDocument::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function pointWithdrawals(): HasMany
    {
        return $this->hasMany(PointWithdrawal::class);
    }

    public function targetBelanja(): HasMany
    {
        return $this->hasMany(TargetBelanja::class, 'distributor_id');
    }

    public function categoryDiscounts(): HasMany
    {
        return $this->hasMany(DistributorCategoryDiscount::class, 'distributor_id');
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ECOMMERCE,
            self::ROLE_BRAND_MARKETING,
            self::ROLE_FINANCE,
        ]);
    }

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_code', 'sales_code')->where('role', self::ROLE_SALES);
    }

    /**
     * Distributor yang merekrut outlet ini lewat link pendaftaran.
     */
    public function referringDistributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_code', 'distributor_sales_code')->where('role', self::ROLE_DISTRIBUTOR);
    }

    public function distributorApiClients(): HasMany
    {
        return $this->hasMany(DistributorApiClient::class);
    }

    /**
     * User ecom yang ditandai admin sebagai reseller.
     */
    public function isReseller(): bool
    {
        return (bool) $this->is_reseller;
    }

    /**
     * Memakai strata diskon subdist (> 50 karton): role subdist, atau user yang ditandai admin.
     */
    public function usesSubdistTier(): bool
    {
        return $this->isSubdist() || $this->isReseller();
    }

    /**
     * Level pembeli di halaman Outlet dan Reseller: reseller (outlet/subdist/ecom bertanda reseller),
     * outlet, subdist, atau ecom.
     */
    public function customerLevel(): string
    {
        if ($this->isReseller()) {
            return 'reseller';
        }

        return match ($this->role) {
            self::ROLE_OUTLET => 'outlet',
            self::ROLE_SUBDIST => 'subdist',
            default => 'ecom',
        };
    }

    /**
     * Pembeli dilayani lewat jalur outlet (hub outlet, stok WMS, kurir toko/ambil sendiri):
     * reseller selalu, selain itu bila checkout memakai kode sales.
     */
    /**
     * Outlet yang kode sales di profilnya valid dianggap berkode sales di semua halaman,
     * tidak bergantung pada parameter ?sales_code= di URL. Dipakai untuk memilih hub dan sumber stok.
     */
    public function hasOwnSalesCode(): bool
    {
        if (! $this->isOutlet()) {
            return false;
        }

        return $this->hasOwnSalesCodeCache ??= static::isKnownSalesCode($this->sales_code);
    }

    public function usesOutletChannel(bool $hasSalesCode): bool
    {
        return $this->isReseller() || $hasSalesCode;
    }

    public function levelChanges(): HasMany
    {
        return $this->hasMany(UserLevelChange::class)->latest('created_at')->latest('id');
    }

    /**
     * Semua gudang milik distributor ini: gudang utama (users.warehouse_id) lebih dulu, lalu gudang tambahan.
     *
     * @return \Illuminate\Support\Collection<int, Warehouse>
     */
    public function distributorWarehouses(): \Illuminate\Support\Collection
    {
        if (! $this->isDistributor()) {
            return collect();
        }

        $primary = $this->warehouse_id ? Warehouse::find($this->warehouse_id) : null;
        $extras = Warehouse::where('owner_distributor_id', $this->id)
            ->when($primary, fn ($q) => $q->where('id', '!=', $primary->id))
            ->orderBy('name')->get();

        return collect([$primary])->filter()->concat($extras)->values();
    }

    /**
     * Gudang distributor induk yang bisa dipilih subdist (utama lebih dulu).
     *
     * @return \Illuminate\Support\Collection<int, Warehouse>
     */
    public function subdistWarehouseOptions(): \Illuminate\Support\Collection
    {
        if (! $this->isSubdist()) {
            return collect();
        }

        $distributor = $this->referringDistributor;

        return $distributor && $distributor->canReferOutlets() ? $distributor->distributorWarehouses() : collect();
    }

    /**
     * Gudang asal order subdist: pilihan subdist (bila masih siap), selain itu gudang siap pertama
     * (utama lebih dulu). Bila tidak ada yang siap, gudang utama dikembalikan agar alasannya bisa ditampilkan.
     */
    public function subdistWarehouse(): ?Warehouse
    {
        $options = $this->subdistWarehouseOptions();
        if ($options->isEmpty()) {
            return null;
        }

        $selectedId = session('subdist_warehouse_id');
        $selected = $selectedId ? $options->firstWhere('id', $selectedId) : null;
        if ($selected && $selected->isReadyForSubdist()) {
            return $selected;
        }

        return $options->first(fn (Warehouse $w) => $w->isReadyForSubdist()) ?? $options->first();
    }

    /**
     * Alasan subdist belum bisa berbelanja (distributor/gudang belum siap), atau null bila siap.
     */
    public function subdistFulfillmentProblem(): ?string
    {
        if (! $this->isSubdist()) {
            return null;
        }

        $options = $this->subdistWarehouseOptions();
        if ($options->isEmpty() || $options->contains(fn (Warehouse $w) => $w->isReadyForSubdist())) {
            $warehouse = $this->subdistWarehouse();
            if (! $warehouse) {
                return 'Gudang distributor Anda belum aktif, sehingga belum bisa menerima pesanan. Silakan hubungi distributor Anda.';
            }

            return null;
        }

        if ($options->contains(fn (Warehouse $w) => $w->is_active)) {
            return 'Alamat gudang distributor Anda belum lengkap, sehingga belum bisa menerima pesanan. Silakan hubungi distributor Anda.';
        }

        return 'Gudang distributor Anda belum aktif, sehingga belum bisa menerima pesanan. Silakan hubungi distributor Anda.';
    }

    /**
     * Subdist: user yang terdaftar lewat link/kode distributor ini.
     */
    public function referredSubdists(): HasMany
    {
        return $this->hasMany(User::class, 'sales_code', 'distributor_sales_code')->where('role', self::ROLE_SUBDIST);
    }

    public function isAgent(): bool
    {
        return in_array($this->role, [self::ROLE_AGENT, self::ROLE_SUPER_ADMIN]);
    }

    public function isWarehouse(): bool
    {
        return $this->role === self::ROLE_WAREHOUSE;
    }

    public function isAffiliator()
    {
        return $this->role === self::ROLE_AFFILIATOR;
    }

    public function isDriippreneur(): bool
    {
        return $this->role === self::ROLE_DRIIPPRENEUR;
    }

    public function isDistributor(): bool
    {
        return $this->role === self::ROLE_DISTRIBUTOR;
    }

    /**
     * Minimal sisa masa berlaku (bulan). Distributor kosong memakai default 9.
     */
    public function shelfLifeMonths(): int
    {
        if ($this->aturan_minimal_masa_berlaku !== null) {
            return (int) $this->aturan_minimal_masa_berlaku;
        }

        return $this->isDistributor() ? self::DEFAULT_SHELF_LIFE_MONTHS : 0;
    }

    /**
     * Default YA. Null dianggap memakai PPN.
     */
    public function usesPpn(): bool
    {
        return $this->pakai_ppn !== false;
    }

    public function isEcom(): bool
    {
        return $this->role === self::ROLE_ECOM;
    }

    public function isSubdist(): bool
    {
        return $this->role === self::ROLE_SUBDIST;
    }

    /**
     * Nama di header toko hanya untuk peran belanja yang memang dipakai di depan.
     */
    public function showsStorefrontIdentity(): bool
    {
        return in_array($this->role, [
            self::ROLE_ECOM,
            self::ROLE_RESELLER,
            self::ROLE_DISTRIBUTOR,
            self::ROLE_OUTLET,
            self::ROLE_SUBDIST,
            self::ROLE_DRIIPPRENEUR,
            self::ROLE_AFFILIATOR,
        ], true);
    }

    public function isOutlet(): bool
    {
        return $this->role === self::ROLE_OUTLET;
    }

    public function isSales(): bool
    {
        return $this->role === self::ROLE_SALES;
    }

    /** Sales milik distributor (role sales dengan distributor_id), bukan sales perusahaan. */
    public function isDistributorSales(): bool
    {
        return $this->isSales() && filled($this->distributor_id);
    }

    /** Email pengganti (.invalid) untuk sales distributor yang tidak mengisi email. */
    public const INTERNAL_EMAIL_DOMAIN = 'rasaconnect.invalid';

    public static function internalSalesEmail(): string
    {
        return 'sales-distributor-' . \Illuminate\Support\Str::uuid() . '@' . self::INTERNAL_EMAIL_DOMAIN;
    }

    public function hasInternalEmail(): bool
    {
        return str_ends_with((string) $this->email, '@' . self::INTERNAL_EMAIL_DOMAIN);
    }

    /** Distributor pemilik sales ini (khusus sales milik distributor). */
    public function ownerDistributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id')->where('role', self::ROLE_DISTRIBUTOR);
    }

    /** Sales milik distributor ini (menu Data Sales di akun distributor). */
    public function distributorSales(): HasMany
    {
        return $this->hasMany(User::class, 'distributor_id')->where('role', self::ROLE_SALES);
    }

    /** Hanya sales perusahaan (Data Sales admin), tanpa sales milik distributor. */
    public function scopeCompanySales($query)
    {
        return $query->where('role', self::ROLE_SALES)->whereNull('distributor_id');
    }

    public function getFaspayCompany(): string
    {
        if ($this->isDistributor()) {
            return \App\Services\FaspayConfig::COMPANY_MCR;
        }

        return \App\Services\FaspayConfig::getDefaultCompany();
    }

    /**
     * Rekening bank untuk penarikan poin afiliasi / syarat daftar Affiliator (bukan data distributor).
     */
    public function hasAffiliateBankDetails(): bool
    {
        return filled(trim((string) $this->bank_name))
            && filled(trim((string) $this->bank_account_number))
            && filled(trim((string) $this->bank_account_name));
    }

    public function canApplyAsDistributor(): bool
    {
        return $this->role === self::ROLE_ECOM && empty($this->distributor_status);
    }

    public function hasDistributorApplicationPending(): bool
    {
        return $this->distributor_status === 'pending';
    }

    public function isDistributorApproved(): bool
    {
        return $this->distributor_status === 'approved';
    }

    public function isDistributorRejected(): bool
    {
        return $this->distributor_status === 'rejected';
    }

    public function canApplyAsDriippreneur(): bool
    {
        return $this->role === self::ROLE_ECOM && empty($this->driippreneur_status);
    }

    public function hasDriippreneurApplicationPending(): bool
    {
        return $this->driippreneur_status === 'pending';
    }

    public function isDriippreneurApproved(): bool
    {
        return $this->driippreneur_status === 'approved';
    }

    public function isDriippreneurRejected(): bool
    {
        return $this->driippreneur_status === 'rejected';
    }

    /**
     * Warehouse ID yang tidak boleh dipakai sebagai hub belanja (restock dari pusat lain).
     * Hanya berlaku untuk akun distributor yang terikat ke sebuah hub.
     */
    public function distributorShoppingExcludedWarehouseId(): ?string
    {
        if ($this->isDistributor() && filled($this->warehouse_id)) {
            return (string) $this->warehouse_id;
        }

        return null;
    }

    /**
     * Hapus pilihan hub belanja dari session + cookie jika mengarah ke warehouse distributor sendiri.
     */
    public function forgetOwnHubShoppingSelectionIfSet(): void
    {
        $exclude = $this->distributorShoppingExcludedWarehouseId();
        if (!$exclude || !session()->has('selected_hub_id')) {
            return;
        }
        if ((string) session('selected_hub_id') !== $exclude) {
            return;
        }
        session()->forget(['selected_hub_id', 'selected_hub_name', 'selected_hub_slug']);
        foreach (['selected_hub_id', 'selected_hub_name', 'selected_hub_slug'] as $name) {
            Cookie::queue(Cookie::forget($name));
        }
    }

    /**
     * Diskon kategori distributor untuk produk (brand + kategori).
     */
    public function categoryDiscountPercentageFor(Product $product): float
    {
        if (! $this->isDistributor() || ! $product->category_id) {
            return 0.0;
        }

        $this->loadMissing('categoryDiscounts');

        $row = $this->categoryDiscounts
            ->where('brand_id', $product->brand_id)
            ->where('category_id', $product->category_id)
            ->first();

        return $row ? max(0.0, (float) $row->discount_percentage) : 0.0;
    }

    /**
     * Persentase diskon yang dipakai untuk produk: kategori dulu, lalu price level.
     */
    public function productDiscountPercentageFor(Product $product): float
    {
        $percent = $this->categoryDiscountPercentageFor($product);
        if ($percent > 0) {
            return $percent;
        }

        if ($this->isDistributor()) {
            $this->loadMissing('priceLevel');
            if ($this->priceLevel) {
                return max(0.0, (float) $this->priceLevel->discount_percentage);
            }
        }

        return 0.0;
    }

    /**
     * Harga jual DPP: pajak katalog dikeluarkan dulu, baru diskon distributor.
     */
    public function getProductPrice(Product $product): float
    {
        $inclusive = (float) $product->final_price;

        $categoryPercent = $this->categoryDiscountPercentageFor($product);
        if ($categoryPercent > 0) {
            return \App\Support\TaxAwarePrice::applyDiscount($inclusive, $categoryPercent);
        }

        if ($this->isDistributor()) {
            $this->loadMissing('priceLevel');
            if ($this->priceLevel) {
                return $this->priceLevel->calculatePriceForProduct($product);
            }
        }

        return \App\Support\TaxAwarePrice::applyDiscount($inclusive, 0.0);
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        Log::info('Sending email verification notification', [
            'user_id' => $this->getKey(),
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
        ]);

        $this->notify(new \App\Notifications\VerifyEmailNotification);
    }

    /**
     * AR Outstanding dari master distributor (QAD / input admin).
     */
    public function getArOutstandingAmount(): float
    {
        return max(0, (float) ($this->ar_outstanding ?? 0));
    }

    /**
     * Total order TOP yang masih berjalan di sistem (belum lunas, tidak dibatalkan).
     */
    public function getExistingTopTransactionTotal(?string $excludeOrderId = null): float
    {
        $query = $this->orders()
            ->where('payment_method', 'term_of_payment')
            ->where('payment_status', '!=', 'paid')
            ->whereNotIn('order_status', ['cancelled']);

        if ($excludeOrderId) {
            $query->where('id', '!=', $excludeOrderId);
        }

        return (float) $query->sum('total_amount');
    }

    /**
     * Total utang yang dipakai cek limit kredit TOP:
     * AR Outstanding + transaksi TOP existing yang belum lunas.
     */
    public function getAccumulatedTopDebt(?string $excludeOrderId = null): float
    {
        return $this->getArOutstandingAmount() + $this->getExistingTopTransactionTotal($excludeOrderId);
    }

    /**
     * Nama label role pengguna yang ramah dibaca.
     */
    public function getRoleLabelAttribute(): string
    {
        $labels = [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ECOMMERCE => 'eCommerce',
            self::ROLE_BRAND_MARKETING => 'Brand Marketing',
            self::ROLE_FINANCE => 'Finance',
            'sales_admin' => 'Sales Admin',
            self::ROLE_SALES => 'Sales',
            self::ROLE_CUSTOMER_SERVICE => 'Customer Service',
            self::ROLE_IT_APPLICATION => 'IT Application',
            self::ROLE_INVENTORY_MANAGER => 'Inventory Manager',
            self::ROLE_DISTRIBUTOR => 'Distributor',
            self::ROLE_OUTLET => 'Outlet',
            self::ROLE_SUBDIST => 'Subdist',
            self::ROLE_DRIIPPRENEUR => 'Driippreneur',
            self::ROLE_AFFILIATOR => 'Affiliator',
            'agent' => 'Agen',
            self::ROLE_ECOM => 'Ecom',
        ];

        return $labels[$this->role] ?? ucwords(str_replace('_', ' ', (string) ($this->role ?? 'Admin')));
    }
}
