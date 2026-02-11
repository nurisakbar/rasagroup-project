<meta charset="utf-8" />
<title>@yield('title', 'Nest - Multipurpose eCommerce HTML Template')</title>
<meta http-equiv="x-ua-compatible" content="ie=edge" />
<meta name="description" content="@yield('meta_description', '')" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta property="og:title" content="@yield('og_title', '')" />
<meta property="og:type" content="@yield('og_type', '')" />
<meta property="og:url" content="@yield('og_url', '')" />
<meta property="og:image" content="@yield('og_image', '')" />
<!-- Favicon -->
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('themes/nest-frontend/assets/imgs/theme/favicon.svg') }}" />
<!-- Template CSS -->
<link rel="stylesheet" href="{{ asset('themes/nest-frontend/assets/css/plugins/animate.min.css') }}" />
<link rel="stylesheet" href="{{ asset('themes/nest-frontend/assets/css/main.css?v=6.1') }}" />
@stack('styles')
