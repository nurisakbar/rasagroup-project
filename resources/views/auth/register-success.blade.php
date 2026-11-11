@extends('themes.nest.layouts.app')

@section('title', 'Pendaftaran Berhasil')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Daftar
            <span></span> Berhasil
        </div>
    </div>
</div>
<div class="page-content pt-100 pb-100" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-xl-6 col-lg-8 col-md-10 m-auto">
                <div class="login_wrap widget-taber-content p-30 p-md-40 border-radius-15 shadow-sm text-center" style="background-color: #ffffff;">
                    <div class="padding_eight_all">
                        <div class="mb-25">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle mb-20" style="width: 88px; height: 88px; background: rgba(106, 27, 27, 0.12);">
                                <i class="fi-rs-envelope" style="font-size: 40px; color: #6A1B1B;"></i>
                            </span>
                            <h1 class="mb-10" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">
                                Pendaftaran berhasil
                            </h1>
                            <p class="mb-0" style="max-width: 440px; margin-left: auto; margin-right: auto; color: #4F5D77; font-size: 16px;">
                                Kami telah mengirim email aktivasi ke
                                <strong style="color: #253D4E; word-break: break-all;">{{ $email }}</strong>.
                                Aktifkan akun Anda sebelum masuk.
                            </p>
                        </div>

                        <div class="text-start p-20 border-radius-15 mb-25" style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;">
                            <h6 class="mb-10" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Langkah aktivasi</h6>
                            <ol class="mb-0 ps-3" style="color: #4F5D77; font-size: 15px; line-height: 1.8;">
                                <li>Buka kotak masuk email <strong>{{ $email }}</strong>.</li>
                                <li>Cari email dari RasaConnect, lalu klik tombol <strong>verifikasi email</strong> di dalamnya.</li>
                                <li>Setelah email terverifikasi, masuk menggunakan email dan kata sandi Anda.</li>
                            </ol>
                        </div>

                        <p class="font-sm mb-25" style="color: #7E7E7E;">
                            Tidak menemukan emailnya? Periksa folder <strong>Spam</strong> atau <strong>Promosi</strong>, atau
                            <a href="{{ route('verification.resend_form', ['email' => $email]) }}" style="color: #6A1B1B; font-weight: 600;">kirim ulang email aktivasi</a>.
                        </p>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center align-items-stretch">
                            <a href="{{ route('home') }}" class="btn hover-up flex-sm-fill" style="background-color: transparent; color: #6A1B1B; border: 2px solid #6A1B1B; border-radius: 12px; height: 52px; font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center;">
                                Ke beranda
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-heading flex-sm-fill hover-up" style="background-color: #6A1B1B; color: #fff; border: none; border-radius: 12px; height: 52px; font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center;">
                                Masuk <i class="fi-rs-arrow-right ml-8"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
