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
<div class="page-content pt-150 pb-150" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-xl-9 col-lg-10 col-md-12 m-auto">
                <div class="row align-items-center">
                    <div class="col-lg-6 pr-30 d-none d-lg-block">
                        <img class="border-radius-20 shadow-lg" src="{{ asset('themes/nest-frontend/assets/imgs/page/login-1.png') }}" alt="Register" />
                    </div>
                    <div class="col-lg-6 col-md-8">
                        <div class="login_wrap widget-taber-content background-white p-30 border-radius-20">
                            <div class="padding_eight_all">
                                <div class="heading_s1">
                                    <h1 class="mb-5" style="font-family: 'Fira Sans', sans-serif; font-weight: 700;">Daftar Akun</h1>
                                    <p class="mb-30" style="font-family: 'Lato', sans-serif; color: #7E7E7E;">Sudah punya akun? <a href="{{ route('login') }}" style="color: #6A1B1B; font-weight: 600;">Masuk di sini</a></p>
                                </div>
                                <form method="POST" action="{{ route('register') }}">
                                    @csrf
                                    <div class="form-group mb-20">
                                        <input type="text" required="" name="name" placeholder="Nama Lengkap *" value="{{ old('name') }}" autofocus style="background: #ffffff; border: none; border-radius: 12px; padding: 15px 25px; height: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02);" />
                                        @error('name')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-20">
                                        <input type="email" required="" name="email" placeholder="Email *" value="{{ old('email') }}" style="background: #ffffff; border: none; border-radius: 12px; padding: 15px 25px; height: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02);" />
                                        @error('email')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-20">
                                        <input required="" type="password" name="password" placeholder="Kata Sandi *" style="background: #ffffff; border: none; border-radius: 12px; padding: 15px 25px; height: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02);" />
                                        @error('password')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-20">
                                        <input required="" type="password" name="password_confirmation" placeholder="Konfirmasi Kata Sandi *" style="background: #ffffff; border: none; border-radius: 12px; padding: 15px 25px; height: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02);" />
                                    </div>
                                    
                                    <div class="login_footer form-group mb-50">
                                        <div class="chek-form">
                                            <div class="custome-checkbox">
                                                <input class="form-check-input" type="checkbox" name="terms" id="exampleCheckbox12" value="" required />
                                                <label class="form-check-label" for="exampleCheckbox12"><span style="color: #6A1B1B; font-weight: 500;">Saya setuju Syarat & Ketentuan</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-heading btn-block hover-up" name="login" style="width: 180px; background-color: #1a1a1a; color: #ffffff; border-radius: 12px; height: 55px; font-weight: 700; border: none;">Daftar Akun</button>
                                    </div>
                                    
                                    <div class="form-group text-center mt-30">
                                        <p class="mb-10 text-muted small">Atau daftar dengan</p>
                                        <a href="{{ route('google.login') }}" class="btn btn-heading btn-block hover-up google-login" style="background-color: #fff; color: #333; border: 1px solid #eee; width: 100%; display: flex; align-items: center; justify-content: center; height: 55px; font-weight: 600; border-radius: 12px;">
                                            <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/logo-google.svg') }}" alt="" style="width: 18px; margin-right: 10px;" />
                                            Daftar dengan Google
                                        </a>
                                    </div>
                                    <p class="font-xs text-muted mt-30">Data pribadi Anda akan dilindungi dan digunakan sesuai dengan kebijakan privasi kami.</p>
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
