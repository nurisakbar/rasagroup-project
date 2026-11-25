<?php

namespace App\Support\AdminAccess;

use App\Models\AdminModule;
use App\Models\AdminRolePermission;

/**
 * Modul bawaan panel admin dan izin awal tiap role. Daftar role di bawah meniru akses menu yang dulu
 * di-hardcode di sidebar, jadi menjalankan migrasi tidak mengubah siapa yang melihat apa. Setelah itu
 * semuanya dikelola lewat menu Hak Akses. super_admin selalu punya akses penuh dan tidak disimpan di tabel.
 */
class DefaultModules
{
    private const ALL_ADMIN = ['ecommerce', 'sales_admin', 'brand_marketing', 'finance', 'customer_service', 'it_application', 'inventory_manager', 'sales', 'agent'];

    /**
     * key => [nama, grup, ikon, rute menu, pola rute, urutan, tampil di menu, role bawaan]
     *
     * @return array<string, array<string, mixed>>
     */
    public static function definitions(): array
    {
        $m = fn (string $name, ?string $group, string $icon, ?string $route, array $patterns, int $sort, bool $menu, array $roles) => compact('name', 'group', 'icon', 'route', 'patterns', 'sort', 'menu', 'roles');

        return [
            'orders' => $m('LAPORAN PESANAN', null, 'fa-shopping-cart', 'admin.orders.index', ['admin.orders.*'], 10, true, ['ecommerce', 'sales_admin', 'finance']),
            'report_gmv' => $m('LAPORAN GMV', null, 'fa-line-chart', 'admin.reports.gmv.index', ['admin.reports.gmv.*'], 20, true, ['ecommerce', 'sales_admin', 'finance', 'brand_marketing']),
            'report_distributor_sales' => $m('PENJUALAN DISTRIBUTOR', null, 'fa-bar-chart', 'admin.reports.distributor-sales.index', ['admin.reports.distributor-sales.*'], 30, true, ['finance']),
            'report_distributor_forecast' => $m('FORECAST DISTRIBUTOR', null, 'fa-line-chart', 'admin.reports.distributor-forecast.index', ['admin.reports.distributor-forecast.*'], 40, true, ['finance']),
            'manual_orders' => $m('INPUT TRANSAKSI', null, 'fa-plus-square', 'admin.orders.create', ['admin.orders.create', 'admin.orders.store'], 50, true, ['ecommerce', 'sales_admin']),
            'warehouses' => $m('DATA HUB', null, 'fa-building', 'admin.warehouses.index', ['admin.warehouses.*', 'admin.debug-ekspedisiku'], 60, true, ['inventory_manager']),
            'armadas' => $m('ARMADA PENGIRIMAN', null, 'fa-motorcycle', 'admin.armadas.index', ['admin.armadas.*'], 70, true, ['inventory_manager']),
            'distributors' => $m('DISTRIBUTOR', null, 'fa-truck', 'admin.distributors.index', ['admin.distributors.*'], 80, true, ['inventory_manager', 'finance']),
            'driippreneurs' => $m('AFFILIATOR', null, 'fa-users', 'admin.driippreneurs.index', ['admin.driippreneurs.*'], 90, true, ['ecommerce', 'sales_admin']),
            'point_withdrawals' => $m('PENARIKAN POIN', null, 'fa-money', 'admin.point-withdrawals.index', ['admin.point-withdrawals.*'], 100, true, ['finance']),
            'payment_fees' => $m('BIAYA LAYANAN', null, 'fa-credit-card', 'admin.payment-fees.index', ['admin.payment-fees.*'], 110, true, ['finance']),
            'users' => $m('DATA PENGGUNA', null, 'fa-users', 'admin.users.index', ['admin.users.*'], 120, true, ['customer_service']),
            'outlets' => $m('OUTLET DAN RESELLER', null, 'fa-building-o', 'admin.outlets.index', ['admin.outlets.*'], 130, true, ['customer_service']),
            'sales' => $m('DATA SALES', null, 'fa-briefcase', 'admin.sales.index', ['admin.sales.*'], 140, true, ['ecommerce', 'sales_admin']),
            'products' => $m('PRODUK', 'MASTER DATA', 'fa-shopping-bag', 'admin.products.index', ['admin.products.*'], 150, true, ['brand_marketing']),
            'brands' => $m('BRAND', 'MASTER DATA', 'fa-bookmark', 'admin.brands.index', ['admin.brands.*'], 160, true, ['brand_marketing']),
            'categories' => $m('KATEGORI', 'MASTER DATA', 'fa-folder', 'admin.categories.index', ['admin.categories.*'], 170, true, ['brand_marketing']),
            'price_levels' => $m('LEVEL HARGA', 'MASTER DATA', 'fa-tags', 'admin.price-levels.index', ['admin.price-levels.*'], 180, true, ['ecommerce', 'sales_admin']),
            'warehouse_prices' => $m('MASTER HARGA', 'MASTER DATA', 'fa-money', 'admin.warehouse-prices.index', ['admin.warehouse-prices.*'], 190, true, ['ecommerce']),
            'menus' => $m('MENU', 'MASTER DATA', 'fa-list', 'admin.menus.index', ['admin.menus.*'], 200, true, ['it_application']),
            'sliders' => $m('WEB SLIDER', null, 'fa-image', 'admin.sliders.index', ['admin.sliders.*'], 210, true, ['brand_marketing']),
            'website_popups' => $m('POP UP WEBSITE', null, 'fa-window-maximize', 'admin.website-popups.index', ['admin.website-popups.*'], 220, true, ['ecommerce', 'sales_admin']),
            'pages' => $m('HALAMAN STATIS', null, 'fa-file-text', 'admin.pages.index', ['admin.pages.*'], 230, true, ['it_application']),
            'information_channels' => $m('SALURAN INFORMASI', null, 'fa-info-circle', 'admin.information-channels.index', ['admin.information-channels.*'], 240, true, ['brand_marketing']),
            'discount_tiers' => $m('POTONGAN HARGA', null, 'fa-percent', 'admin.discount-tiers.index', ['admin.discount-tiers.*'], 250, true, ['ecommerce', 'sales_admin']),
            'settings' => $m('PENGATURAN', null, 'fa-cog', 'admin.settings.index', ['admin.settings.*'], 260, true, ['it_application']),
            // Tanpa menu: tetap dikontrol aksesnya.
            'promos' => $m('PROMO', null, 'fa-gift', 'admin.promos.index', ['admin.promos.*'], 270, false, []),
            'master_sync' => $m('SINKRON DATA MASTER', null, 'fa-refresh', null, ['admin.master-sync.*'], 280, false, ['it_application']),
            'lookups' => $m('DATA PENDUKUNG (PENCARIAN)', null, 'fa-search', null, [
                'admin.orders.search-customers', 'admin.orders.customer-addresses', 'admin.orders.preview-pricing',
                'admin.orders.product-batches', 'admin.orders.search-products', 'admin.orders.search-sales',
                'admin.promos.search-products', 'admin.distributors.get-*', 'admin.get-*', 'admin.distributors.qad-customers',
                'admin.warehouses.jubelio-locations', 'admin.warehouses.qad-locations', 'admin.warehouses.sync-qad-batches.locations',
            ], 290, false, self::ALL_ADMIN),
            // Hanya super_admin (tetap): mengelola hak akses itu sendiri.
            'access_control' => $m('HAK AKSES', null, 'fa-lock', 'admin.access.index', ['admin.access.*'], 300, true, []),
        ];
    }

