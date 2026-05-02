    <header class="header-area header-style-1 header-height-2">
        <div class="mobile-promotion">
            <span>Pembukaan besar-besaran, diskon hingga <strong>15%</strong> untuk semua item. Tinggal <strong>3 hari</strong> lagi</span>
        </div>
        <div class="header-middle header-middle-ptb-1 d-none d-lg-block">
            <div class="container">
                <div class="header-wrap">
                    <div class="logo logo-width-1">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/logorasa.png') }}" alt="logo" style="max-width: 180px;" />
                        </a>
                    </div>
                    <div class="header-right">
                        <div class="search-style-2">
                            <form class="rg-search-no-category" action="{{ route('products.index') }}" method="GET">
                                <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari produk..." autocomplete="off" enterkeyhint="search" />
                                <button type="submit" aria-label="Cari produk"><i class="fi-rs-search"></i></button>
                            </form>
                        </div>
                        <div class="header-action-right">
                            <div class="header-action-2">
                                <div class="search-location">
                                    <form action="#">
                                        <select class="select-active">
                                            <option>Lokasi Anda</option>
                                            <option>Alabama</option>
                                            <option>Alaska</option>
                                            <option>Arizona</option>
                                            <option>Delaware</option>
                                            <option>Florida</option>
                                            <option>Georgia</option>
                                            <option>Hawaii</option>
                                            <option>Indiana</option>
                                            <option>Maryland</option>
                                            <option>Nevada</option>
                                            <option>New Jersey</option>
                                            <option>New Mexico</option>
                                            <option>New York</option>
                                        </select>
                                    </form>
                                </div>
                                {{-- <div class="header-action-icon-2">
                                    <a href="shop-compare.html">
                                        <img class="svgInject" alt="Nest" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-compare.svg') }}" />
                                        <span class="pro-count blue">3</span>
                                    </a>
                                    <a href="shop-compare.html"><span class="lable ml-0">Compare</span></a>
                                </div>
                                <div class="header-action-icon-2">
                                    <a href="shop-wishlist.html">
                                        <img class="svgInject" alt="Nest" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-heart.svg') }}" />
                                        <span class="pro-count blue">6</span>
                                    </a>
                                    <a href="shop-wishlist.html"><span class="lable">Wishlist</span></a>
                                </div> --}}
                                <div class="header-action-icon-2">
                                    @php
                                        $cartCount = auth()->check() 
                                            ? \App\Models\Cart::where('user_id', auth()->id())->where('cart_type', 'regular')->sum('quantity')
                                            : \App\Models\Cart::where('session_id', session()->getId())->where('cart_type', 'regular')->sum('quantity');
                                    @endphp
                                    <a class="mini-cart-icon" href="{{ route('cart.index') }}">
                                        <img alt="Nest" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-cart.svg') }}" />
                                        <span class="pro-count blue">{{ $cartCount }}</span>
                                    </a>
                                    <a href="{{ route('cart.index') }}"><span class="lable">Keranjang</span></a>
                                    <div class="cart-dropdown-wrap cart-dropdown-hm2">
                                        @include('themes.nest.partials.mini-cart')
                                    </div>
                                </div>
                                <div class="header-action-icon-2">
                                    <a href="{{ auth()->check() ? route('buyer.dashboard') : route('login') }}">
                                        <img class="svgInject" alt="Nest" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-user.svg') }}" />
                                    </a>
                                    <a href="{{ auth()->check() ? route('buyer.dashboard') : route('login') }}"><span class="lable ml-0">Akun</span></a>
                                    @auth
                                    <div class="cart-dropdown-wrap cart-dropdown-hm2 account-dropdown">
                                        <ul>
                                            <li>
                                                <a href="{{ route('buyer.dashboard') }}"><i class="fi fi-rs-user mr-10"></i>Akun Saya</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('buyer.orders.index') }}"><i class="fi fi-rs-shopping-bag mr-10"></i>Pesanan Saya</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('buyer.addresses.index') }}"><i class="fi fi-rs-marker mr-10"></i>Alamat Saya</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('buyer.profile') }}"><i class="fi fi-rs-settings-sliders mr-10"></i>Pengaturan</a>
                                            </li>
                                            @if(Auth::user()->isDistributor())
                                            <li>
                                                <a href="{{ route('distributor.manage-orders.index') }}"><i class="fi fi-rs-shopping-bag mr-10"></i>Kelola Pesanan</a>
                                            </li>
                                            @endif
                                            <li>
                                                <form method="POST" action="{{ route('logout') }}" id="logout-form-header">
                                                    @csrf
                                                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();">
                                                        <i class="fi fi-rs-sign-out mr-10"></i>Keluar
                                                    </a>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-bottom header-bottom-bg-color sticky-bar">
            <div class="container">
                <div class="header-wrap header-space-between position-relative">
                    <div class="logo logo-width-1 d-block d-lg-none">
                        <a href="/"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/logorasa.png') }}" alt="logo" /></a>
                    </div>
                    <div class="header-nav d-none d-lg-flex">

                        <div class="main-categori-wrap d-none d-lg-block">
                            <a class="categories-button-active" href="#" style="white-space: nowrap;">
                                <span class="fi-rs-apps"></span> <span class="et">Jelajahi</span> Semua Brand
                                <i class="fi-rs-angle-down"></i>
                            </a>
                            <div class="categories-dropdown-wrap categories-dropdown-active-large font-heading">
                                <div class="d-flex categori-dropdown-inner">
                                    @php
                                        $allBrands = \App\Models\Brand::active()->get();
                                        $count = $allBrands->count();
                                        $half = ceil($count / 2);
                                        $col1 = $allBrands->take($half);
                                        $col2 = $allBrands->skip($half);
                                    @endphp
                                    <ul>
                                        @foreach($col1 as $brand)
                                            <li>
                                                <a class="{{ request('brand') === $brand->slug ? 'rg-brand-active' : '' }}" href="{{ route('products.index', ['brand' => $brand->slug]) }}">
                                                    <img src="{{ $brand->logo_url ?? asset('themes/nest-frontend/assets/imgs/theme/icons/category-1.svg') }}" alt="" />
                                                    {{ $brand->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <ul class="end">
                                        @foreach($col2 as $brand)
                                            <li>
                                                <a class="{{ request('brand') === $brand->slug ? 'rg-brand-active' : '' }}" href="{{ route('products.index', ['brand' => $brand->slug]) }}">
                                                    <img src="{{ $brand->logo_url ?? asset('themes/nest-frontend/assets/imgs/theme/icons/category-6.svg') }}" alt="" />
                                                    {{ $brand->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="more_categories"><span class="icon"></span> <span class="heading-sm-1">Lihat Semua Brand...</span></div>
                            </div>
                        </div>
                        <div class="main-menu main-menu-padding-1 main-menu-lh-2 d-none d-lg-block font-heading">
                            <nav>
                                <ul>
                                    <li>
                                        <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Halaman Utama</a>
                                    </li>
                                    <li>
                                        <a class="{{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Katalog Produk</a>
                                    </li>
                                    <li>
                                        <a class="{{ request()->routeIs('promo.index') ? 'active' : '' }}" href="{{ route('promo.index') }}">Promo</a>
                                    </li>
                                    {{-- <li>
                                        <a class="{{ request()->routeIs('hubs.*') ? 'active' : '' }}" href="{{ route('hubs.index') }}">Distributor</a>
                                    </li> --}}
                                    <li>
                                        <a class="{{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Hubungi Kami</a>
                                    </li>
                                    <li>
                                        <a class="{{ request()->routeIs('information-channels.*') ? 'active' : '' }}" href="{{ route('information-channels.index') }}">Saluran Informasi</a>
                                    </li>
                                    @auth
                                        @if(Auth::user()->isDistributor())
                                            <li>
                                                <a class="{{ request()->routeIs('distributor.manage-orders.index') ? 'active' : '' }}" href="{{ route('distributor.manage-orders.index') }}">Distributor</a>
                                            </li>
                                        @elseif(Auth::user()->isDriippreneur())
                                            <li>
                                                <a class="{{ request()->routeIs('buyer.affiliate.index') ? 'active' : '' }}" href="{{ route('buyer.affiliate.index') }}">Affiliator</a>
                                            </li>
                                        @else
                                            <li>
                                                <a class="{{ request()->routeIs('buyer.affiliate.index') ? 'active' : '' }}" href="{{ route('buyer.affiliate.index') }}">Affiliator</a>
                                            </li>
                                            <li>
                                                <a class="{{ request()->routeIs('distributor.manage-orders.index') ? 'active' : '' }}" href="{{ route('distributor.manage-orders.index') }}">Distributor</a>
                                            </li>
                                        @endif
                                    @else
                                        <li>
                                            <a class="{{ request()->routeIs('buyer.affiliate.index') ? 'active' : '' }}" href="{{ route('buyer.affiliate.index') }}">Affiliator</a>
                                        </li>
                                        <li>
                                            <a class="{{ request()->routeIs('distributor.manage-orders.index') ? 'active' : '' }}" href="{{ route('distributor.manage-orders.index') }}">Distributor</a>
                                        </li>
                                    @endauth
                                </ul>
                            </nav>
                        </div>
                    </div>
                    {{-- <div class="hotline d-none d-lg-flex">
                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-headphone.svg') }}" alt="hotline" />
                        <p style="white-space: nowrap;">+62 812-3456-7890<span style="display: block;">24/7 Support Center</span></p>
                    </div> --}}
                    <div class="header-action-icon-2 d-block d-lg-none">
                        <div class="burger-icon burger-icon-white">
                            <span class="burger-icon-top"></span>
                            <span class="burger-icon-mid"></span>
                            <span class="burger-icon-bottom"></span>
                        </div>
                    </div>
                    <div class="header-action-right d-block d-lg-none">
                        <div class="header-action-2">
                            {{-- <div class="header-action-icon-2">
                                <a href="shop-wishlist.html">
                                    <img alt="Nest" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-heart.svg') }}" />
                                    <span class="pro-count white">4</span>
                                </a>
                            </div> --}}
                            <div class="header-action-icon-2">
                                <a class="mini-cart-icon" href="{{ route('cart.index') }}">
                                    <img alt="Nest" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-cart.svg') }}" />
                                    <span class="pro-count white">{{ $cartCount }}</span>
                                </a>
                                <div class="cart-dropdown-wrap cart-dropdown-hm2">
                                    @include('themes.nest.partials.mini-cart')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
