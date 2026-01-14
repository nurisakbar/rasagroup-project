<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Toko Online') | Rasa Group</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #e74c3c;
            --primary-dark: #c0392b;
            --secondary-color: #f39c12;
            --accent-color: #3498db;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --gradient-primary: linear-gradient(135deg, #e74c3c 0%, #f39c12 100%);
        }
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        /* Top Bar */
        .top-bar {
            background: var(--dark-color);
            color: white;
            padding: 8px 0;
            font-size: 0.85rem;
        }
        
        .top-bar a {
            color: white;
            text-decoration: none;
        }
        
        .top-bar a:hover {
            color: var(--secondary-color);
        }
        
        /* Header */
        .main-header {
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary-color) !important;
        }
        
        .navbar-brand span {
            color: var(--secondary-color);
        }
        
        .nav-link {
            color: var(--dark-color) !important;
            font-weight: 500;
            padding: 1rem 1.2rem !important;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .nav-link.active {
            color: var(--primary-color) !important;
        }
        
        .btn-cart {
            background: var(--gradient-primary);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 10px 25px !important;
            font-weight: 500;
        }
        
        .btn-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(231, 76, 60, 0.4);
        }
        
        .cart-badge {
            position: absolute;
            top: 0;
            right: -5px;
            background: var(--secondary-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Hero Slider */
        .hero-slider {
            position: relative;
            overflow: hidden;
        }
        
        .hero-slider .carousel-item {
            height: 500px;
            background-size: cover;
            background-position: center;
        }
        
        .hero-slider .carousel-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.9) 0%, rgba(243, 156, 18, 0.7) 100%);
        }
        
        .hero-content {
            position: relative;
            z-index: 10;
            color: white;
            text-align: center;
            padding: 100px 0;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.3);
        }
        
        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.95;
        }
        
        .btn-hero {
            background: white;
            color: var(--primary-color);
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-hero:hover {
            background: var(--dark-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .btn-hero-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
            margin-left: 15px;
        }
        
        .btn-hero-outline:hover {
            background: white;
            color: var(--primary-color);
        }
        
        /* Features */
        .features-section {
            padding: 60px 0;
            background: var(--light-color);
        }
        
        .feature-box {
            text-align: center;
            padding: 30px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .feature-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
        }
        
        .feature-box h4 {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .feature-box p {
            color: #7f8c8d;
            margin-bottom: 0;
        }
        
        /* Section Title */
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .section-title p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .section-title .divider {
            width: 80px;
            height: 4px;
            background: var(--gradient-primary);
            margin: 15px auto;
            border-radius: 2px;
        }
        
        /* Product Card */
        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: none;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .product-card .card-img-top {
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .card-img-top {
            transform: scale(1.1);
        }
        
        .product-card .card-img-wrapper {
            overflow: hidden;
            position: relative;
        }
        
        .product-card .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--primary-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .product-card .card-body {
            padding: 25px;
        }
        
        .product-card .card-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .product-card .price {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .product-card .stock {
            font-size: 0.85rem;
            color: #27ae60;
        }
        
        .btn-add-cart {
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(231, 76, 60, 0.4);
            color: white;
        }
        
        /* Footer */
        .main-footer {
            background: var(--dark-color);
            color: white;
            padding: 80px 0 30px;
        }
        
        .footer-brand {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .footer-brand span {
            color: var(--secondary-color);
        }
        
        .footer-desc {
            color: #bdc3c7;
            line-height: 1.8;
        }
        
        .footer-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--secondary-color);
            padding-left: 10px;
        }
        
        .footer-links i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .footer-contact li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            color: #bdc3c7;
        }
        
        .footer-contact i {
            margin-right: 15px;
            margin-top: 5px;
            color: var(--secondary-color);
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .social-icons a {
            width: 45px;
            height: 45px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .social-icons a:hover {
            background: var(--primary-color);
            transform: translateY(-5px);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 30px;
            margin-top: 50px;
            text-align: center;
            color: #bdc3c7;
        }
        
        /* Alert Messages */
        .alert-floating {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }
            
            .hero-slider .carousel-item {
                height: 400px;
            }
            
            .btn-hero {
                padding: 12px 25px;
                font-size: 1rem;
            }
            
            .btn-hero-outline {
                margin-left: 0;
                margin-top: 15px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span><i class="bi bi-telephone me-2"></i> +62 812-3456-7890</span>
                    <span class="ms-4"><i class="bi bi-envelope me-2"></i> info@rasagroup.com</span>
                </div>
                <div class="col-md-6 text-end d-none d-md-block">
                    <a href="#"><i class="bi bi-facebook me-3"></i></a>
                    <a href="#"><i class="bi bi-instagram me-3"></i></a>
                    <a href="#"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <i class="bi bi-droplet-fill me-2"></i>Rasa<span>Group</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                <i class="bi bi-house-door me-1"></i> Beranda
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('hubs.*') ? 'active' : '' }}" href="{{ route('hubs.index') }}">
                                <i class="bi bi-grid me-1"></i> Produk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">
                                <i class="bi bi-info-circle me-1"></i> Tentang Kami
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">
                                <i class="bi bi-chat-dots me-1"></i> Kontak
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        @auth
                            <li class="nav-item position-relative me-3">
                                <a class="btn btn-cart" href="{{ route('cart.index') }}">
                                    <i class="bi bi-cart3 me-1"></i> Keranjang
                                    @php
                                        $cartCount = auth()->check() 
                                            ? \App\Models\Cart::where('user_id', auth()->id())->sum('quantity')
                                            : \App\Models\Cart::where('session_id', session()->getId())->sum('quantity');
                                    @endphp
                                    @if($cartCount > 0)
                                        <span class="cart-badge">{{ $cartCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('buyer.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('buyer.orders.index') }}"><i class="bi bi-bag me-2"></i> Pesanan Saya</a></li>
                                    <li><a class="dropdown-item" href="{{ route('buyer.profile') }}"><i class="bi bi-person me-2"></i> Profil</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item position-relative me-2">
                                <a class="btn btn-cart" href="{{ route('cart.index') }}">
                                    <i class="bi bi-cart3 me-1"></i> Keranjang
                                    @php
                                        $cartCount = \App\Models\Cart::where('session_id', session()->getId())->sum('quantity');
                                    @endphp
                                    @if($cartCount > 0)
                                        <span class="cart-badge">{{ $cartCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}"><i class="bi bi-person-plus me-1"></i> Daftar</a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert-floating">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-floating">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-floating">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="main-footer" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <div class="footer-brand">
                        <i class="bi bi-droplet-fill me-2"></i>Rasa<span>Group</span>
                    </div>
                    <p class="footer-desc">
                        Rasa Group menyediakan berbagai varian sirup dengan kualitas terbaik untuk kebutuhan minuman Anda. 
                        Terbuat dari bahan-bahan pilihan dengan rasa yang nikmat dan menyegarkan.
                    </p>
                    <div class="social-icons">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-whatsapp"></i></a>
                        <a href="#"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-5 mb-lg-0">
                    <h5 class="footer-title">Menu</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('home') }}">Beranda</a></li>
                        {{-- <li><a href="{{ route('products.index') }}">Produk</a></li> --}}
                        <li><a href="{{ route('hubs.index') }}">Produk</a></li>
                        <li><a href="{{ route('about') }}">Tentang Kami</a></li>
                        <li><a href="{{ route('contact') }}">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-5 mb-lg-0">
                    <h5 class="footer-title">Akun</h5>
                    <ul class="footer-links">
                        @auth
                            <li><a href="{{ route('buyer.dashboard') }}">Dashboard</a></li>
                            <li><a href="{{ route('buyer.orders.index') }}">Pesanan Saya</a></li>
                            <li><a href="{{ route('buyer.profile') }}">Profil</a></li>
                            <li><a href="{{ route('cart.index') }}">Keranjang</a></li>
                        @else
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('register') }}">Daftar</a></li>
                            <li><a href="{{ route('cart.index') }}">Keranjang</a></li>
                        @endauth
                    </ul>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h5 class="footer-title">Hubungi Kami</h5>
                    <ul class="footer-links footer-contact">
                        <li>
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Jl. Rasa Manis No. 123<br>Jakarta Selatan, Indonesia 12345</span>
                        </li>
                        <li>
                            <i class="bi bi-telephone-fill"></i>
                            <span>+62 812-3456-7890</span>
                        </li>
                        <li>
                            <i class="bi bi-envelope-fill"></i>
                            <span>info@rasagroup.com</span>
                        </li>
                        <li>
                            <i class="bi bi-clock-fill"></i>
                            <span>Senin - Sabtu: 08.00 - 17.00 WIB</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Rasa Group. All rights reserved. Made with <i class="bi bi-heart-fill text-danger"></i> in Indonesia</p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <div class="whatsapp-float">
        <a href="https://wa.me/6281234567890?text=Halo%20Rasa%20Group%2C%20saya%20ingin%20bertanya%20tentang%20produk%20Anda" target="_blank" class="whatsapp-btn" title="Chat via WhatsApp">
            <i class="bi bi-whatsapp"></i>
            <span class="whatsapp-text">Chat Kami</span>
        </a>
        <div class="whatsapp-tooltip">
            <div class="tooltip-content">
                <div class="tooltip-header">
                    <i class="bi bi-whatsapp me-2"></i> WhatsApp
                </div>
                <div class="tooltip-body">
                    Ada pertanyaan? Chat langsung dengan tim kami!
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Floating WhatsApp Button */
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
            font-size: 1rem;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
            transition: all 0.3s ease;
            animation: pulse-wa 2s infinite;
        }
        
        .whatsapp-btn:hover {
            color: white;
            transform: scale(1.05);
            box-shadow: 0 12px 35px rgba(37, 211, 102, 0.5);
            animation: none;
        }
        
        .whatsapp-btn i {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        
        .whatsapp-text {
            display: inline;
        }
        
        @keyframes pulse-wa {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
            }
        }
        
        /* Tooltip */
        .whatsapp-tooltip {
            position: absolute;
            bottom: 70px;
            right: 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        
        .whatsapp-float:hover .whatsapp-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .tooltip-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            padding: 0;
            min-width: 250px;
            overflow: hidden;
        }
        
        .tooltip-header {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            padding: 12px 20px;
            font-weight: 600;
        }
        
        .tooltip-body {
            padding: 15px 20px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .tooltip-content::after {
            content: '';
            position: absolute;
            bottom: -8px;
            right: 30px;
            width: 16px;
            height: 16px;
            background: white;
            transform: rotate(45deg);
            box-shadow: 3px 3px 5px rgba(0,0,0,0.05);
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .whatsapp-float {
                bottom: 20px;
                right: 20px;
            }
            
            .whatsapp-btn {
                padding: 15px;
                border-radius: 50%;
            }
            
            .whatsapp-btn i {
                margin-right: 0;
            }
            
            .whatsapp-text {
                display: none;
            }
            
            .whatsapp-tooltip {
                display: none;
            }
        }
    </style>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        // Auto hide alerts
        setTimeout(function() {
            $('.alert-floating .alert').alert('close');
        }, 5000);
    </script>
    @stack('scripts')
</body>
</html>
