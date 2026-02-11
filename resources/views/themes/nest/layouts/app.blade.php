<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    @include('themes.nest.partials.head')
</head>

<body>
    @include('themes.nest.partials.modals')
    @include('themes.nest.partials.header')
    @include('themes.nest.partials.mobile-header')

    <main class="main">
        @yield('content')
    </main>

    @include('themes.nest.partials.footer')
    @include('themes.nest.partials.preloader')
    @include('themes.nest.partials.scripts')
    
    @stack('scripts')
</body>

</html>
