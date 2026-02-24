<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('themes.nest.partials.head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
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

    @if(session('success'))
        <div class="container mt-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
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
                            swal({
                                title: "Berhasil!",
                                text: response.message,
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            // Update cart count in header if element exists
                            $('.pro-count.blue').text(response.cart_count);
                            $('.pro-count.white').text(response.cart_count);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            swal({
                                title: "Perhatian!",
                                text: "Silakan masuk terlebih dahulu untuk belanja.",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonText: "Masuk Sekarang",
                                cancelButtonText: "Nanti",
                                closeOnConfirm: true
                            }, function() {
                                window.location.href = '{{ route("login") }}';
                            });
                            return;
                        }

                        let errorMsg = "Terjadi kesalahan saat menambahkan ke keranjang.";
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            errorMsg = Object.values(errors).flat()[0];
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        
                        swal({
                            title: "Gagal!",
                            text: errorMsg,
                            type: "error"
                        });
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
