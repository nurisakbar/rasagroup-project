@extends('themes.nest.layouts.app')

@section('title', 'WhatsApp Terverifikasi')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Verifikasi WhatsApp
            <span></span> Berhasil
        </div>
    </div>
</div>
<div class="page-content pt-100 pb-100" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-xl-6 col-lg-8 col-md-10 m-auto">
                <div class="login_wrap widget-taber-content p-30 p-md-40 border-radius-15 text-center" style="background-color: #ffffff !important; border: 1px solid #E3D6C8; box-shadow: 0 10px 30px rgba(106, 27, 27, 0.12) !important;">
                    <div class="padding_eight_all">
                        <div class="mb-25">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle mb-20" style="width: 104px; height: 104px; background: rgba(106, 27, 27, 0.08); box-shadow: 0 0 0 10px rgba(106, 27, 27, 0.05);">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 72px; height: 72px; background: #6A1B1B;">
                                    <i class="fi-rs-check" style="font-size: 34px; color: #ffffff; line-height: 1;"></i>
                                </span>
                            </span>
                            <h1 class="mb-15" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E; font-size: clamp(24px, 7vw, 36px); line-height: 1.25;">
                                WhatsApp berhasil<br class="d-sm-none"> diverifikasi
                            </h1>
                            <div class="d-inline-flex align-items-center mb-15 px-4 py-2" style="background: #FAF6F1; border: 1.5px solid #D9CFC4; border-radius: 50px;">
                                <i class="fi-rs-smartphone me-2" style="color: #6A1B1B; font-size: 18px;"></i>
                                <strong style="color: #6A1B1B; font-size: clamp(18px, 5.5vw, 24px); letter-spacing: 1px;">{{ auth()->user()->phone }}</strong>
                            </div>
                            <p class="mb-0" style="max-width: 420px; margin-left: auto; margin-right: auto; color: #4F5D77; font-size: 16px; line-height: 1.6;">
                                Nomor ini sudah terhubung dengan akun Anda. Anda bisa melanjutkan belanja dan mengakses semua fitur akun.
                            </p>
                        </div>

                        <div class="p-20 mb-30 text-start" style="background: #FAF6F1; border-radius: 12px; border: 1px solid #E3D6C8;">
                            <p class="small mb-0" style="color: #4F5D77;">
                                <i class="fi-rs-shield-check text-brand mr-5"></i>
                                Kami menggunakan nomor ini untuk keamanan akun dan notifikasi pesanan penting.
                            </p>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center align-items-stretch">
                            <a href="{{ route('home') }}" class="btn hover-up flex-sm-fill" style="background-color: #ffffff !important; color: #6A1B1B !important; border: 2px solid #6A1B1B !important; border-radius: 12px; height: 52px; font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center;">
                                Ke beranda
                            </a>
                            @if(!empty($continueUrl))
                                <a href="{{ $continueUrl }}" class="btn btn-heading flex-sm-fill hover-up" style="background-color: #6A1B1B; color: #fff; border: none; border-radius: 12px; height: 52px; font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center;">
                                    Lanjut belanja <i class="fi-rs-arrow-right ml-8"></i>
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="btn btn-heading flex-sm-fill hover-up" style="background-color: #6A1B1B; color: #fff; border: none; border-radius: 12px; height: 52px; font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center;">
                                    Ke dashboard <i class="fi-rs-arrow-right ml-8"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
