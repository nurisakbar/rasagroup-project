    <header class="header-area header-style-1 header-height-2">
        <div class="mobile-promotion">
            <span>Grand opening, <strong>up to 15%</strong> off all items. Only <strong>3 days</strong> left</span>
        </div>
        <div class="header-top header-top-ptb-1 d-none d-lg-block">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-3 col-lg-4">
                        <div class="header-info">
                            <ul>
                                <li><a href="page-about.htlm">About Us</a></li>
                                <li><a href="page-account.html">My Account</a></li>
                                <li><a href="shop-wishlist.html">Wishlist</a></li>
                                <li><a href="shop-order.html">Order Tracking</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-4">
                        <div class="text-center">
                            <div id="news-flash" class="d-inline-block">
                                <ul>
                                    <li>100% Secure delivery without contacting the courier</li>
                                    <li>Supper Value Deals - Save more with coupons</li>
                                    <li>Trendy 25silver jewelry, save up 35% off today</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4">
                        <div class="header-info header-info-right">
                            <ul>
                                <li>Need help? Call Us: <strong class="text-brand" style="white-space: nowrap;"> +62 812-3456-7890</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-middle header-middle-ptb-1 d-none d-lg-block">
            <div class="container">
                <div class="header-wrap">
                    <div class="logo logo-width-1">
                        <a href="{{ route('home') }}" class="navbar-brand" style="font-weight: 700; font-size: 1.8rem; color: #253D4E !important; text-decoration: none;">
                            <span style="color: #3BB77E;">Rasa</span>Group
                        </a>
                    </div>
                    <div class="header-right">
                        <div class="search-style-2">
                            <form action="#">
                                <select class="select-active">
                                    <option>All Categories</option>
                                    <option>Milks and Dairies</option>
                                    <option>Wines & Alcohol</option>
                                    <option>Clothing & Beauty</option>
                                    <option>Pet Foods & Toy</option>
                                    <option>Fast food</option>
                                    <option>Baking material</option>
                                    <option>Vegetables</option>
                                    <option>Fresh Seafood</option>
                                    <option>Noodles & Rice</option>
                                    <option>Ice cream</option>
                                </select>
                                <input type="text" placeholder="Search for items..." />
                            </form>
                        </div>
                        <div class="header-action-right">
                            <div class="header-action-2">
                                <div class="search-location">
                                    <form action="#">
                                        <select class="select-active">
                                            <option>Your Location</option>
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
                                <div class="header-action-icon-2">
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
                                </div>
                                <div class="header-action-icon-2">
                                    <a class="mini-cart-icon" href="{{ route('cart.index') }}">
                                        <img alt="Nest" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-cart.svg') }}" />
                                        <span class="pro-count blue">2</span>
                                    </a>
                                    <a href="{{ route('cart.index') }}"><span class="lable">Cart</span></a>
                                    <div class="cart-dropdown-wrap cart-dropdown-hm2">
                                        <ul>
                                            @php
                                                $miniCarts = auth()->check() 
                                                    ? \App\Models\Cart::with('product')->where('user_id', auth()->id())->latest()->get()
                                                    : \App\Models\Cart::with('product')->where('session_id', session()->getId())->latest()->get();
                                                $miniCartTotal = $miniCarts->sum(function($item) { return $item->product->price * $item->quantity; });
                                            @endphp
                                            
                                            @forelse($miniCarts as $cartItem)
                                            <li>
                                                <div class="shopping-cart-img">
                                                    <a href="{{ route('products.show', $cartItem->product) }}">
                                                        <img alt="{{ $cartItem->product->name }}" src="{{ $cartItem->product->image_url ? $cartItem->product->image_url : asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" onerror="this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}'">
                                                    </a>
                                                </div>
                                                <div class="shopping-cart-title">
                                                    <h4><a href="{{ route('products.show', $cartItem->product) }}">{{ Str::limit($cartItem->product->name . ($cartItem->product->commercial_name ? ' - ' . $cartItem->product->commercial_name : ''), 40) }}</a></h4>
                                                    <h4><span>{{ $cartItem->quantity }} × </span>Rp {{ number_format($cartItem->product->price, 0, ',', '.') }}</h4>
                                                </div>
                                                <div class="shopping-cart-delete">
                                                    <form action="{{ route('cart.destroy', $cartItem) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" style="background:none; border:none; padding:0; cursor:pointer;" onclick="return confirm('Hapus item ini?')"><i class="fi-rs-cross-small"></i></button>
                                                    </form>
                                                </div>
                                            </li>
                                            @empty
                                            <li>
                                                <div class="shopping-cart-title">
                                                    <h4>Keranjang masih kosong</h4>
                                                </div>
                                            </li>
                                            @endforelse
                                        </ul>
                                        <div class="shopping-cart-footer">
                                            <div class="shopping-cart-total">
                                                <h4>Total <span>Rp {{ number_format($miniCartTotal, 0, ',', '.') }}</span></h4>
                                            </div>
                                            <div class="shopping-cart-button">
                                                <a href="{{ route('cart.index') }}" class="outline">Lihat Keranjang</a>
                                                <a href="{{ route('checkout.index') }}">Checkout</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="header-action-icon-2">
                                    <a href="page-account.html">
                                        <img class="svgInject" alt="Nest" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-user.svg') }}" />
                                    </a>
                                    <a href="page-account.html"><span class="lable ml-0">Account</span></a>
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
                        <a href="/"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/logo.svg') }}" alt="logo" /></a>
                    </div>
                    <div class="header-nav d-none d-lg-flex">

                        <div class="main-categori-wrap d-none d-lg-block">
                            <a class="categories-button-active" href="#" style="white-space: nowrap;">
                                <span class="fi-rs-apps"></span> <span class="et">Browse</span> All Categories
                                <i class="fi-rs-angle-down"></i>
                            </a>
                            <div class="categories-dropdown-wrap categories-dropdown-active-large font-heading">
                                <div class="d-flex categori-dropdown-inner">
                                    <ul>
                                        @foreach(\App\Models\Category::take(5)->get() as $category)
                                            <li>
                                                <a href="{{ route('hubs.index', ['category' => $category->slug]) }}">
                                                    <img src="{{ $category->image_url ?? asset('themes/nest-frontend/assets/imgs/theme/icons/category-1.svg') }}" alt="" />
                                                    {{ $category->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <ul class="end">
                                        @foreach(\App\Models\Category::skip(5)->take(5)->get() as $category)
                                            <li>
                                                <a href="{{ route('hubs.index', ['category' => $category->slug]) }}">
                                                    <img src="{{ $category->image_url ?? asset('themes/nest-frontend/assets/imgs/theme/icons/category-6.svg') }}" alt="" />
                                                    {{ $category->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="more_categories"><span class="icon"></span> <span class="heading-sm-1">Lihat Semua...</span></div>
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
                                    <li>
                                        <a class="{{ request()->routeIs('hubs.*') ? 'active' : '' }}" href="{{ route('hubs.index') }}">Distributor</a>
                                    </li>
                                    <li>
                                        <a class="{{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">Tentang Kami</a>
                                    </li>
                                    <li>
                                        <a class="{{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Hubungi Kami</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <div class="hotline d-none d-lg-flex">
                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-headphone.svg') }}" alt="hotline" />
                        <p style="white-space: nowrap;">+62 812-3456-7890<span style="display: block;">24/7 Support Center</span></p>
                    </div>
                    <div class="header-action-icon-2 d-block d-lg-none">
                        <div class="burger-icon burger-icon-white">
                            <span class="burger-icon-top"></span>
                            <span class="burger-icon-mid"></span>
                            <span class="burger-icon-bottom"></span>
                        </div>
                    </div>
                    <div class="header-action-right d-block d-lg-none">
                        <div class="header-action-2">
                            <div class="header-action-icon-2">
                                <a href="shop-wishlist.html">
                                    <img alt="Nest" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-heart.svg') }}" />
                                    <span class="pro-count white">4</span>
                                </a>
                            </div>
                            <div class="header-action-icon-2">
                                <a class="mini-cart-icon" href="{{ route('cart.index') }}">
                                    <img alt="Nest" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-cart.svg') }}" />
                                    <span class="pro-count white">2</span>
                                </a>
                                <div class="cart-dropdown-wrap cart-dropdown-hm2">
                                    <ul>
                                        @php
                                            $miniCarts = auth()->check() 
                                                ? \App\Models\Cart::with('product')->where('user_id', auth()->id())->latest()->get()
                                                : \App\Models\Cart::with('product')->where('session_id', session()->getId())->latest()->get();
                                            $miniCartTotal = $miniCarts->sum(function($item) { return $item->product->price * $item->quantity; });
                                        @endphp
                                        
                                        @forelse($miniCarts as $cartItem)
                                        <li>
                                            <div class="shopping-cart-img">
                                                <a href="{{ route('products.show', $cartItem->product) }}">
                                                    <img alt="{{ $cartItem->product->name }}" src="{{ $cartItem->product->image_url ? $cartItem->product->image_url : asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" onerror="this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}'">
                                                </a>
                                            </div>
                                            <div class="shopping-cart-title">
                                                <h4><a href="{{ route('products.show', $cartItem->product) }}">{{ Str::limit($cartItem->product->name . ($cartItem->product->commercial_name ? ' - ' . $cartItem->product->commercial_name : ''), 40) }}</a></h4>
                                                <h4><span>{{ $cartItem->quantity }} × </span>Rp {{ number_format($cartItem->product->price, 0, ',', '.') }}</h4>
                                            </div>
                                            <div class="shopping-cart-delete">
                                                <form action="{{ route('cart.destroy', $cartItem) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="background:none; border:none; padding:0; cursor:pointer;" onclick="return confirm('Hapus item ini?')"><i class="fi-rs-cross-small"></i></button>
                                                </form>
                                            </div>
                                        </li>
                                        @empty
                                        <li>
                                            <div class="shopping-cart-title">
                                                <h4>Keranjang masih kosong</h4>
                                            </div>
                                        </li>
                                        @endforelse
                                    </ul>
                                    <div class="shopping-cart-footer">
                                        <div class="shopping-cart-total">
                                            <h4>Total <span>Rp {{ number_format($miniCartTotal, 0, ',', '.') }}</span></h4>
                                        </div>
                                        <div class="shopping-cart-button">
                                            <a href="{{ route('cart.index') }}" class="outline">Lihat Keranjang</a>
                                            <a href="{{ route('checkout.index') }}">Checkout</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
