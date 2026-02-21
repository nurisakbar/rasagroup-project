@extends('themes.nest.layouts.app')

@section('title', 'Daftar')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Daftar
        </div>
    </div>
</div>
<div class="page-content pt-150 pb-150">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 col-lg-10 col-md-12 m-auto">
                <div class="row">
                    <div class="col-lg-10 col-md-12 m-auto">
                        <div class="login_wrap widget-taber-content background-white">
                            <div class="padding_eight_all bg-white">
                                <div class="heading_s1">
                                    <h1 class="mb-5">Buat Akun</h1>
                                    <p class="mb-30">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
                                </div>
                                <form method="POST" action="{{ route('register') }}">
                                    @csrf
                                    <div class="form-group">
                                        <input type="text" required="" name="name" placeholder="Nama" value="{{ old('name') }}" autofocus />
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <input type="email" required="" name="email" placeholder="Email" value="{{ old('email') }}" />
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <input required="" type="password" name="password" placeholder="Kata Sandi" />
                                        @error('password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <input required="" type="password" name="password_confirmation" placeholder="Konfirmasi Kata Sandi" />
                                    </div>

                                    
                                    <div class="login_footer form-group mb-50">
                                        <div class="chek-form">
                                            <div class="custome-checkbox">
                                                <input class="form-check-input" type="checkbox" name="terms" id="exampleCheckbox12" value="" required />
                                                <label class="form-check-label" for="exampleCheckbox12"><span>Saya setuju dengan Syarat &amp; Kebijakan.</span></label>
                                            </div>
                                        </div>
                                        <a href="page-privacy-policy.html"><i class="fi-rs-book-alt mr-5 text-muted"></i>Pelajari lebih lanjut</a>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-fill-out btn-block hover-up font-weight-bold" name="login" style="width: 100%;">Daftar Sekarang</button>
                                    </div>
                                    <div class="form-group text-center">
                                        <p class="mb-10 text-muted">Atau daftar dengan</p>
                                        <a href="{{ route('google.login') }}" class="btn btn-fill-out btn-block hover-up font-weight-bold google-login" style="background-color: #fff; color: #333; border: 1px solid #ddd; width: 100%; display: flex; align-items: center; justify-content: center; height: 64px;">
                                            <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/logo-google.svg') }}" alt="" style="width: 20px; margin-right: 10px;" />
                                            Daftar dengan Google
                                        </a>
                                    </div>
                                    <p class="font-xs text-muted"><strong>Catatan:</strong> Data pribadi Anda akan digunakan untuk mendukung pengalaman Anda di seluruh situs web ini, untuk mengelola akses ke akun Anda, dan untuk tujuan lain yang dijelaskan dalam kebijakan privasi kami.</p>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
