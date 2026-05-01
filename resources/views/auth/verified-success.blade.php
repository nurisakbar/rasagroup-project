@extends('themes.nest.layouts.app')

@section('title', 'Email Berhasil Diverifikasi')

@section('content')
<div class="page-content pt-150 pb-150">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 col-lg-10 col-md-12 m-auto text-center">
                <div class="mb-50">
                    <div class="mb-4">
                        <i class="fi-rs-check-circle" style="font-size: 84px; color: #6A1B1B;"></i>
                    </div>
                    <h2 class="mb-3">Email Berhasil Dikonfirmasi!</h2>
                    <p class="text-muted mb-4">
                        Terima kasih, alamat email Anda telah berhasil diverifikasi. <br>
                        Akun Anda sekarang selangkah lebih dekat untuk aktif sepenuhnya.
                    </p>
                    
                    <div class="p-4 mb-40 mx-auto" style="max-width: 500px; background: #f8f9fa; border-radius: 15px; border: 1px dashed #6A1B1B;">
                        <h5 class="mb-2">Langkah Terakhir: Verifikasi WhatsApp</h5>
                        <p class="mb-0 text-muted small">
                            Untuk keamanan tambahan, silakan lanjutkan untuk memverifikasi nomor WhatsApp Anda.
                        </p>
                    </div>

                    @auth
                        <a href="{{ route('wa.verify') }}" class="btn btn-lg btn-brush-3" style="background-color: #6A1B1B !important; border: none; padding: 14px 34px;">
                            Lanjutkan Verifikasi WhatsApp <i class="fi-rs-arrow-right ml-10"></i>
                        </a>
                    @endauth

                    @guest
                        <a href="{{ route('login') }}" class="btn btn-lg btn-brush-3" style="background-color: #6A1B1B !important; border: none; padding: 14px 34px;">
                            Login untuk lanjut <i class="fi-rs-arrow-right ml-10"></i>
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