    /** Modul yang aksesnya selalu hanya untuk super_admin dan tidak bisa diberikan ke role lain. */
    public const SUPER_ADMIN_ONLY = ['access_control'];

    /** Isi modul bawaan dan izin awalnya. Aman dijalankan berulang: tidak menimpa pengaturan yang sudah ada. */
    public static function seed(): void
    {
        foreach (self::definitions() as $key => $d) {
            $module = AdminModule::firstOrCreate(['key' => $key], [
                'name' => $d['name'], 'group' => $d['group'], 'icon' => $d['icon'], 'menu_route' => $d['route'],
                'route_patterns' => $d['patterns'], 'sort_order' => $d['sort'], 'show_in_menu' => $d['menu'],
                'is_active' => true, 'is_system' => true,
            ]);

            if (in_array($key, self::SUPER_ADMIN_ONLY, true)) {
                continue;
            }
            foreach ($d['roles'] as $role) {
                AdminRolePermission::firstOrCreate(['role' => $role, 'module_id' => $module->id], [
                    'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true,
                ]);
            }
        }
    }

    /** Kembalikan izin satu role ke bawaan untuk semua modul bawaan (modul buatan sendiri tidak disentuh). */
    public static function resetRole(string $role): void
    {
        $modules = AdminModule::where('is_system', true)->get()->keyBy('key');
        $defs = self::definitions();

        foreach ($modules as $key => $module) {
            if (in_array($key, self::SUPER_ADMIN_ONLY, true)) {
                continue;
            }
            $granted = in_array($role, $defs[$key]['roles'] ?? [], true);
            if ($granted) {
                AdminRolePermission::updateOrCreate(['role' => $role, 'module_id' => $module->id], [
                    'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true,
                ]);
            } else {
                AdminRolePermission::where('role', $role)->where('module_id', $module->id)->delete();
            }
        }
    }
}
