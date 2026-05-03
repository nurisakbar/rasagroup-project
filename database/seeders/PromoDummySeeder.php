<?php

namespace Database\Seeders;

use App\Models\Promo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromoDummySeeder extends Seeder
{
    /** Path relatif ke `public/` → sama dengan /themes/nest-frontend/assets/... */
    private const DUMMY_IMAGE = 'themes/nest-frontend/assets/imgs/banner/banner-7.png';

    /**
     * 5 contoh promo dummy (aman dijalankan ulang: cocokkan lewat kode_promo).
     */
    public function run(): void
    {
        $now = Carbon::now();

        $promos = [
            [
                'kode_promo' => 'DUMMY-WELCOME',
                'judul_promo' => 'Welcome Pack — diskon pembelian pertama',
                'deskripsi' => '<p>Potongan khusus pelanggan baru. Berlaku untuk pembelian pertama di marketplace.</p>',
                'harga' => 25000,
                'awal' => $now->copy()->subDays(3)->setTime(8, 0),
                'akhir' => $now->copy()->addDays(14)->setTime(23, 59),
            ],
            [
                'kode_promo' => 'DUMMY-GAJIAN',
                'judul_promo' => 'Promo Gajian — hemat hingga 50 rb',
                'deskripsi' => '<p>Periode gajian: gunakan kode di checkout. Tidak digabung dengan promo lain.</p>',
                'harga' => 50000,
                'awal' => $now->copy()->subDay()->setTime(0, 0),
                'akhir' => $now->copy()->addDays(7)->setTime(23, 59),
            ],
            [
                'kode_promo' => 'DUMMY-WEEKEND',
                'judul_promo' => 'Weekend Flash — dua hari saja',
                'deskripsi' => '<p>Flash sale akhir pekan. Stok terbatas.</p>',
                'harga' => 15000,
                'awal' => $now->copy()->next(Carbon::SATURDAY)->setTime(6, 0),
                'akhir' => $now->copy()->next(Carbon::SATURDAY)->addDay()->setTime(21, 0),
            ],
            [
                'kode_promo' => 'DUMMY-BUNDLING',
                'judul_promo' => 'Bundling Sirup — beli 3 bayar 2',
                'deskripsi' => '<p>Khusus kategori sirup. Otomatis di keranjang saat syarat terpenuhi.</p>',
                'harga' => 35000,
                'awal' => $now->copy()->subHours(12),
                'akhir' => $now->copy()->addDays(30)->setTime(23, 59),
            ],
            [
                'kode_promo' => 'DUMMY-LOYALTY',
                'judul_promo' => 'Member Loyalty — cashback poin',
                'deskripsi' => '<p>Untuk member tier Gold ke atas. Cashback dalam bentuk poin.</p>',
                'harga' => 10000,
                'awal' => $now->copy()->startOfMonth()->setTime(0, 0),
                'akhir' => $now->copy()->endOfMonth()->setTime(23, 59),
            ],
        ];

        foreach ($promos as $row) {
            $slug = Str::slug($row['judul_promo']);
            $base = $slug;
            $n = 1;
            while (
                Promo::where('slug', $slug)
                    ->where('kode_promo', '!=', $row['kode_promo'])
                    ->exists()
            ) {
                $slug = $base.'-'.$n;
                $n++;
            }

            Promo::updateOrCreate(
                ['kode_promo' => $row['kode_promo']],
                [
                    'judul_promo' => $row['judul_promo'],
                    'slug' => $slug,
                    'deskripsi' => $row['deskripsi'],
                    'harga' => $row['harga'],
                    'awal' => $row['awal'],
                    'akhir' => $row['akhir'],
                    'image' => self::DUMMY_IMAGE,
                ]
            );
        }
    }
}
