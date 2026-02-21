@extends('themes.nest.layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Masuk
        </div>
    </div>
</div>
<div class="page-content pt-150 pb-150">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 col-lg-10 col-md-12 m-auto">
                <div class="row">
                    <div class="col-lg-6 pr-30 d-none d-lg-block">
                        <img class="border-radius-15" src="{{ asset('themes/nest-frontend/assets/imgs/page/login-1.png') }}" alt="" />

                    </div>
                    <div class="col-lg-6 col-md-8">
                        <div class="login_wrap widget-taber-content background-white">
                            <div class="padding_eight_all bg-white">
                                <div class="heading_s1">
                                    <h1 class="mb-5">Masuk</h1>
                                    <p class="mb-30">Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></p>
                                </div>
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div class="form-group">
                                        <input type="email" required="" name="email" placeholder="Email *" value="{{ old('email') }}" autofocus />
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <input required="" type="password" name="password" placeholder="Kata sandi Anda *" />
                                        @error('password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="login_footer form-group mb-50">
                                        <div class="chek-form">
                                            <div class="custome-checkbox">
                                                <input class="form-check-input" type="checkbox" name="remember" id="exampleCheckbox1" value="" />
                                                <label class="form-check-label" for="exampleCheckbox1"><span>Ingat saya</span></label>
                                            </div>
                                        </div>
                                        <a class="text-muted" href="{{ route('password.request') }}">Lupa kata sandi?</a>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-heading btn-block hover-up" name="login" style="width: 100%;">Masuk</button>
                                    </div>
                                    <div class="form-group text-center mt-30">
                                        <p class="mb-10 text-muted">Atau masuk dengan</p>
                                        <a href="{{ route('google.login') }}" class="btn btn-heading btn-block hover-up google-login" style="background-color: #fff; color: #333; border: 1px solid #ddd; width: 100%; display: flex; align-items: center; justify-content: center; height: 64px; font-weight: 700;">
                                            <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/logo-google.svg') }}" alt="" style="width: 20px; margin-right: 10px;" />
                                            Masuk dengan Google
                                        </a>
                                    </div>
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
