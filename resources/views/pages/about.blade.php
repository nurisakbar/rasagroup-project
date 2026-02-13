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
                        <section class="row align-items-center mb-50">
                            <div class="col-lg-6">
                                <img src="{{ asset('themes/nest-frontend/assets/imgs/page/about-1.png') }}" alt="Tentang Rasa Group" class="border-radius-15 mb-md-3 mb-lg-0 mb-sm-4 shadow-sm" />
                            </div>
                            <div class="col-lg-6">
                                <div class="pl-25">
                                    <h2 class="mb-30">Selamat Datang di Rasa Group</h2>
                                    <p class="mb-25">Rasa Group didirikan pada tahun 2010 dengan visi menjadi produsen sirup berkualitas tinggi yang mampu menjangkau seluruh pelosok Indonesia. Berawal dari sebuah usaha kecil di Jakarta, kami terus berkembang hingga kini memiliki fasilitas produksi modern dengan standar kualitas internasional.</p>
                                    <p class="mb-50">Selama lebih dari 14 tahun perjalanan, kami telah melayani ribuan pelanggan dari berbagai kalangan, mulai dari pengusaha minuman, hotel, restoran, hingga konsumen rumah tangga. Kepercayaan pelanggan adalah motivasi kami untuk terus berinovasi dan meningkatkan kualitas produk.</p>
                                    <div class="carausel-3-columns-cover position-relative">
                                        <div id="carausel-3-columns-arrows"></div>
                                        <div class="carausel-3-columns" id="carausel-3-columns">
                                            <div class="px-2"><img class="border-radius-15" src="{{ asset('themes/nest-frontend/assets/imgs/page/about-2.png') }}" alt="Galeri 1" /></div>
                                            <div class="px-2"><img class="border-radius-15" src="{{ asset('themes/nest-frontend/assets/imgs/page/about-3.png') }}" alt="Galeri 2" /></div>
                                            <div class="px-2"><img class="border-radius-15" src="{{ asset('themes/nest-frontend/assets/imgs/page/about-4.png') }}" alt="Galeri 3" /></div>
                                            <div class="px-2"><img class="border-radius-15" src="{{ asset('themes/nest-frontend/assets/imgs/page/about-2.png') }}" alt="Galeri 4" /></div>
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
                                    <h1 class="heading-1 mb-40">Partner Solusi Sirup Berkualitas Anda</h1>
                                    <p class="mb-30">Kami berkomitmen untuk selalu menghadirkan produk dengan cita rasa terbaik yang dibuat dari bahan-bahan pilihan berkualitas tinggi.</p>
                                    <p>Kepercayaan pelanggan adalah motivasi kami untuk terus berinovasi dan meningkatkan kualitas produk di setiap tetesnya.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4 pr-30 mb-md-5 mb-lg-0 mb-sm-5">
                                    <h3 class="mb-30">Siapa Kami</h3>
                                    <p>Rasa Group adalah produsen sirup terkemuka yang berfokus pada kualitas dan inovasi rasa untuk memenuhi kebutuhan industri kuliner dan rumah tangga.</p>
                                </div>
                                <div class="col-lg-4 pr-30 mb-md-5 mb-lg-0 mb-sm-5">
                                    <h3 class="mb-30">Sejarah Kami</h3>
                                    <p>Dimulai dari usaha kecil di tahun 2010, kini kami melayani ribuan pelanggan di seluruh Indonesia dengan berbagai varian rasa premium.</p>
                                </div>
                                <div class="col-lg-4">
                                    <h3 class="mb-30">Misi Kami</h3>
                                    <p>Menghasilkan produk sirup berkualitas tinggi dengan standar keamanan pangan yang ketat dan memberikan pelayanan terbaik bagi pelanggan.</p>
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
            <div class="container">
                <div class="row">
                    <div class="col-xl-10 col-lg-12 m-auto">
                        <section class="mb-50">
                            <h2 class="title style-3 mb-40 text-center">Tim Kami</h2>
                            <div class="row">
                                <div class="col-lg-4 mb-lg-0 mb-md-5 mb-sm-5">
                                    <h6 class="mb-5 text-brand">Tim Kami</h6>
                                    <h1 class="mb-30">Kenali Tim Ahli Kami</h1>
                                    <p class="mb-30">Orang-orang hebat di balik kesuksesan Rasa Group yang berdedikasi tinggi untuk memberikan yang terbaik bagi Anda.</p>
                                    <p class="mb-30">Tim kami terdiri dari para ahli di bidang produksi, pengembangan rasa, dan pemasaran yang telah berpengalaman bertahun-tahun.</p>
                                    <a href="#" class="btn btn-brand">Lihat Semua Anggota</a>
                                </div>
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="team-card shadow-sm">
                                                <img src="{{ asset('themes/nest-frontend/assets/imgs/page/about-6.png') }}" alt="Ahmad Susanto" />
                                                <div class="content text-center">
                                                    <h4 class="mb-5">Ahmad Susanto</h4>
                                                    <span>CEO & Founder</span>
                                                    <div class="social-network mt-20">
                                                        <a href="#"><i class="bi bi-facebook"></i></a>
                                                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                                                        <a href="#"><i class="bi bi-instagram"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="team-card shadow-sm">
                                                <img src="{{ asset('themes/nest-frontend/assets/imgs/page/about-8.png') }}" alt="Siti Rahayu" />
                                                <div class="content text-center">
                                                    <h4 class="mb-5">Siti Rahayu</h4>
                                                    <span>Chief Operating Officer</span>
                                                    <div class="social-network mt-20">
                                                        <a href="#"><i class="bi bi-facebook"></i></a>
                                                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                                                        <a href="#"><i class="bi bi-instagram"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
