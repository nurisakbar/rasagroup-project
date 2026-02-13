<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Toko Online') | Rasa Group</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('themes/nest-frontend/assets/imgs/theme/favicon.svg') }}" />
    
    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('themes/nest-frontend/assets/css/main.css?v=6.1') }}" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #E63946;
            --primary-dark: #C1121F;
            --secondary-color: #F77F00;
            --accent-color: #FCBF49;
            --dark-color: #1D3557;
            --light-bg: #F8F9FA;
            --white: #FFFFFF;
            --text-dark: #2B2D42;
            --text-light: #6C757D;
            --border-color: #E0E0E0;
            --success-color: #06A77D;
        }
        
        body {
            font-family: 'Poppins', sans-serif !important;
        }

        .header-wrap .logo img {
            max-width: 150px;
        }

        /* Brand Color consistency for Nest Theme */
        .text-brand { color: var(--primary-color) !important; }
        .bg-brand { background-color: var(--primary-color) !important; }
        .btn { background-color: var(--primary-color); border-color: var(--primary-color); }
        .btn:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); }
        
        .main-menu > nav > ul > li > a.active,
        .main-menu > nav > ul > li:hover > a {
            color: var(--primary-color) !important;
        }

        .header-action-icon-2 > a span.pro-count {
            background-color: var(--primary-color) !important;
        }

        .footer-list li a:hover {
            color: var(--primary-color) !important;
            padding-left: 5px;
        }

        /* Floating WhatsApp Button Override */
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
        }
        
        .whatsapp-btn {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
            transition: all 0.3s ease;
        }

        .whatsapp-btn:hover {
            transform: scale(1.05);
            color: white;
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="header-area header-style-1 header-height-2">
        <div class="header-middle header-middle-ptb-1 d-none d-lg-block">
            <div class="container">
                <div class="header-wrap">
                    <div class="logo logo-width-1">
                        <a href="{{ route('home') }}">
                            <h3 class="fw-bold mb-0 text-brand">Rasa<span class="text-dark">Group</span></h3>
                        </a>
                    </div>
                    <div class="header-right">
                        <div class="search-style-2">
                            <form action="{{ route('hubs.index') }}" method="GET">
                                <select class="select-active">
                                    <option>Semua Kategori</option>
                                    @foreach(\App\Models\Category::all() as $cat)
                                        <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="search" placeholder="Cari produk..." />
                            </form>
                        </div>
                        <div class="header-action-right">
                            <div class="header-action-2">
                                <div class="header-action-icon-2">
                                    <a class="mini-cart-icon" href="{{ route('cart.index') }}">
                                        <img alt="Cart" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-cart.svg') }}" />
                                        @php
                                            $cartCount = auth()->check() 
                                                ? \App\Models\Cart::where('user_id', auth()->id())->sum('quantity')
                                                : \App\Models\Cart::where('session_id', session()->getId())->sum('quantity');
                                        @endphp
                                        <span class="pro-count blue">{{ $cartCount }}</span>
                                    </a>
                                    <a href="{{ route('cart.index') }}"><span class="lable">Keranjang</span></a>
                                </div>
                                <div class="header-action-icon-2">
                                    <a href="#">
                                        <img class="svgInject" alt="User" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-user.svg') }}" />
                                    </a>
                                    <a href="#"><span class="lable ml-0">{{ auth()->check() ? auth()->user()->name : 'Akun' }}</span></a>
                                    <div class="cart-dropdown-wrap cart-dropdown-hm2 account-dropdown">
                                        <ul>
                                            @auth
                                                <li><a href="{{ route('buyer.dashboard') }}"><i class="fi-rs-user mr-10"></i>Dashboard</a></li>
                                                <li><a href="{{ route('buyer.orders.index') }}"><i class="fi-rs-shopping-bag mr-10"></i>Pesanan Saya</a></li>
                                                <li><a href="{{ route('buyer.profile') }}"><i class="fi-rs-settings mr-10"></i>Profil</a></li>
                                                <li>
                                                    <form method="POST" action="{{ route('logout') }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item border-0 bg-transparent"><i class="fi-rs-sign-out mr-10"></i>Logout</button>
                                                    </form>
                                                </li>
                                            @else
                                                <li><a href="{{ route('login') }}"><i class="fi-rs-key mr-10"></i>Login</a></li>
                                                <li><a href="{{ route('register') }}"><i class="fi-rs-user-add mr-10"></i>Register</a></li>
                                            @endauth
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
                        <a href="{{ route('home') }}"><h3 class="fw-bold mb-0 text-brand">Rasa<span>Group</span></h3></a>
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
                                                    <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/category-1.svg') }}" alt="" />
                                                    {{ $category->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <ul class="end">
                                        @foreach(\App\Models\Category::skip(5)->take(5)->get() as $category)
                                            <li>
                                                <a href="{{ route('hubs.index', ['category' => $category->slug]) }}">
                                                    <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/category-6.svg') }}" alt="" />
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
                                    <li><a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Halaman Utama</a></li>
                                    <li><a class="{{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Katalog Produk</a></li>
                                    <li><a href="#">Promo</a></li>
                                    <li><a class="{{ request()->routeIs('hubs.*') ? 'active' : '' }}" href="{{ route('hubs.index') }}">Distributor</a></li>
                                    <li><a class="{{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">Tentang Kami</a></li>
                                    <li><a class="{{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Hubungi Kami</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <div class="hotline d-none d-lg-flex">
                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-headphone.svg') }}" alt="hotline" />
                        <p>0813-1234-5678<span>24/7 Support Center</span></p>
                    </div>
                    <div class="header-action-icon-2 d-block d-lg-none">
                        <div class="burger-icon burger-icon-white">
                            <span class="burger-icon-top"></span>
                            <span class="burger-icon-mid"></span>
                            <span class="burger-icon-bottom"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="mobile-header-active mobile-header-wrapper-style">
        <div class="mobile-header-wrapper-inner">
            <div class="mobile-header-top">
                <div class="mobile-header-logo">
                    <a href="{{ route('home') }}"><h3 class="fw-bold mb-0 text-brand">Rasa<span>Group</span></h3></a>
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
                    <nav>
                        <ul class="mobile-menu font-heading">
                            <li><a href="{{ route('home') }}">Halaman Utama</a></li>
                            <li><a href="{{ route('products.index') }}">Katalog Produk</a></li>
                            <li><a href="#">Promo</a></li>
                            <li><a href="{{ route('hubs.index') }}">Distributor</a></li>
                            <li><a href="{{ route('about') }}">Tentang Kami</a></li>
                            <li><a href="{{ route('contact') }}">Hubungi Kami</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="mobile-header-info-wrap">
                    <div class="single-mobile-header-info">
                        <a href="{{ route('login') }}"><i class="fi-rs-user"></i>Log In / Sign Up </a>
                    </div>
                </div>
                <div class="mobile-social-icon mb-50">
                    <h6 class="mb-15">Follow Us</h6>
                    <a href="#"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-facebook-white.svg') }}" alt="" /></a>
                    <a href="#"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-instagram-white.svg') }}" alt="" /></a>
                    <a href="#"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-youtube-white.svg') }}" alt="" /></a>
                </div>
                <div class="site-copyright">&copy; {{ date('Y') }}, Rasa Group. All rights reserved.</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="container mt-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @yield('content')

    @include('themes.nest.partials.footer')

    <!-- WhatsApp Floating Button -->
    <div class="whatsapp-float">
        <a href="https://wa.me/6282355138133" target="_blank" class="whatsapp-btn">
            <i class="bi bi-whatsapp me-2"></i> Chat Kami
        </a>
    </div>

    <!-- Vendor JS-->
    <script src="{{ asset('themes/nest-frontend/assets/js/vendor/modernizr-3.6.0.min.js') }}"></script>
    <script src="{{ asset('themes/nest-frontend/assets/js/vendor/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('themes/nest-frontend/assets/js/vendor/jquery-migrate-3.3.0.min.js') }}"></script>
    <script src="{{ asset('themes/nest-frontend/assets/js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('themes/nest-frontend/assets/js/plugins/slick.js') }}"></script>
    <script src="{{ asset('themes/nest-frontend/assets/js/plugins/wow.js') }}"></script>
    <script src="{{ asset('themes/nest-frontend/assets/js/plugins/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('themes/nest-frontend/assets/js/plugins/magnific-popup.js') }}"></script>
    <script src="{{ asset('themes/nest-frontend/assets/js/plugins/select2.min.js') }}"></script>
    <script src="{{ asset('themes/nest-frontend/assets/js/plugins/waypoints.js') }}"></script>
    <script src="{{ asset('themes/nest-frontend/assets/js/plugins/counterup.js') }}"></script>
    <script src="{{ asset('themes/nest-frontend/assets/js/plugins/scrollup.js') }}"></script>
    <script src="{{ asset('themes/nest-frontend/assets/js/main.js?v=6.1') }}"></script>
    @stack('scripts')
</body>
</html>
