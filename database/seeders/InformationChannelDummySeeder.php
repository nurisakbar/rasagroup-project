<?php

namespace Database\Seeders;

use App\Models\InformationChannel;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InformationChannelDummySeeder extends Seeder
{
    /** Path relatif ke `public/` (gambar tema Nest). */
    private static function bannerImage(int $index): string
    {
        $n = (($index - 1) % 10) + 5;

        return 'themes/nest-frontend/assets/imgs/banner/banner-'.$n.'.png';
    }

    /**
     * 10 saluran informasi dummy (idempoten lewat slug).
     */
    public function run(): void
    {
        $now = Carbon::now();

        $rows = [
            [
                'slug' => 'dummy-info-pembaruan-pengiriman',
                'title' => 'Pembaruan ketentuan pengiriman antar hub',
                'description' => '<p>Berikut ringkasan perubahan SLA pengiriman untuk pesanan lintas hub. Mohon disesuaikan dengan jadwal operasional gudang Anda.</p><p>Perubahan berlaku mulai tanggal tertera di bawah.</p>',
                'target_audience' => 'all',
            ],
            [
                'slug' => 'dummy-info-jam-layanan-cs',
                'title' => 'Jam layanan customer service hari libur nasional',
                'description' => '<p>Tim kami tetap melayani chat dan email dengan jam terbatas pada tanggal merah. Untuk urusan mendesak silakan gunakan saluran prioritas di aplikasi.</p>',
                'target_audience' => 'customer',
            ],
            [
                'slug' => 'dummy-info-integrasi-qid',
                'title' => 'Pengumuman: penyesuaian integrasi QID & kode produk',
                'description' => '<p>Beberapa kode QID akan diselaraskan dengan master data terbaru. Tidak ada perubahan harga otomatis; mohon tinjau ulang daftar pesanan terjadwal.</p>',
                'target_audience' => 'distributor',
            ],
            [
                'slug' => 'dummy-info-panduan-promo',
                'title' => 'Panduan singkat klaim promo & kode voucher',
                'description' => '<p>Promo bersifat kumulatif hanya jika disebutkan di halaman promo. Cek masa berlaku jam (tanggal & waktu) sebelum checkout.</p>',
                'target_audience' => 'all',
            ],
            [
                'slug' => 'dummy-info-stok-produk',
                'title' => 'Informasi fluktuasi stok produk musiman',
                'description' => '<p>Produk tertentu mengalami keterlambatan restock dari prinsipal. Gunakan fitur notifikasi stok di katalog untuk mendapat pemberitahuan.</p>',
                'target_audience' => 'distributor',
            ],
            [
                'slug' => 'dummy-info-kebijakan-retur',
                'title' => 'Kebijakan retur & tukar barang terbaru',
                'description' => '<p>Dokumentasi foto wajib diunggah dalam 48 jam setelah barang diterima. Produk kedaluwarsa tidak dapat dikembalikan kecuali kesalahan pengiriman.</p>',
                'target_audience' => 'customer',
            ],
            [
                'slug' => 'dummy-info-pembayaran',
                'title' => 'Metode pembayaran yang didukung sementara',
                'description' => '<p>Beberapa channel pembayaran sedang dalam pemeliharaan bank. Daftar metode aktif dapat berubah sewaktu-waktu tanpa pemberitahuan panjang.</p>',
                'target_audience' => 'all',
            ],
            [
                'slug' => 'dummy-info-webinar-distributor',
                'title' => 'Undangan webinar: strategi penjualan paket menu',
                'description' => '<p>Khusus mitra distributor. Silakan daftar melalui tautan yang akan dikirim via email. Kuota terbatas.</p>',
                'target_audience' => 'distributor',
            ],
            [
                'slug' => 'dummy-info-keamanan-akun',
                'title' => 'Pengingat keamanan: aktifkan verifikasi dua langkah',
                'description' => '<p>Lindungi akun Anda dari akses tidak sah. Disarankan mengganti password berkala dan tidak membagikan OTP kepada siapa pun.</p>',
                'target_audience' => 'all',
            ],
            [
                'slug' => 'dummy-info-perawatan-server',
                'title' => 'Jendela pemeliharaan sistem (dampak minimal)',
                'description' => '<p>Pada rentang waktu terbatas, checkout dapat mengalami penundaan beberapa detik. Transaksi yang sudah berhasil tidak terpengaruh.</p>',
                'target_audience' => 'all',
            ],
        ];

        foreach ($rows as $i => $row) {
            InformationChannel::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'slug' => $row['slug'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'image' => self::bannerImage($i + 1),
                    'target_audience' => $row['target_audience'],
                    'start_date' => $now->copy()->subDays(14)->toDateString(),
                    'end_date' => $now->copy()->addYear()->toDateString(),
                    'is_active' => true,
                ]
            );
        }
    }
}
