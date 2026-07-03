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
                <div class="mobile-menu-wrap mobile-header-border">
                    <!-- mobile menu start -->
                    @php
                        $distributorMainMenuUrl = ! auth()->check()
                            ? route('login')
                            : (auth()->user()->isDistributor()
                                ? route('buyer.dashboard')
                                : route('buyer.distributor.apply'));
                    @endphp
                    <nav>
                        <ul class="mobile-menu font-heading">
                            <li><a href="{{ route('home') }}">Halaman Utama</a></li>
                            <li><a href="{{ route('products.index') }}">Katalog Produk</a></li>
                            <li><a href="{{ route('promo.index') }}">Promo</a></li>
                            <li><a href="{{ route('menus.index') }}">Menu Paket</a></li>
                            <li><a href="{{ route('contact') }}">Hubungi Kami</a></li>
                            <li><a href="{{ route('information-channels.index') }}">Saluran Informasi</a></li>



                        </ul>
                    </nav>
                    <!-- mobile menu end -->
                </div>

                <div class="mobile-social-icon mt-40 mb-50 pt-20 border-top">
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
