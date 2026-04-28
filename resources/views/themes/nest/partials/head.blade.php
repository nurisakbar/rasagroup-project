<meta charset="utf-8" />
<title>@hasSection('title') @yield('title') | Multi Citra Rasa Marketplace @else Multi Citra Rasa Marketplace - Solusi Kebutuhan Produk Berkualitas @endif</title>
<meta http-equiv="x-ua-compatible" content="ie=edge" />
<meta name="description" content="@yield('meta_description', 'Multi Citra Rasa Marketplace menyediakan berbagai produk berkualitas mulai dari sirup premium, bahan makanan, hingga kebutuhan horeca dengan layanan terbaik.')" />
<meta name="keywords" content="Multi Citra Rasa, Marketplace, Sirup Premium, Horeca, Bahan Makanan, Rasa Group" />
<meta name="robots" content="index, follow" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:title" content="@yield('title', 'Multi Citra Rasa Marketplace')" />
<meta property="og:description" content="@yield('meta_description', 'Multi Citra Rasa Marketplace - Solusi Kebutuhan Produk Berkualitas')" />
<meta property="og:image" content="@yield('og_image', asset('logorasa.png'))" />

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image" />
<meta property="twitter:url" content="{{ url()->current() }}" />
<meta property="twitter:title" content="@yield('title', 'Multi Citra Rasa Marketplace')" />
<meta property="twitter:description" content="@yield('meta_description', 'Multi Citra Rasa Marketplace - Solusi Kebutuhan Produk Berkualitas')" />
<meta property="twitter:image" content="@yield('og_image', asset('logorasa.png'))" />
<!-- Favicon -->
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('themes/nest-frontend/assets/imgs/theme/favicon.svg') }}" />
<!-- Template CSS -->
<link rel="stylesheet" href="{{ asset('themes/nest-frontend/assets/css/plugins/animate.min.css') }}" />
<link rel="stylesheet" href="{{ asset('themes/nest-frontend/assets/css/main.css?v=6.1') }}" />
<link rel="stylesheet" href="{{ asset('rasafont/fonts.css') }}" />
<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
@stack('styles')
