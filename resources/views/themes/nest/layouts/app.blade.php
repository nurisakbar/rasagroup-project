<!DOCTYPE html>
<html class="no-js" lang="id">

<head>
    @include('themes.nest.partials.head')
    <style>
        :root {
            --primary-rasa: #6A1B1B;
            --bg-cream: #F2EAE1;
            /* Button theme (from Figma screenshot) */
            --btn-rasa: rgba(111, 23, 21, 1);
            --btn-rasa-hover: rgba(138, 90, 87, 1);
            --btn-rasa-shadow: 0 18px 35px rgba(24, 24, 24, 0.12);
        }

        body, .main, .header-area, .header-bottom, .header-middle, .header-top, .sticky-bar {
            background-color: var(--bg-cream) !important;
            font-family: 'Fira Sans', sans-serif !important;
        }

        h1, h2, h3, h4, h5, h6, .font-heading, .main-menu nav ul li a, .btn, button {
            font-family: 'Fira Sans', sans-serif !important;
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

        footer .hotline p {
            color: var(--primary-rasa) !important;
        }
        footer .mobile-social-icon a {
            background-color: var(--primary-rasa) !important;
        }
        footer .mobile-social-icon a:hover {
            background-color: var(--btn-rasa-hover) !important;
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

        .product-cart-wrap .product-badges span.new,
        .vendor-wrap .product-badges.product-badges-position span.new {
            background-color: var(--btn-rasa) !important;
        }

        /* ------------------------------------------------------------------ */
        /* Global button theme (apply to all primary buttons)                  */
        /* ------------------------------------------------------------------ */
        .btn:not(.btn-link):not(.btn-outline):not(.btn-outline-secondary):not(.btn-outline-default),
        a.btn:not(.btn-link):not(.btn-outline):not(.btn-outline-secondary):not(.btn-outline-default),
        button.btn:not(.btn-link):not(.btn-outline):not(.btn-outline-secondary):not(.btn-outline-default),
        input[type="submit"].btn,
        .button-add-to-cart,
        .btn-buy,
        .add-cart .add,
        .add-cart button.add,
        .add-to-cart-form .add {
            background-color: var(--btn-rasa) !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 12px !important;
            box-shadow: var(--btn-rasa-shadow) !important;
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 600 !important;
            transition: background-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease !important;
        }

        .btn:not(.btn-link):not(.btn-outline):not(.btn-outline-secondary):not(.btn-outline-default):hover,
        a.btn:not(.btn-link):not(.btn-outline):not(.btn-outline-secondary):not(.btn-outline-default):hover,
        button.btn:not(.btn-link):not(.btn-outline):not(.btn-outline-secondary):not(.btn-outline-default):hover,
        input[type="submit"].btn:hover,
        .button-add-to-cart:hover,
        .btn-buy:hover,
        .add-cart .add:hover,
        .add-cart button.add:hover,
        .add-to-cart-form .add:hover {
            background-color: var(--btn-rasa-hover) !important;
            color: #ffffff !important;
            box-shadow: var(--btn-rasa-shadow) !important;
            transform: none !important;
        }

        .btn:focus,
        button.btn:focus,
        .button-add-to-cart:focus,
        .btn-buy:focus,
        .add-cart .add:focus,
        .add-to-cart-form .add:focus {
            outline: none !important;
            box-shadow: 0 0 0 4px rgba(111, 23, 21, 0.18), var(--btn-rasa-shadow) !important;
        }

        .btn:disabled,
        button.btn:disabled,
        .btn.disabled {
            opacity: 0.7 !important;
            box-shadow: none !important;
        }

        /* Slider Typography Overrides */
        .slider-content .display-2 {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 500 !important;
        }

        .slider-content p.mb-65 {
            font-family: 'Lato', sans-serif !important;
        }

        /* Product detail: qty + tombol — border maroon, sejajar tinggi */
        .detail-extralink {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            gap: 12px !important;
        }

        .detail-extralink > div {
            display: block !important;
            vertical-align: unset !important;
        }

        .detail-extralink .detail-qty {
            max-width: 128px !important;
            min-height: 48px !important;
            box-sizing: border-box !important;
            display: inline-flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 4px !important;
            margin: 0 6px 0 0 !important;
            padding: 0 10px !important;
            background: #fff !important;
            border: 2px solid var(--primary-rasa) !important;
            border-radius: 12px !important;
            color: var(--primary-rasa) !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            position: relative !important;
        }

        .detail-extralink .qty-val {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 600 !important;
            color: var(--primary-rasa) !important;
            border: none !important;
            text-align: center !important;
            width: auto !important;
            flex: 1 1 auto !important;
            min-width: 1.5rem !important;
            max-width: 3rem !important;
            padding: 0 !important;
            background: transparent !important;
            line-height: 1.2 !important;
            -moz-appearance: textfield !important;
        }

        .detail-extralink .qty-val::-webkit-outer-spin-button,
        .detail-extralink .qty-val::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }

        .detail-extralink .qty-up,
        .detail-extralink .qty-down {
            color: var(--primary-rasa) !important;
            font-size: 16px !important;
            line-height: 1 !important;
            padding: 8px 4px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
        }

        .detail-extralink .qty-up:hover,
        .detail-extralink .qty-down:hover {
            color: var(--btn-rasa-hover) !important;
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

        /* Keep icon spacing for CTA buttons */
        .button-add-to-cart i, .btn-buy i, button.add i {
            margin-right: 8px !important;
            font-size: 18px !important;
        }

        /* Halaman detail produk: pakai tombol standar (btn), hindari style lama .button Nest */
        .product-extra-link2 .btn.button-add-to-cart {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 48px !important;
            line-height: 1.25 !important;
            height: auto !important;
            padding: 12px 28px !important;
            vertical-align: middle !important;
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
            color: rgba(111, 23, 21, 1) !important;
        }

        .main-menu nav ul li a:hover {
            color: rgba(79, 12, 10, 1) !important; /* darker on hover */
        }

        .main-menu nav ul li a.active,
        .main-menu nav ul li a.active:hover {
            color: rgba(111, 23, 21, 1) !important;
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

        /* Tab label home (Produk Populer, dll.): maroon, hover hitam */
        .nav-tabs.links .nav-link {
            color: var(--primary-rasa) !important;
        }

        .nav-tabs.links .nav-link.active {
            color: var(--primary-rasa) !important;
        }

        .nav-tabs.links .nav-link:hover,
        .nav-tabs.links .nav-link:focus-visible {
            color: #000000 !important;
            background: none !important;
            background-color: transparent !important;
            transform: none !important;
        }

        .nav-tabs.links .nav-link.active:hover,
        .nav-tabs.links .nav-link.active:focus-visible {
            color: #000000 !important;
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

        /* Custom Brand Dropdown Styling */
        .categories-dropdown-wrap.categories-dropdown-active-large {
            padding: 30px !important;
            border-radius: 15px !important;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1) !important;
            width: 500px !important; /* Ensure enough width for 2 columns */
            background: #fff !important;
            border: 1px solid #F2EAE1 !important;
        }

        /* Force 2-column layout like screenshot (cards grid) */
        .categories-dropdown-wrap .categori-dropdown-inner {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 18px !important;
            align-items: start !important;
        }

        .categories-dropdown-wrap .categori-dropdown-inner ul {
            width: auto !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .categories-dropdown-wrap ul li {
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            background: none !important;
            list-style: none !important;
        }

        .categories-dropdown-wrap ul li a {
            border: 1px solid #F2EAE1 !important;
            border-radius: 12px !important;
            padding: 15px 20px !important;
            margin: 0 0 15px 0 !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 700 !important;
            color: #253D4E !important;
            background: #fff !important;
            height: 70px !important;
            line-height: 1.2 !important;
            width: 100% !important;
        }

        .categories-dropdown-wrap ul li a:hover {
            border-color: #6A1B1B !important;
            color: #6A1B1B !important;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(106, 27, 27, 0.08) !important;
            background: #fff !important;
        }

        /* Active brand (matches screenshot selected card) */
        .categories-dropdown-wrap ul li a.rg-brand-active {
            border-color: #6A1B1B !important;
            box-shadow: 0 10px 20px rgba(106, 27, 27, 0.06) !important;
        }

        .categories-dropdown-wrap ul li a img {
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            margin-right: 15px !important;
            object-fit: contain !important;
            border-radius: 0 !important;
        }

        .categories-dropdown-wrap .more_categories {
            padding-top: 20px !important;
            border-top: 1px solid #F2EAE1 !important;
            margin-top: 5px !important;
            text-align: center !important;
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 10px !important;
        }

        /* Plus icon circle like screenshot */
        .categories-dropdown-wrap .more_categories .icon {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 3px solid #6A1B1B;
            position: relative;
            display: inline-block;
        }
        .categories-dropdown-wrap .more_categories .icon::before,
        .categories-dropdown-wrap .more_categories .icon::after {
            content: "";
            position: absolute;
            background: #6A1B1B;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            border-radius: 2px;
        }
        .categories-dropdown-wrap .more_categories .icon::before { width: 14px; height: 3px; }
        .categories-dropdown-wrap .more_categories .icon::after { width: 3px; height: 14px; }

        .categories-dropdown-wrap .more_categories .heading-sm-1 {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 700 !important;
            color: #7E7E7E !important;
            font-size: 16px !important;
        }

        .categories-dropdown-wrap .more_categories i {
            font-size: 20px !important;
            color: #6A1B1B !important;
        }

        /* ------------------------------------------------------------------ */
        /* Brand dropdown (match Figma spec shared by user)                     */
        /* ------------------------------------------------------------------ */
        .main-categori-wrap .categories-dropdown-wrap.categories-dropdown-active-large {
            box-sizing: border-box !important;
            width: 502.23px !important;
            height: auto !important;
            padding: 26px 30px 22px !important;
            background: #FFFFFF !important;
            border: 1px solid #D0B3AD !important;
            box-shadow: 20px 20px 40px rgba(24, 24, 24, 0.07) !important;
            border-radius: 10px !important;
            overflow: hidden !important;
        }

        .main-categori-wrap .categories-dropdown-wrap .categori-dropdown-inner {
            display: grid !important;
            grid-template-columns: 211.5px 211.5px !important;
            column-gap: 18px !important;
            row-gap: 15px !important;
            align-items: start !important;
        }

        .main-categori-wrap .categories-dropdown-wrap .categori-dropdown-inner ul {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: auto !important;
        }

        .main-categori-wrap .categories-dropdown-wrap .categori-dropdown-inner ul li {
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
        }

        .main-categori-wrap .categories-dropdown-wrap .categori-dropdown-inner ul li a {
            box-sizing: border-box !important;
            width: 211.5px !important;
            height: 48px !important;
            background: #FFFFFF !important;
            border: 1px solid #F2F3F4 !important;
            border-radius: 5px !important;
            padding: 0 14px !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            margin: 0 !important;
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 500 !important;
            font-size: 14px !important;
            line-height: 20px !important;
            letter-spacing: -0.0004em !important;
            color: #1D1D1D !important;
        }

        .main-categori-wrap .categories-dropdown-wrap .categori-dropdown-inner ul li a:hover {
            border-color: #D0B3AD !important;
            box-shadow: 5px 5px 15px rgba(24, 24, 24, 0.05) !important;
            transform: none !important;
        }

        /* Selected item (Figma: border #D0B3AD + shadow) */
        .main-categori-wrap .categories-dropdown-wrap .categori-dropdown-inner ul li a.rg-brand-active {
            border-color: #D0B3AD !important;
            box-shadow: 5px 5px 15px rgba(24, 24, 24, 0.05) !important;
        }

        .main-categori-wrap .categories-dropdown-wrap .categori-dropdown-inner ul li a img {
            width: 30px !important;
            height: 30px !important;
            min-width: 30px !important;
            margin-right: 0 !important;
            object-fit: contain !important;
            display: block !important;
        }

        /* Bottom link (More) */
        .main-categori-wrap .categories-dropdown-wrap .more_categories {
            margin-top: 12px !important;
            padding-top: 14px !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
            border-top: 1px solid #D0B3AD !important;
            justify-content: center !important;
        }

        .main-categori-wrap .categories-dropdown-wrap .more_categories .heading-sm-1 {
            font-family: 'Fira Sans', sans-serif !important;
            font-weight: 500 !important;
            font-size: 14px !important;
            color: #7E7E7E !important;
        }

        /* Keep responsive fallback */
        @media (max-width: 992px) {
            .main-categori-wrap .categories-dropdown-wrap.categories-dropdown-active-large {
                width: 92vw !important;
                height: auto !important;
            }
            .main-categori-wrap .categories-dropdown-wrap .categori-dropdown-inner {
                grid-template-columns: 1fr 1fr !important;
            }
            .main-categori-wrap .categories-dropdown-wrap .categori-dropdown-inner ul li a {
                width: 100% !important;
            }
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

                        @if (session('status') === 'verification-link-sent')
                            <span class="ms-2 fw-bold" style="color: #155724;">Link verifikasi sudah dikirim ulang.</span>
                        @endif

                        @if (session('error'))
                            <span class="ms-2 fw-bold" style="color: #721c24;">
                                {{ session('error') }}
                                @if (session('debug_id'))
                                    <span class="fw-normal">(debug_id: {{ session('debug_id') }})</span>
                                @endif
                            </span>
                        @endif
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
    @include('themes.nest.partials.shop-toast')

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
                                    showShopToast("Lokasi terdeteksi! Kami memilih '" + response.hub.name + "' sebagai hub terdekat.", 'success');
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
                            showShopToast(response.message, 'success');
                            
                            // Update cart counts in header
                            $('.pro-count').text(response.cart_count);
                            
                            // Update mini-cart content
                            if (response.mini_cart_html) {
                                $('.cart-dropdown-wrap').html(response.mini_cart_html);
                            }
                        } else {
                            showShopToast(response.error || 'Terjadi kesalahan saat menambahkan produk.', 'error');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const body = xhr.responseJSON || {};
                            let message = '';
                            if (body.errors) {
                                for (const key in body.errors) {
                                    message += body.errors[key][0] + ' ';
                                }
                            } else if (body.error) {
                                message = body.error;
                            } else if (body.message) {
                                message = body.message;
                            }
                            showShopToast(message.trim() || 'Validasi gagal.', 'error');
                        } else if (xhr.status === 302) {
                            // Handle case where back() redirect happens (non-ajax fallback)
                            // This shouldn't normally happen with X-Requested-With
                            window.location.reload();
                        } else {
                            showShopToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
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
