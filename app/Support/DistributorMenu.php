<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * Menu akun distributor, dikelompokkan per peran: distributor menjual ke outlet & subdist (Penjualan, Jaringan)
 * dan membeli ke Rasa Group (Pembelian). Dipakai sidebar desktop dan menu akun mobile agar isinya selalu sama.
 */
class DistributorMenu
{
    /**
     * @return list<array{title: string, hint: ?string, items: list<array{label: string, icon: string, url: string, active: bool, badge: ?int}>}>
     */
    public static function groups(): array
    {
        $item = fn (string $label, string $icon, string $url, array $patterns, ?int $badge = null) => [
            'label' => $label,
            'icon' => $icon,
            'url' => $url,
            'active' => Route::is(...$patterns),
            'badge' => $badge ?: null,
        ];

        return [
            [
                'title' => 'Penjualan',
                'hint' => 'Anda sebagai penjual ke outlet & subdist',
                'items' => [
                    $item('Pesanan Masuk', 'fi-rs-shopping-bag', route('distributor.manage-orders.index'), ['distributor.manage-orders.*'], self::pendingIncomingOrders()),
                    $item('Kelola Stok', 'fi-rs-box', route('distributor.stock.index'), ['distributor.stock.*']),
                    $item('Lokasi Gudang', 'fi-rs-building', route('distributor.warehouse-location.index'), ['distributor.warehouse-location.*']),
                    $item('Rekening Pembayaran', 'fi-rs-bank', route('distributor.bank-accounts.index'), ['distributor.bank-accounts.*']),
                ],
            ],
            [
                'title' => 'Jaringan',
                'hint' => 'Outlet, subdist, dan sales Anda',
                'items' => [
                    $item('Outlet dan Subdist', 'fi-rs-users', route('buyer.outlet-referral.index'), ['buyer.outlet-referral.*']),
                    $item('Data Sales', 'fi-rs-id-badge', route('buyer.sales.index'), ['buyer.sales.*']),
                    $item('Forecast', 'fi-rs-stats', route('buyer.forecast.index'), ['buyer.forecast.*']),
                ],
            ],
            [
                'title' => 'Pembelian',
                'hint' => 'Anda sebagai pembeli ke Rasa Group',
                'items' => [
                    $item('Belanja Produk', 'fi-rs-shopping-cart', route('products.index'), ['products.*']),
                    $item('Riwayat Pembelian', 'fi-rs-time-past', route('distributor.orders.history'), ['distributor.orders.history', 'distributor.orders.show', 'buyer.orders.show']),
                    $item('Alamat Pengiriman', 'fi-rs-marker', route('buyer.addresses.index'), ['buyer.addresses.*']),
                ],
            ],
            [
                'title' => 'Akun',
                'hint' => null,
                'items' => [
                    $item('Profil Distributor', 'fi-rs-user', route('distributor.profile'), ['distributor.profile', 'buyer.profile', 'buyer.profile.edit']),
                    $item('Jadwal Operasional', 'fi-rs-clock', route('buyer.profile.operational-hours'), ['buyer.profile.operational-hours']),
                ],
            ],
        ];
    }

    /** Pesanan masuk yang perlu ditindaklanjuti (menunggu diproses) di gudang distributor. Di-cache per request. */
    private static function pendingIncomingOrders(): int
    {
        static $count = null;

        if ($count !== null) {
            return $count;
        }

        $warehouseIds = Auth::user()?->distributorWarehouses()->pluck('id')->all() ?? [];

        return $count = $warehouseIds === [] ? 0 : Order::whereIn('source_warehouse_id', $warehouseIds)
            ->where('finance_approved', true)
            ->where('order_status', 'pending')
            ->count();
    }
}
