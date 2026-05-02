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
                    @auth
                        @php $user = auth()->user(); @endphp
                    @endauth

                    <p class="text-muted mb-4">
                        Terima kasih, alamat email Anda telah berhasil diverifikasi.
                        @auth
                            @if($user->isSuperAdmin() || $user->isDistributor() || $user->wa_verified_at)
                                <br>Anda dapat melanjutkan menggunakan akun Anda.
                            @else
                                <br>Akun Anda selangkah lagi aktif sepenuhnya setelah nomor WhatsApp diverifikasi.
                            @endif
                        @else
                            <br>Silakan masuk untuk melanjutkan.
                        @endauth
                    </p>

                    @auth
                        @if(! $user->isSuperAdmin() && ! $user->isDistributor() && ! $user->wa_verified_at)
                            <div class="p-4 mb-40 mx-auto" style="max-width: 500px; background: #f8f9fa; border-radius: 15px; border: 1px dashed #6A1B1B;">
                                <h5 class="mb-2">Langkah Terakhir: Verifikasi WhatsApp</h5>
                                <p class="mb-0 text-muted small">
                                    Untuk keamanan tambahan, silakan lanjutkan untuk memverifikasi nomor WhatsApp Anda.
                                </p>
                            </div>
                            <a href="{{ route('wa.verify') }}" class="btn btn-lg btn-brush-3" style="background-color: #6A1B1B !important; border: none; padding: 14px 34px;">
                                Lanjutkan Verifikasi WhatsApp <i class="fi-rs-arrow-right ml-10"></i>
                            </a>
                        @else
                            <div class="d-flex flex-wrap gap-3 justify-content-center">
                                <a href="{{ route('dashboard') }}" class="btn btn-lg btn-brush-3" style="background-color: #6A1B1B !important; border: none; padding: 14px 34px;">
                                    Ke dashboard <i class="fi-rs-arrow-right ml-10"></i>
                                </a>
                                <a href="{{ route('home') }}" class="btn btn-lg" style="background-color: #fff !important; color: #6A1B1B !important; border: 2px solid #6A1B1B; padding: 14px 34px;">
                                    Ke beranda
                                </a>
                            </div>
                        @endif
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
