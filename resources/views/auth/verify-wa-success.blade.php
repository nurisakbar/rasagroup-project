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
                <div class="login_wrap widget-taber-content background-white p-30 p-md-40 border-radius-15 shadow-sm text-center">
                    <div class="padding_eight_all">
                        <div class="mb-25">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle mb-20" style="width: 88px; height: 88px; background: rgba(106, 27, 27, 0.12);">
                                <i class="fi-rs-check" style="font-size: 42px; color: #6A1B1B;"></i>
                            </span>
                            <h1 class="mb-10" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">
                                WhatsApp berhasil diverifikasi
                            </h1>
                            <p class="text-muted mb-0" style="max-width: 420px; margin-left: auto; margin-right: auto;">
                                Nomor <strong class="text-dark">{{ auth()->user()->phone }}</strong> telah terhubung dengan akun Anda.
                                Anda bisa melanjutkan belanja dan mengakses fitur akun sepenuhnya.
                            </p>
                        </div>

                        <div class="p-20 mb-30 text-start" style="background: #F8F9FA; border-radius: 12px; border: 1px solid #ECECEC;">
                            <p class="small text-muted mb-0">
                                <i class="fi-rs-shield-check text-brand mr-5"></i>
                                Kami menggunakan nomor ini untuk keamanan akun dan notifikasi pesanan penting.
                            </p>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center align-items-stretch">
                            <a href="{{ route('home') }}" class="btn btn-heading flex-sm-fill hover-up" style="background-color: #fff; color: #6A1B1B; border: 2px solid #6A1B1B; border-radius: 12px; height: 52px; font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center;">
                                Ke beranda
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn btn-heading flex-sm-fill hover-up" style="background-color: #6A1B1B; color: #fff; border: none; border-radius: 12px; height: 52px; font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center;">
                                Ke dashboard <i class="fi-rs-arrow-right ml-8"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
