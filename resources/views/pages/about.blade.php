@extends('layouts.shop')

@section('title', 'Tentang Kami')

@section('content')
    <main class="main pages">
        <div class="page-header breadcrumb-wrap">
            <div class="container">
                <div class="breadcrumb">
                    <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
                    <span></span> Halaman <span></span> Tentang Kami
                </div>
            </div>
        </div>
        <div class="page-content pt-50">
            <div class="container">
                <div class="row">
                    <div class="col-xl-10 col-lg-12 m-auto">
                        <section class="row align-items-stretch mb-50">
                            <div class="col-lg-6">
                                <img src="{{ asset('themes/nest-frontend/assets/imgs/page/about10.jpg') }}" alt="Tentang Rasa Group" class="border-radius-15 mb-md-3 mb-lg-0 mb-sm-4 shadow-sm" style="width: 100%; height: 100%; object-fit: cover;" />
                            </div>
                            <div class="col-lg-6">
                                <div class="pl-25">
                                    <h2 class="mb-30">Selamat Datang di Rasa Group</h2>
                                    <p class="mb-25" style="text-align: justify;">RASA Group adalah perusahaan beverage yang berkomitmen menghadirkan produk dan solusi berkualitas bagi industri F&B Indonesia. Melalui inovasi, kualitas, dan pemahaman terhadap tren pasar, kami membantu bisnis menghadirkan pengalaman minuman yang relevan, konsisten, dan berdaya saing.</p>
                                    <p class="mb-50" style="text-align: justify;">Dengan pengalaman lebih dari satu dekade, kami telah menjadi mitra terpercaya bagi coffee shop, cafe, restoran, hotel, dan berbagai pelaku usaha F&B dalam mengembangkan menu minuman yang bernilai tinggi serta mendukung pertumbuhan bisnis yang berkelanjutan.</p>
                                    <div class="carausel-3-columns-cover position-relative">
                                        <div id="carausel-3-columns-arrows"></div>
                                        <div class="carausel-3-columns" id="carausel-3-columns">
                                            <div class="px-2"><img class="border-radius-15" src="{{ asset('themes/nest-frontend/assets/imgs/page/about4.jpg') }}" alt="Galeri 1" /></div>
                                            <div class="px-2"><img class="border-radius-15" src="{{ asset('themes/nest-frontend/assets/imgs/page/about2.jpg') }}" alt="Galeri 2" /></div>
                                            <div class="px-2"><img class="border-radius-15" src="{{ asset('themes/nest-frontend/assets/imgs/page/about3.jpg') }}" alt="Galeri 3" /></div>
                                            <div class="px-2"><img class="border-radius-15" src="{{ asset('themes/nest-frontend/assets/imgs/page/about3.jpg') }}" alt="Galeri 3" /></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <section class="text-center mb-50">
                            <h2 class="title style-3 mb-40 text-center">Apa yang Kami Sediakan?</h2>
                            <div class="row">
                                <div class="col-lg-4 col-md-6 mb-24">
                                    <div class="featured-card">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-1.svg') }}" alt="Best Prices" />
                                        <h4>Harga & Penawaran Terbaik</h4>
                                        <p>Memberikan harga yang kompetitif dengan berbagai penawaran menarik untuk pelanggan setia kami.</p>
                                        <a href="#" class="text-brand">Baca selengkapnya</a>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-24">
                                    <div class="featured-card">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-2.svg') }}" alt="Wide Assortment" />
                                        <h4>Varian Rasa Lengkap</h4>
                                        <p>Lebih dari 20 varian rasa sirup yang dapat dipilih sesuai dengan kebutuhan bisnis atau pribadi Anda.</p>
                                        <a href="#" class="text-brand">Baca selengkapnya</a>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-24">
                                    <div class="featured-card">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-3.svg') }}" alt="Free Delivery" />
                                        <h4>Pengiriman Cepat</h4>
                                        <p>Layanan pengiriman cepat ke seluruh pelosok Indonesia dengan jangkauan luas di kota-kota besar.</p>
                                        <a href="#" class="text-brand">Baca selengkapnya</a>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-24">
                                    <div class="featured-card">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-4.svg') }}" alt="Easy Returns" />
                                        <h4>Pengembalian Mudah</h4>
                                        <p>Komitmen kami terhadap kepuasan pelanggan dengan proses klaim dan pengembalian yang transparan.</p>
                                        <a href="#" class="text-brand">Baca selengkapnya</a>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-24">
                                    <div class="featured-card">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-5.svg') }}" alt="Satisfaction" />
                                        <h4>Kepuasan 100%</h4>
                                        <p>Kualitas produk yang terjamin untuk memberikan pengalaman rasa terbaik di setiap tetesnya.</p>
                                        <a href="#" class="text-brand">Baca selengkapnya</a>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-24">
                                    <div class="featured-card">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-6.svg') }}" alt="Daily Deals" />
                                        <h4>Penawaran Harian</h4>
                                        <p>Dapatkan update promo dan diskon khusus setiap hari untuk pembelian melalui website kami.</p>
                                        <a href="#" class="text-brand">Baca selengkapnya</a>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <section class="row align-items-center mb-50">
                            <div class="row mb-50 align-items-center">
                                <div class="col-lg-7 pr-30">
                                    <img src="{{ asset('themes/nest-frontend/assets/imgs/page/about-5.png') }}" alt="Performa Kami" class="border-radius-15 mb-md-3 mb-lg-0 mb-sm-4" />
                                </div>
                                <div class="col-lg-5">
                                    <h4 class="mb-20 text-muted">Performa Kami</h4>
                                    <h1 class="heading-1 mb-40">Solusi Minuman untuk Bisnis F&B Anda</h1>
                                    <p class="mb-30" style="text-align: justify;">Kami menghadirkan rangkaian produk dan solusi minuman yang dirancang untuk membantu bisnis F&B menciptakan menu yang inovatif, konsisten, dan memiliki daya saing tinggi. Dari kreasi premium hingga kebutuhan operasional harian, kami mendukung setiap langkah pertumbuhan bisnis pelanggan.</p>
                                    <p style="text-align: justify;">Dengan komitmen terhadap kualitas dan inovasi, kami terus mengembangkan produk yang mampu menjawab tren pasar sekaligus menghadirkan pengalaman rasa terbaik dalam setiap sajian.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4 pr-30 mb-md-5 mb-lg-0 mb-sm-5">
                                    <h3 class="mb-30">Siapa Kami</h3>
                                    <p style="text-align: justify;">RASA Group adalah perusahaan beverage yang berfokus pada pengembangan produk dan solusi minuman untuk industri F&B Indonesia. Dengan mengedepankan inovasi, kualitas, dan pemahaman terhadap tren pasar, kami membantu pelanggan menghadirkan pengalaman minuman yang relevan dan bernilai.</p>
                                </div>
                                <div class="col-lg-4 pr-30 mb-md-5 mb-lg-0 mb-sm-5">
                                    <h3 class="mb-30">Sejarah Kami</h3>
                                    <p style="text-align: justify;">Sejak didirikan pada tahun 2010, RASA Group terus berkembang bersama industri F&B Indonesia. Berawal dari visi untuk menghadirkan produk minuman berkualitas, kami kini dipercaya oleh ribuan pelanggan dari berbagai segmen, termasuk coffee shop, cafe, restoran, hotel, dan pelaku usaha minuman di seluruh Indonesia.</p>
                                </div>
                                <div class="col-lg-4">
                                    <h3 class="mb-30">Misi Kami</h3>
                                    <p style="text-align: justify;">Menjadi mitra terpercaya bagi pertumbuhan bisnis F&B melalui produk berkualitas, inovasi berkelanjutan, dan pelayanan yang unggul. Kami berkomitmen menciptakan solusi beverage yang mampu memberikan nilai tambah bagi pelanggan dan konsumen akhir.</p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
            <section class="container mb-50 d-none d-md-block">
                <div class="row about-count text-center">
                    <div class="col-lg-1-5 col-md-6 mb-lg-0 mb-md-5">
                        <h1 class="heading-1 text-brand"><span class="count">14</span>+</h1>
                        <h4 class="text-muted">Tahun Berdiri</h4>
                    </div>
                    <div class="col-lg-1-5 col-md-6">
                        <h1 class="heading-1 text-brand"><span class="count">36</span>k+</h1>
                        <h4 class="text-muted">Pelanggan Puas</h4>
                    </div>
                    <div class="col-lg-1-5 col-md-6">
                        <h1 class="heading-1 text-brand"><span class="count">58</span>+</h1>
                        <h4 class="text-muted">Proyek Selesai</h4>
                    </div>
                    <div class="col-lg-1-5 col-md-6 text-center">
                        <h1 class="heading-1 text-brand"><span class="count">24</span>+</h1>
                        <h4 class="text-muted">Tim Ahli</h4>
                    </div>
                    <div class="col-lg-1-5 text-center d-none d-lg-block">
                        <h1 class="heading-1 text-brand"><span class="count">500</span>+</h1>
                        <h4 class="text-muted">Kota Terjangkau</h4>
                    </div>
                </div>
            </section>

        </div>
    </main>
@endsection
