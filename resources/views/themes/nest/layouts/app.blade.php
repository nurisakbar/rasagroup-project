<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    @include('themes.nest.partials.head')
</head>

<body>
    @include('themes.nest.partials.header')
    @include('themes.nest.partials.mobile-header')

    <main class="main">
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
        });
    </script>
    
    @stack('scripts')
</body>

</html>
