<div class="mobile-header-active mobile-header-wrapper-style">
        <div class="mobile-header-wrapper-inner">
            <div class="mobile-header-top">
                <div class="mobile-header-logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/logorasa.png') }}" alt="logo" style="max-width: 120px;" />
                    </a>
                </div>
                <div class="mobile-menu-close close-style-wrap close-style-position-inherit">
                    <button class="close-style search-close">
                        <i class="icon-top"></i>
                        <i class="icon-bottom"></i>
                    </button>
                </div>
            </div>
            <div class="mobile-header-content-area">
                <div class="mobile-search search-style-3 mobile-header-border">
                    <form action="#">
                        <input type="text" placeholder="Cari produk…" />
                        <button type="submit"><i class="fi-rs-search"></i></button>
                    </form>
                </div>
                <div class="mobile-menu-wrap mobile-header-border">
                    <!-- mobile menu start -->
                    <nav>
                        <ul class="mobile-menu font-heading">
                            <li><a href="{{ route('home') }}">Halaman Utama</a></li>
                            <li><a href="{{ route('products.index') }}">Katalog Produk</a></li>
                            <li><a href="{{ route('promo.index') }}">Promo</a></li>
                            <li><a href="{{ route('hubs.index') }}">Distributor</a></li>
                            <li><a href="{{ route('contact') }}">Hubungi Kami</a></li>
                            <li><a href="{{ route('information-channels.index') }}">Saluran Informasi</a></li>
                            <li><a href="{{ route('buyer.affiliate.index') }}">Affiliator</a></li>
                            <li><a href="{{ route('distributor.manage-orders.index') }}">Distributor</a></li>


                        </ul>
                    </nav>
                    <!-- mobile menu end -->
                </div>
                <div class="mobile-header-info-wrap">
                    <div class="single-mobile-header-info">
                        <a href="{{ route('contact') }}"><i class="fi-rs-marker"></i> Lokasi kami </a>
                    </div>
                    @auth
                    <div class="single-mobile-header-info">
                        <a href="{{ route('buyer.dashboard') }}"><i class="fi-rs-user"></i>Akun Saya </a>
                    </div>
                    @if(Auth::user()->isDistributor())
                    <div class="single-mobile-header-info">
                        <a href="{{ route('distributor.manage-orders.index') }}"><i class="fi-rs-shopping-bag"></i>Kelola Pesanan </a>
                    </div>
                    @endif
                    <div class="single-mobile-header-info">
                        <form method="POST" action="{{ route('logout') }}" id="logout-form-mobile">
                            @csrf
                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();" style="display: flex; align-items: center;">
                                <i class="fi-rs-sign-out" style="margin-right: 10px;"></i>Keluar
                            </a>
                        </form>
                    </div>
                    @else
                    <div class="single-mobile-header-info">
                        <a href="{{ route('login') }}"><i class="fi-rs-user"></i> Masuk / Daftar </a>
                    </div>
                    @endauth
                    {{-- <div class="single-mobile-header-info">
                        <a href="#"><i class="fi-rs-headphones"></i>+62 812-3456-7890 </a>
                    </div> --}}
                </div>
                <div class="mobile-social-icon mb-50">
                    <h6 class="mb-15">Ikuti Kami</h6>
                    <a href="#"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-facebook-white.svg') }}" alt="" /></a>
                    <a href="#"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-twitter-white.svg') }}" alt="" /></a>
                    <a href="#"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-instagram-white.svg') }}" alt="" /></a>
                    <a href="#"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-pinterest-white.svg') }}" alt="" /></a>
                    <a href="#"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-youtube-white.svg') }}" alt="" /></a>
                </div>
                <div class="site-copyright">Copyright {{ date('Y') }} © Rasa Group. Seluruh hak cipta dilindungi.</div>
            </div>
        </div>
    </div>
    <!--End header-->
