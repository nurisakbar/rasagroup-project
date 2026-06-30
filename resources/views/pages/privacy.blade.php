@extends('layouts.shop')

@section('title', 'Kebijakan Privasi')

@section('content')
    <main class="main pages">
        <div class="page-header breadcrumb-wrap">
            <div class="container">
                <div class="breadcrumb">
                    <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
                    <span></span> Halaman <span></span> Kebijakan Privasi
                </div>
            </div>
        </div>
        <div class="page-content pt-50 pb-50">
            <div class="container">
                <div class="row">
                    <div class="col-xl-10 col-lg-12 m-auto">
                        <h2 class="mb-30">Kebijakan Privasi</h2>
                        <div class="single-content">
                            <p>Kebijakan Privasi ini menjelaskan bagaimana Rasa Group mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda saat Anda menggunakan layanan atau situs web kami. Privasi Anda sangat penting bagi kami, dan kami berkomitmen untuk melindungi data Anda sesuai dengan hukum yang berlaku.</p>

                            <h4>1. Informasi yang Kami Kumpulkan</h4>
                            <p>Kami dapat mengumpulkan beberapa informasi pribadi saat Anda menggunakan layanan kami, termasuk namun tidak terbatas pada:</p>
                            <ul>
                                <li>Nama lengkap</li>
                                <li>Alamat email</li>
                                <li>Nomor telepon / WhatsApp</li>
                                <li>Alamat pengiriman</li>
                                <li>Informasi transaksi (jika berlaku)</li>
                            </ul>

                            <h4>2. Bagaimana Kami Menggunakan Informasi Anda</h4>
                            <p>Informasi yang kami kumpulkan dapat digunakan untuk:</p>
                            <ul>
                                <li>Menyediakan, mengoperasikan, dan memelihara layanan kami</li>
                                <li>Memproses transaksi dan mengirimkan pesanan Anda</li>
                                <li>Mengirimkan notifikasi terkait layanan (misalnya via WhatsApp/Email)</li>
                                <li>Meningkatkan pengalaman pengguna di situs web kami</li>
                                <li>Berkomunikasi dengan Anda mengenai promosi atau informasi terbaru (jika Anda setuju)</li>
                            </ul>

                            <h4>3. Perlindungan Informasi</h4>
                            <p>Kami menerapkan berbagai langkah keamanan untuk menjaga keamanan informasi pribadi Anda. Informasi sensitif dikumpulkan melalui saluran yang aman dan dienkripsi.</p>

                            <h4>4. Pembagian Informasi kepada Pihak Ketiga</h4>
                            <p>Kami tidak menjual, memperdagangkan, atau menyewakan informasi identifikasi pribadi Anda kepada pihak lain. Kami dapat membagikan informasi Anda dengan mitra tepercaya yang membantu kami beroperasi (seperti kurir pengiriman atau layanan pembayaran), asalkan mereka setuju untuk menjaga kerahasiaan informasi tersebut.</p>

                            <h4>5. Perubahan Kebijakan Privasi</h4>
                            <p>Rasa Group berhak memperbarui kebijakan privasi ini kapan saja. Setiap perubahan akan diumumkan di halaman ini. Dengan terus menggunakan layanan kami, Anda menyetujui kebijakan privasi yang telah direvisi.</p>

                            <h4>6. Hubungi Kami</h4>
                            <p>Jika Anda memiliki pertanyaan lebih lanjut mengenai kebijakan privasi ini, silakan hubungi kami melalui halaman <a href="{{ route('contact') }}">Kontak</a> kami.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
