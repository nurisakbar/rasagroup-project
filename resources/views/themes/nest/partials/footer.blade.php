    <footer class="main">
    @if(request()->routeIs('home'))
        <section class="newsletter mb-15 wow animate__animated animate__fadeIn">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="position-relative newsletter-inner">
                            <div class="newsletter-content">
                                <h2 class="mb-20">
                                    Dapatkan info terbaru & <br />
                                    promo menarik dari kami
                                </h2>
                                <p class="mb-45">Mulai belanja kebutuhan sirup Anda dengan <span class="text-brand">Rasa Group</span></p>
                                <form class="form-subcriber d-flex">
                                    <input type="email" placeholder="Alamat email Anda" />
                                    <button class="btn" type="submit">Berlangganan</button>
                                </form>
                            </div>
                            <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-9.png') }}" alt="newsletter" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="featured section-padding">
            <div class="container">
                <div class="row">
                    <div class="col-lg-1-5 col-md-4 col-12 col-sm-6 mb-md-4 mb-xl-0">
                        <div class="banner-left-icon d-flex align-items-center wow animate__animated animate__fadeInUp" data-wow-delay="0">
                            <div class="banner-icon">
                                <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-1.svg') }}" alt="" />
                            </div>
                            <div class="banner-text">
                                <h3 class="icon-box-title">Harga & Penawaran Terbaik</h3>
                                <p>Hemat lebih banyak setiap hari</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-1-5 col-md-4 col-12 col-sm-6">
                        <div class="banner-left-icon d-flex align-items-center wow animate__animated animate__fadeInUp" data-wow-delay=".1s">
                            <div class="banner-icon">
                                <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-2.svg') }}" alt="" />
                            </div>
                            <div class="banner-text">
                                <h3 class="icon-box-title">Pengiriman Nasional</h3>
                                <p>Menuju ke seluruh Indonesia</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-1-5 col-md-4 col-12 col-sm-6">
                        <div class="banner-left-icon d-flex align-items-center wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                            <div class="banner-icon">
                                <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-3.svg') }}" alt="" />
                            </div>
                            <div class="banner-text">
                                <h3 class="icon-box-title">Promo Harian</h3>
                                <p>Diskon spesial setiap hari</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-1-5 col-md-4 col-12 col-sm-6">
                        <div class="banner-left-icon d-flex align-items-center wow animate__animated animate__fadeInUp" data-wow-delay=".3s">
                            <div class="banner-icon">
                                <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-4.svg') }}" alt="" />
                            </div>
                            <div class="banner-text">
                                <h3 class="icon-box-title">Koleksi Lengkap</h3>
                                <p>Pilihan sirup terlengkap</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-1-5 col-md-4 col-12 col-sm-6">
                        <div class="banner-left-icon d-flex align-items-center wow animate__animated animate__fadeInUp" data-wow-delay=".4s">
                            <div class="banner-icon">
                                <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-5.svg') }}" alt="" />
                            </div>
                            <div class="banner-text">
                                <h3 class="icon-box-title">Pengembalian Mudah</h3>
                                <p>Garansi kualitas produk</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
        <section class="section-padding footer-mid">
            <div class="container pt-15 pb-20">
                <div class="row">
                    <div class="col">
                        <div class="widget-about font-md mb-md-3 mb-lg-3 mb-xl-0 wow animate__animated animate__fadeInUp" data-wow-delay="0">
                            <div class="logo mb-30">
                                <a href="{{ route('home') }}" class="mb-15"><h3 class="fw-bold mb-0 text-brand">Rasa<span class="text-dark">Group</span></h3></a>
                                <p class="font-lg text-heading">Produsen sirup premium berkualitas tinggi untuk kebutuhan industri dan rumah tangga.</p>
                            </div>
                            <ul class="contact-infor">
                                <li><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-location.svg') }}" alt="" /><strong>Alamat: </strong> <span>Jl. Rasa Manis No. 123, Jakarta Selatan</span></li>
                                <li><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-contact.svg') }}" alt="" /><strong>Telepon:</strong><span>0813-1234-5678</span></li>
                                <li><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-email-2.svg') }}" alt="" /><strong>Email:</strong><span>info@rasagroup.com</span></li>
                                <li><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-clock.svg') }}" alt="" /><strong>Jam Kerja:</strong><span>08:00 - 17:00, Senin - Sabtu</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="footer-link-widget col wow animate__animated animate__fadeInUp" data-wow-delay=".1s">
                        <h4 class=" widget-title">Perusahaan</h4>
                        <ul class="footer-list mb-sm-5 mb-md-0">
                            <li><a href="{{ route('about') }}">Tentang Kami</a></li>
                            <li><a href="{{ route('contact') }}">Kontak Kami</a></li>
                            <li><a href="#">Syarat & Ketentuan</a></li>
                            <li><a href="#">Kebijakan Privasi</a></li>
                        </ul>
                    </div>
                    <div class="footer-link-widget col wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                        <h4 class="widget-title">Akun</h4>
                        <ul class="footer-list mb-sm-5 mb-md-0">
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('cart.index') }}">Keranjang Saya</a></li>
                            <li><a href="{{ route('buyer.dashboard') }}">Dashboard</a></li>
                        </ul>
                    </div>
                    <div class="footer-link-widget col wow animate__animated animate__fadeInUp" data-wow-delay=".3s">
                        <h4 class="widget-title">Link Cepat</h4>
                        <ul class="footer-list mb-sm-5 mb-md-0">
                            {{-- <li><a href="{{ route('hubs.index') }}">Distributor</a></li> --}}
                            <li><a href="{{ route('products.index') }}">Katalog Produk</a></li>
                            <li><a href="#">Promo</a></li>
                        </ul>
                    </div>
                    <div class="footer-link-widget col wow animate__animated animate__fadeInUp" data-wow-delay=".4s">
                        <h4 class="widget-title">Produk Populer</h4>
                        <ul class="footer-list mb-sm-5 mb-md-0">
                            <li><a href="#">Sirup Buah</a></li>
                            <li><a href="#">Sirup Kopi</a></li>
                            <li><a href="#">Sirup Dessert</a></li>
                        </ul>
                    </div>
                    <div class="footer-link-widget widget-install-app col wow animate__animated animate__fadeInUp" data-wow-delay=".5s">
                        <h4 class="widget-title">Metode Pembayaran</h4>
                        <img class="" src="{{ asset('themes/nest-frontend/assets/imgs/theme/payment-method.png') }}" alt="" />
                    </div>
                </div>
            </div>
        </section>
        <div class="container pb-30 wow animate__animated animate__fadeInUp" data-wow-delay="0">
            <div class="row align-items-center">
                <div class="col-12 mb-30">
                    <div class="footer-bottom"></div>
                </div>
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <p class="font-sm mb-0">&copy; {{ date('Y') }}, <strong class="text-brand">Rasa Group</strong> - All rights reserved</p>
                </div>
                <div class="col-xl-4 col-lg-6 text-center d-none d-xl-block">
                    <div class="hotline d-lg-inline-flex mr-30">
                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/phone-call.svg') }}" alt="hotline" />
                        <p>0813-1234-5678<span>Layanan Pelanggan</span></p>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-6 col-md-6 text-end d-none d-md-block">
                    <div class="mobile-social-icon">
                        <h6>Ikuti Kami</h6>
                        <a href="#"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-facebook-white.svg') }}" alt="" /></a>
                        <a href="#"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-instagram-white.svg') }}" alt="" /></a>
                        <a href="#"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-youtube-white.svg') }}" alt="" /></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
