<!DOCTYPE html>
<html class="no-js" lang="id">

<head>
    @include('themes.nest.partials.head')
    <style>
        :root {
            --primary-rasa: #6A1B1B;
            --bg-cream: #F2EAE1;
        }

        body, .main, .header-area, .header-bottom, .header-middle, .header-top, .sticky-bar {
            background-color: var(--bg-cream) !important;
        }

        /* Update header elements */
        .categories-button-active {
            background-color: var(--primary-rasa) !important;
            border-radius: 5px !important;
            color: #fff !important;
        }
        
        .categories-button-active span, .categories-button-active i {
            color: #fff !important;
        }

        .header-style-1 .search-style-2 form {
            border: 2px solid var(--primary-rasa) !important;
            border-radius: 5px !important;
            background-color: #fff !important;
        }

        .text-brand {
            color: var(--primary-rasa) !important;
        }

        .btn-brush-secondary {
            background-color: var(--primary-rasa) !important;
        }

        .newsletter .form-subcriber button, .form-subcriber button {
            background-color: var(--primary-rasa) !important;
            border: 1px solid var(--primary-rasa) !important;
        }

        .header-action-icon-2 > a span.pro-count {
            background-color: var(--primary-rasa) !important;
        }

        .product-price span {
            color: #801D1D !important;
            font-size: 1.3rem !important;
            font-weight: 700 !important;
        }

        .product-price span.old-price {
            color: #ADADAD !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            text-decoration: line-through !important;
            margin-left: 8px !important;
        }

        /* Unified Add Button Style */
        .btn-add-cart, .add-cart .add, .add-cart button.add {
            background: #F5E6E6 !important;
            color: #801D1D !important;
            padding: 8px 18px !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: 700 !important;
            font-size: 0.9rem !important;
            transition: all 0.3s !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 5px !important;
            width: auto !important;
            height: auto !important;
        }

        .btn-add-cart:hover, .add-cart .add:hover {
            background: #801D1D !important;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }

        /* Slider Typography Overrides */
        .slider-content .display-2 {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 500 !important;
        }

        .slider-content p.mb-65 {
            font-family: 'Lato', sans-serif !important;
        }

        /* Product Detail Controls Overrides */
        .detail-qty {
            max-width: 120px !important;
            border: 1.5px solid #6A1B1B !important;
            border-radius: 8px !important;
            padding: 5px 10px !important;
            position: relative !important;
        }

        .qty-val {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 600 !important;
            color: #6A1B1B !important;
            border: none !important;
            text-align: center !important;
            width: 100% !important;
            background: transparent !important;
        }

        .qty-up, .qty-down {
            color: #6A1B1B !important;
            font-size: 14px !important;
        }

        .button-add-to-cart, .btn-buy, button.add, .product-extra-link2 button {
            background-color: #6A1B1B !important;
            border: none !important;
            border-radius: 8px !important;
            color: #ffffff !important;
            padding: 12px 30px !important;
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 600 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.3s ease !important;
        }

        .button-add-to-cart:hover, .btn-buy:hover, button.add:hover, .product-extra-link2 button:hover {
            background-color: #4D1313 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
        }

        .button-add-to-cart i, .btn-buy i, button.add i {
            margin-right: 8px !important;
            font-size: 18px !important;
        }

        /* Countdown Timer Color Style */
        .deals-countdown, .deals-countdown span, .deals-countdown .countdown-section, .deals-countdown .countdown-amount, .deals-countdown .countdown-period {
            color: rgba(111, 23, 21, 1) !important;
        }

        /* Checkout Page Styling Overrides */
        .checkout-container, .order_table.checkout, .payment_method {
            background-color: transparent !important;
        }

        .order_table.checkout table tbody tr td {
            border: none !important;
            padding: 15px 0 !important;
        }

        .order_table.checkout h6 a {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 600 !important;
            color: #253D4E !important;
        }

        .order_table.checkout h5.text-brand {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 700 !important;
            color: #6A1B1B !important;
        }

        .checkout-card, .cart-totals.checkout {
            background: #ffffff !important;
            border-radius: 20px !important;
            padding: 40px !important;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05) !important;
            border: none !important;
        }

        /* Standardize headings for checkout */
        #checkoutForm h4 {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 700 !important;
            color: #253D4E !important;
        }

        /* Cart Page Styling Overrides */
        .shopping-summery table thead th {
            background: #f7f8f9 !important;
            border: none !important;
            padding: 18px 20px !important;
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 700 !important;
            color: #253D4E !important;
        }

        .shopping-summery table thead tr {
            border-radius: 15px !important;
            overflow: hidden !important;
        }

        .shopping-summery table thead th.start {
            border-radius: 15px 0 0 15px !important;
        }

        .shopping-summery table thead th.end {
            border-radius: 0 15px 15px 0 !important;
        }

        .product-thumbnail img {
            max-width: 100px !important;
            border-radius: 12px !important;
            border: 1px solid #ECECEC !important;
        }

        .shopping-summery table tbody tr td {
            border-bottom: 1px solid #f7f8f9 !important;
            vertical-align: middle !important;
        }

        .shopping-summery .product-name a {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 600 !important;
            font-size: 16px !important;
        }

        .shopping-summery .price h4 {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 700 !important;
            font-size: 18px !important;
            color: #6A1B1B !important;
        }

        .shopping-summery .price h4.text-body {
            color: #7E7E7E !important;
            font-weight: 500 !important;
        }

        .cart-totals {
            background: #ffffff !important;
            border-radius: 20px !important;
            padding: 35px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05) !important;
            border: none !important;
        }

        .cart-totals h4.text-brand {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 700 !important;
            color: #6A1B1B !important;
        }

        .cart-action .btn {
            background-color: transparent !important;
            border: 1.5px solid #6A1B1B !important;
            color: #6A1B1B !important;
            border-radius: 10px !important;
            padding: 12px 25px !important;
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }

        .cart-action .btn:hover {
            background-color: #6A1B1B !important;
            color: #ffffff !important;
        }

        .cart-action .btn i {
            margin-right: 8px !important;
        }

        .cart-totals .btn {
            background-color: #6A1B1B !important;
            border: none !important;
            border-radius: 10px !important;
            padding: 15px !important;
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 700 !important;
            font-size: 16px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }

        /* Sidebar Widget Styling Overrides */
        .sidebar-widget {
            background: #ffffff !important;
            border-radius: 15px !important;
            padding: 30px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05) !important;
            border: none !important;
            margin-bottom: 30px !important;
        }

        .sidebar-widget .section-title.style-1 {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 700 !important;
            font-size: 20px !important;
            color: #253D4E !important;
            border-bottom: 1px solid #ECECEC !important;
            padding-bottom: 15px !important;
            margin-bottom: 25px !important;
            position: relative !important;
        }

        .sidebar-widget .section-title.style-1::after {
            content: "" !important;
            width: 80px !important;
            height: 2px !important;
            background-color: #6A1B1B !important;
            position: absolute !important;
            bottom: -1px !important;
            left: 0 !important;
        }

        .single-post {
            border: none !important;
            margin-bottom: 20px !important;
            padding: 0 !important;
        }

        .single-post .image {
            width: 80px !important;
            height: 80px !important;
            border-radius: 10px !important;
            overflow: hidden !important;
            background: #f8f8f8 !important;
        }

        .single-post .content h5 {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 600 !important;
            font-size: 15px !important;
            line-height: 1.4 !important;
            margin-bottom: 5px !important;
        }

        .single-post .content .price {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 700 !important;
            font-size: 16px !important;
            color: #6A1B1B !important;
        }

        /* Product Tabs Styling Overrides */
        .product-info {
            background: #ffffff !important;
            border-radius: 20px !important;
            padding: 40px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05) !important;
            margin-top: 50px !important;
        }

        .product-info .nav-tabs {
            border: none !important;
            margin-bottom: 30px !important;
            display: flex !important;
            gap: 15px !important;
        }

        .product-info .nav-tabs .nav-item .nav-link {
            border: 1px solid #ECECEC !important;
            border-radius: 50px !important;
            padding: 10px 25px !important;
            color: #7E7E7E !important;
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 500 !important;
            background: #ffffff !important;
            transition: all 0.3s ease !important;
        }

        .product-info .nav-tabs .nav-item .nav-link.active {
            color: #6A1B1B !important;
            border-color: #ECECEC !important;
            box-shadow: 0 5px 15px rgba(106, 27, 27, 0.1) !important;
        }

        .tab-pane h4 {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 700 !important;
            color: #253D4E !important;
            margin-top: 25px !important;
            margin-bottom: 15px !important;
        }

        .tab-pane p {
            font-family: 'Lato', sans-serif !important;
            color: #7E7E7E !important;
            line-height: 1.8 !important;
        }

        /* Breadcrumb Styling */
        .page-header.breadcrumb-wrap {
            background-color: #F2EAE1 !important;
            padding: 20px 0 !important;
            border: none !important;
        }

        .breadcrumb {
            font-family: 'Lato', sans-serif !important;
            font-size: 14px !important;
            color: #6A1B1B !important;
            font-weight: 500 !important;
            display: flex !important;
            align-items: center !important;
            background: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .breadcrumb a {
            color: #7E7E7E !important;
            text-decoration: none !important;
        }

        .breadcrumb a:hover {
            color: #6A1B1B !important;
        }

        .breadcrumb i {
            display: none !important;
        }

        .breadcrumb span {
            margin: 0 10px !important;
            color: #7E7E7E !important;
            font-size: 12px !important;
        }

        .main-menu nav ul li a {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 500 !important;
        }

        /* Banner Styling Overrides */
        .banner-img .banner-text h4, .banner-img.style-2 .banner-text h2 {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 500 !important;
            color: #253D4E !important;
            line-height: 1.2 !important;
        }

        .banner-img .banner-text .btn, .banner-img .banner-text a.btn {
            background-color: var(--primary-rasa) !important;
            color: #fff !important;
            border-radius: 8px !important;
            padding: 10px 25px !important;
            font-weight: 700 !important;
            border: none !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
            font-size: 0.9rem !important;
            height: auto !important;
        }

        .banner-img .banner-text .btn i {
            font-size: 1.1rem !important;
            margin: 0 !important;
        }

        /* Section Headings Overrides */
        .section-title h3, .section-title h4, .section-title h2, h4.section-title, .icon-box-title, .newsletter-content h2, .widget-title {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 500 !important;
        }

        .nav-tabs .nav-link {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 500 !important;
        }

        .product-name, .product-name a, .product-cart-wrap h2, .product-cart-wrap h2 a, .product-content-wrap h2, .product-content-wrap h2 a, h6, h6 a {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 600 !important;
            color: #253D4E !important;
            font-size: 17px !important;
        }

        .product-price span {
            font-weight: 700 !important;
            color: #6A1B1B !important;
            font-size: 20px !important;
        }

        .product-category, .product-category a {
            font-family: 'Lato', sans-serif !important;
            font-weight: 400 !important;
            font-size: 0.85rem !important;
        }

        /* Anti-Green Global Overrides */
        :root {
            --brand-color: #6A1B1B !important;
            --primary-color: #6A1B1B !important;
            --brand-color-hover: #801D1D !important;
        }

        .text-brand {
            color: #6A1B1B !important;
        }

        .btn, .button {
            background-color: #6A1B1B !important;
            border-color: #6A1B1B !important;
        }

        .btn:hover, .button:hover {
            background-color: #801D1D !important;
            border-color: #801D1D !important;
        }

        .product-cart-wrap .product-action-1 a.action-btn:hover {
            background-color: #6A1B1B !important;
            color: #fff !important;
        }

        .product-cart-wrap .product-price span {
            color: #6A1B1B !important;
        }

        .add-cart .add, .add-to-cart-form .add {
            background-color: #6A1B1B !important;
            color: #fff !important;
        }

        .add-cart .add:hover, .add-to-cart-form .add:hover {
            background-color: #801D1D !important;
        }

        .pagination .page-item.active .page-link {
            background-color: #6A1B1B !important;
            border-color: #6A1B1B !important;
        }

        .alert-warning {
            border-left: 4px solid #6A1B1B !important;
        }
    </style>
</head>

<body>
    @include('themes.nest.partials.header')
    @include('themes.nest.partials.mobile-header')

    @auth
        @if(!auth()->user()->hasVerifiedEmail())
            <div class="container-fluid p-0">
                <div class="alert alert-warning alert-dismissible fade show border-0 rounded-0 mb-0 py-3 text-center" role="alert" style="background-color: #fff3cd; color: #856404; position: relative; z-index: 1000;">
                    <div class="container">
                        <i class="fi-rs-info me-2"></i>
                        Email Anda (<strong>{{ auth()->user()->email }}</strong>) belum diverifikasi. Silakan cek inbox Anda.
                        <form class="d-inline ms-2" method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn-link p-0 border-0 bg-transparent text-decoration-underline font-weight-bold" style="color: #6A1B1B; cursor: pointer;">Kirim ulang email verifikasi</button>
                        </form>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    <main class="main">
        @if(session('success') && !request()->routeIs('cart.index'))
            <div class="container mt-4">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fi-rs-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error') && !request()->routeIs('cart.index'))
            <div class="container mt-4">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fi-rs-cross-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="container mt-4">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fi-rs-exclamation me-2"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="container mt-4">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fi-rs-info me-2"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('themes.nest.partials.footer')
    @include('themes.nest.partials.modals')
    @include('themes.nest.partials.preloader')
    @include('themes.nest.partials.scripts')

    <script>
        $(document).ready(function() {
            // Automatic Hub Detection
            @if(!session()->has('selected_hub_id'))
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        $.ajax({
                            url: '{{ route("hubs.detect-nearest") }}',
                            type: 'POST',
                            data: {
                                latitude: lat,
                                longitude: lng,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success && response.is_new) {
                                    alert("Lokasi Terdeteksi! Kami telah memilih '" + response.hub.name + "' sebagai Hub terdekat Anda untuk kenyamanan belanja.");
                                    window.location.reload();
                                }
                            }
                        });
                    }, function(error) {
                        console.warn("Geolocation access denied or failed:", error);
                    }, {
                        timeout: 10000,
                        enableHighAccuracy: true
                    });
                }
            @endif
            
            // Handle Add to Cart Form
            $(document).on('submit', '.add-to-cart-form', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const url = form.attr('action');
                const submitBtn = form.find('button[type="submit"]');
                const originalBtnHtml = submitBtn.html();
                
                // Disable button and show loading
                submitBtn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message (using simple alert for now, or Toast)
                            alert(response.message);
                            
                            // Update cart counts in header
                            $('.pro-count').text(response.cart_count);
                            
                            // Optionally reload mini-cart content (could be more complex)
                        } else {
                            alert(response.error || 'Terjadi kesalahan saat menambahkan produk.');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            let message = '';
                            for (const key in errors) {
                                message += errors[key][0] + '\n';
                            }
                            alert(message);
                        } else if (xhr.status === 302) {
                            // Handle case where back() redirect happens (non-ajax fallback)
                            // This shouldn't normally happen with X-Requested-With
                            window.location.reload();
                        } else {
                            alert('Terjadi kesalahan. Silakan coba lagi.');
                        }
                    },
                    complete: function() {
                        // Reset button
                        submitBtn.attr('disabled', false).html(originalBtnHtml);
                    }
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>

</html>
