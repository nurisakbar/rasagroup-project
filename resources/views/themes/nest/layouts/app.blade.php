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

    <!-- SweetAlert v1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

    <script>
        $(document).ready(function() {
            @if(session('success'))
                swal({
                    title: "Berhasil!",
                    text: "{{ session('success') }}",
                    type: "success",
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif

            @if(session('error'))
                swal({
                    title: "Gagal!",
                    text: "{{ session('error') }}",
                    type: "error"
                });
            @endif

            @if(session('info'))
                swal({
                    title: "Informasi",
                    text: "{{ session('info') }}",
                    type: "info"
                });
            @endif

            @if(session('warning'))
                swal({
                    title: "Peringatan",
                    text: "{{ session('warning') }}",
                    type: "warning"
                });
            @endif

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
                                    swal({
                                        title: "Lokasi Terdeteksi!",
                                        text: "Kami telah memilih '" + response.hub.name + "' sebagai Hub terdekat Anda untuk kenyamanan belanja.",
                                        type: "success",
                                        timer: 4000,
                                        showConfirmButton: true
                                    }, function() {
                                        window.location.reload();
                                    });
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
