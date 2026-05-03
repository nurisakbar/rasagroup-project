<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuDetail;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MenuDummySeeder extends Seeder
{
    private const SEED_MARKER = '<!--seed:menu-dummy-->';

    /**
     * 10 menu paket dummy (gambar acak via picsum.photos) + komposisi produk acak.
     */
    public function run(): void
    {
        Menu::query()
            ->where('deskripsi', 'like', '%'.self::SEED_MARKER.'%')
            ->delete();

        $productIds = Product::query()
            ->where('status', 'active')
            ->pluck('id');

        if ($productIds->isEmpty()) {
            $productIds = Product::query()->pluck('id');
        }

        if ($productIds->isEmpty()) {
            $this->command?->warn('MenuDummySeeder: tidak ada produk di database. Jalankan ProductSeeder terlebih dahulu.');

            return;
        }

        $names = [
            'Paket Sarapan Nusantara',
            'Bundling Minuman Horeca',
            'Set Sirup & Topping Premium',
            'Paket Bahan Kue Hemat',
            'Combo Sirup Musim Dingin',
            'Paket Mocktail Starter',
            'Set Minuman Kantor',
            'Bundling Dessert Corner',
            'Paket Catering Mini',
            'Combo Es Krim & Topping',
        ];

        foreach ($names as $index => $namaMenu) {
            $seed = 'rasamenu-'.($index + 1).'-'.bin2hex(random_bytes(3));
            $menu = Menu::query()->create([
                'nama_menu' => $namaMenu,
                'deskripsi' => 'Data dummy untuk pengujian tampilan menu. '.fake()->sentence()."\n\n".self::SEED_MARKER,
                'gambar' => 'https://picsum.photos/seed/'.$seed.'/800/520',
                'status_aktif' => true,
                'tampil_mulai' => null,
                'tampil_sampai' => null,
            ]);

            $nDetails = random_int(2, min(5, $productIds->count()));
            foreach ($productIds->shuffle()->take($nDetails) as $productId) {
                MenuDetail::query()->create([
                    'menu_id' => $menu->id,
                    'product_id' => $productId,
                    'jumlah' => random_int(1, 3),
                ]);
            }
        }

        $this->command?->info('MenuDummySeeder: 10 menu dummy berhasil dibuat.');
    }
}
