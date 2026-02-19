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
        });
    </script>
    @stack('scripts')
</body>
</html>
