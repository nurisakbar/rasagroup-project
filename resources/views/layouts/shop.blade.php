<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('themes.nest.partials.head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        .header-style-1 .search-style-2 form input {
            color: #333 !important;
        }

        .header-style-1 .search-style-2 form select {
            color: #333 !important;
        }

        .search-style-2 form select {
            background: transparent !important;
        }

        .header-action-icon-2 > a span.pro-count {
            background-color: var(--primary-rasa) !important;
        }

        /* Product Price Style Overrides for Nest Theme */
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

        .product-name, .product-name a, .product-cart-wrap h2, .product-cart-wrap h2 a, .product-content-wrap h2, .product-content-wrap h2 a, h6, h6 a {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 600 !important;
            color: #253D4E !important;
        }

        /* Section Headings Overrides */
        .section-title h3, .section-title h4, .section-title h2, h4.section-title, .icon-box-title, .newsletter-content h2, .widget-title {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 500 !important;
            color: #253D4E !important;
        }

        .banner-img .banner-text h4, .banner-img.style-2 .banner-text h2 {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 500 !important;
            color: #253D4E !important;
            line-height: 1.2 !important;
        }

        /* Countdown Timer Color Style */
        .deals-countdown, .deals-countdown span, .deals-countdown .countdown-section, .deals-countdown .countdown-amount, .deals-countdown .countdown-period {
            color: rgba(111, 23, 21, 1) !important;
        }

        .product-category, .product-category a {
            font-family: 'Lato', sans-serif !important;
            font-weight: 400 !important;
            font-size: 0.85rem !important;
        }

        .main-menu nav ul li a {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 500 !important;
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
    <!-- Bootstrap Icons for WhatsApp Button -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    @include('themes.nest.partials.header')
    @include('themes.nest.partials.mobile-header')

    @if(!View::hasSection('hide_layout_alerts'))
    @if(session('success'))
        <div class="container mt-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fi-rs-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
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
    @endif

    @yield('content')

    @include('themes.nest.partials.modals')
    @include('themes.nest.partials.footer')

    <!-- WhatsApp Floating Button -->
    <div class="whatsapp-float">
        <a href="https://wa.me/6282355138133" target="_blank" class="whatsapp-btn">
            <i class="bi bi-whatsapp me-2"></i> Chat Kami
        </a>
    </div>

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
                        console.warn("Geolocation access denied or failed.");
                    }, {
                        timeout: 10000
                    });
                }
            @endif

            // AJAX Add to Cart
            $('.add-to-cart-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const data = form.serialize();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            
                            // Update cart count in header if element exists
                            $('.pro-count.blue').text(response.cart_count);
                            $('.pro-count.white').text(response.cart_count);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            if (confirm("Silakan masuk terlebih dahulu untuk belanja. Masuk sekarang?")) {
                                window.location.href = '{{ route("login") }}';
                            }
                            return;
                        }

                        let errorMsg = "Terjadi kesalahan saat menambahkan ke keranjang.";
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            errorMsg = Object.values(errors).flat()[0];
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        
                        alert(errorMsg);
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
