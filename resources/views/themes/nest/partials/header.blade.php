    <header class="header-area header-style-1 header-height-2">
        {{-- <div class="mobile-promotion">
            <span>Pembukaan besar-besaran, diskon hingga <strong>15%</strong> untuk semua item. Tinggal <strong>3 hari</strong> lagi</span>
        </div> --}}
        <div class="header-middle header-middle-ptb-1 d-none d-lg-block">
            <div class="container">
                <div class="header-wrap">
                    <div class="logo logo-width-1">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/logorasa.png') }}" alt="logo" style="max-width: 180px;" />
                        </a>
                    </div>
                    <div class="header-right">
                        <div class="search-style-2 position-relative">
                            <form class="rg-search-no-category" action="{{ route('products.index') }}" method="GET">
                                <input type="search" id="search-input-desktop" name="search" value="{{ request('search') }}" placeholder="Cari produk..." autocomplete="off" enterkeyhint="search" />
                                <button type="submit" aria-label="Cari produk"><i class="fi-rs-search"></i></button>
                            </form>
                            <div id="search-suggestions" class="search-suggestions-wrap d-none"></div>
                        </div>
                        <div class="header-action-right">
                            <div class="header-action-2">
                                {{-- <div class="search-location">
                                    <a href="javascript:void(0)" class="location-btn" data-bs-toggle="modal" data-bs-target="#modalHubSelection">
                                        <i class="fi-rs-marker mr-5"></i>
                                        <span class="lable">{{ session('selected_hub_name', 'Pilih Lokasi') }}</span>
                                    </a>
                                </div> --}}
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

                                </div>
                                <div class="header-action-icon-2">
                                    <a href="{{ auth()->check() ? route('buyer.dashboard') : route('login') }}">
                                        <img class="svgInject" alt="Nest" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-user.svg') }}" />
                                    </a>
                                    <a href="{{ auth()->check() ? route('buyer.dashboard') : route('login') }}"><span class="lable ml-0">Akun Saya</span></a>

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
                                <span class="fi-rs-apps"></span> <span class="et">Jelajahi</span> Semua Kategori
                                <i class="fi-rs-angle-down"></i>
                            </a>
                            <div class="categories-dropdown-wrap categories-dropdown-active-large font-heading">
                                <div class="d-flex categori-dropdown-inner">
                                    @php
                                        $allCategories = \App\Models\Category::forStorefrontSidebar();
                                        $count = $allCategories->count();
                                        $half = ceil($count / 2);
                                        $col1 = $allCategories->take($half);
                                        $col2 = $allCategories->skip($half);
                                    @endphp
                                    <ul>
                                        @foreach($col1 as $category)
                                            <li>
                                                <a class="{{ request('category') === $category->slug ? 'rg-category-active' : '' }}" href="{{ route('products.index', ['category' => $category->slug]) }}">
                                                    <img src="{{ $category->image_url ?? asset('themes/nest-frontend/assets/imgs/theme/icons/category-1.svg') }}" alt="" />
                                                    {{ $category->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <ul class="end">
                                        @foreach($col2 as $category)
                                            <li>
                                                <a class="{{ request('category') === $category->slug ? 'rg-category-active' : '' }}" href="{{ route('products.index', ['category' => $category->slug]) }}">
                                                    <img src="{{ $category->image_url ?? asset('themes/nest-frontend/assets/imgs/theme/icons/category-6.svg') }}" alt="" />
                                                    {{ $category->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="more_categories"><span class="icon"></span> <span class="heading-sm-1">Lihat Semua Kategori...</span></div>
                            </div>
                        </div>
                        @php
                            $distributorMainMenuUrl = ! auth()->check()
                                ? route('login')
                                : (auth()->user()->isDistributor()
                                    ? route('buyer.dashboard')
                                    : route('buyer.distributor.apply'));
                            $distributorMainMenuActive = request()->routeIs('buyer.dashboard') || request()->routeIs('buyer.distributor.apply');
                        @endphp
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
                                        <a class="{{ request()->routeIs('menus.*') ? 'active' : '' }}" href="{{ route('menus.index') }}">Menu Paket</a>
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

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="rg-mobile-search-bar d-block d-lg-none">
            <div class="container">
                <div class="search-style-2 position-relative">
                    <form class="rg-search-no-category" action="{{ route('products.index') }}" method="GET">
                        <input type="search" id="search-input-mobile" name="search" value="{{ request('search') }}" placeholder="Cari produk..." autocomplete="off" enterkeyhint="search" />
                        <button type="submit" aria-label="Cari produk"><i class="fi-rs-search"></i></button>
                    </form>
                    <div id="search-suggestions-mobile" class="search-suggestions-wrap d-none"></div>
                </div>
            </div>
        </div>
    <style>
        @media (max-width: 991px) {
            .header-bottom.sticky-bar {
                margin-top: 0 !important;
            }
            .logo.d-lg-none img {
                margin-top: 0 !important;
            }
        }
        .rg-mobile-search-bar {
            padding: 10px 0 14px;
            background-color: var(--bg-cream, #F2EAE1);
        }
        .rg-mobile-search-bar .search-style-2 form {
            width: 100%;
        }
        .rg-mobile-search-bar .search-style-2 form input {
            width: 100%;
        }
        .search-suggestions-wrap {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #e2e2e2;
            border-top: none;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            z-index: 9999;
            max-height: 400px;
            overflow-y: auto;
        }
        .suggestion-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.2s;
            text-decoration: none !important;
        }
        .suggestion-item:last-child {
            border-bottom: none;
        }
        .suggestion-item:hover {
            background: #f9f9f9;
        }
        .suggestion-img {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
            border: 1px solid #eee;
        }
        .suggestion-info {
            flex: 1;
        }
        .suggestion-name {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #253D4E;
            margin-bottom: 2px;
        }
        .suggestion-price {
            display: block;
            font-size: 13px;
            color: #3BB77E;
            font-weight: 700;
        }
        .suggestion-loading {
            padding: 15px;
            text-align: center;
            color: #777;
            font-size: 13px;
        }
        .suggestion-empty {
            padding: 15px;
            text-align: center;
            color: #777;
            font-size: 13px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchPairs = [
                [document.getElementById('search-input-desktop'), document.getElementById('search-suggestions')],
                [document.getElementById('search-input-mobile'), document.getElementById('search-suggestions-mobile')],
            ];

            searchPairs.forEach(function(pair) {
                initSearchSuggestions(pair[0], pair[1]);
            });

            function initSearchSuggestions(searchInput, suggestionsWrap) {
                if (!searchInput || !suggestionsWrap) {
                    return;
                }

                let debounceTimer;

                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();

                    clearTimeout(debounceTimer);
                    if (query.length < 2) {
                        suggestionsWrap.classList.add('d-none');
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetchSuggestions(query, suggestionsWrap);
                    }, 300);
                });

                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !suggestionsWrap.contains(e.target)) {
                        suggestionsWrap.classList.add('d-none');
                    }
                });

                searchInput.addEventListener('focus', function() {
                    if (this.value.trim().length >= 2 && suggestionsWrap.innerHTML !== '') {
                        suggestionsWrap.classList.remove('d-none');
                    }
                });
            }

            function fetchSuggestions(query, suggestionsWrap) {
                suggestionsWrap.innerHTML = '<div class="suggestion-loading"><i class="fi-rs-refresh mr-5"></i> Mencari...</div>';
                suggestionsWrap.classList.remove('d-none');

                fetch(`{{ route('products.search-suggestions') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            suggestionsWrap.innerHTML = '<div class="suggestion-empty">Produk tidak ditemukan.</div>';
                            return;
                        }

                        let html = '';
                        data.forEach(product => {
                            html += `
                                <a href="${product.url}" class="suggestion-item">
                                    <img src="${product.image}" class="suggestion-img" onerror="this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}'">
                                    <div class="suggestion-info">
                                        <span class="suggestion-name">${product.name}</span>
                                        <span class="suggestion-price">Rp ${product.price}</span>
                                    </div>
                                </a>
                            `;
                        });
                        suggestionsWrap.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error fetching suggestions:', error);
                        suggestionsWrap.classList.add('d-none');
                    });
            }
        });
    </script>
</header>
