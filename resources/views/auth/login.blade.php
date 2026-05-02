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
<div class="page-content pt-150 pb-150" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-xl-9 col-lg-10 col-md-12 m-auto">
                <div class="row align-items-center">
                    <div class="col-lg-6 pr-30 d-none d-lg-block">
                        <img class="border-radius-20 shadow-lg" src="{{ asset('themes/nest-frontend/assets/imgs/page/login-1.png') }}" alt="Login" />
                    </div>
                    <div class="col-lg-6 col-md-8">
                        <div class="login_wrap widget-taber-content background-white p-30 border-radius-20">
                            <div class="padding_eight_all">
                                <div class="heading_s1">
                                    <h1 class="mb-5" style="font-family: 'Fira Sans', sans-serif; font-weight: 700;">Masuk</h1>
                                    <p class="mb-30" style="font-family: 'Lato', sans-serif; color: #7E7E7E;">Belum punya akun? <a href="{{ route('register') }}" style="color: #6A1B1B; font-weight: 600;">Daftar di sini</a></p>
                                </div>
                                @if (session('error'))
                                    <div class="alert alert-danger border-0 mb-20" style="border-radius: 12px;" role="alert">
                                        {{ session('error') }}
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div class="form-group mb-20">
                                        <input type="email" required="" name="email" placeholder="Username atau Email *" value="{{ old('email') }}" autofocus style="background: #ffffff; border: none; border-radius: 12px; padding: 15px 25px; height: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02);" />
                                        @error('email')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-20 position-relative">
                                        <input id="password" required="" type="password" name="password" placeholder="Kata sandi Anda *" style="background: #ffffff; border: none; border-radius: 12px; padding: 15px 50px 15px 25px; height: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02); width: 100%;" />
                                        <span class="password-toggle" onclick="togglePassword()" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #7E7E7E;">
                                            <i class="fi-rs-eye" id="toggleIcon"></i>
                                        </span>
                                        @error('password')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="login_footer form-group mb-50 d-flex justify-content-between align-items-center">
                                        <div class="chek-form">
                                            <div class="custome-checkbox">
                                                <input class="form-check-input" type="checkbox" name="remember" id="exampleCheckbox1" value="" />
                                                <label class="form-check-label" for="exampleCheckbox1"><span style="color: #6A1B1B; font-weight: 500;">Ingat saya</span></label>
                                            </div>
                                        </div>
                                        <a class="text-muted small" href="{{ route('password.request') }}">Lupa kata sandi?</a>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-heading btn-block hover-up" name="login" style="width: 100%; background-color: rgba(111, 23, 21, 1); color: #ffffff; border-radius: 12px; height: 55px; font-weight: 700; border: none;">Masuk</button>
                                    </div>
                                    
                                    <div class="form-group text-center mt-30">
                                        <p class="mb-10 text-muted small">Atau masuk dengan</p>
                                        <a href="{{ route('google.login', ['intent' => 'login']) }}" class="btn btn-heading btn-block hover-up google-login" style="background-color: rgba(111, 23, 21, 1); color: #ffffff; border: none; width: 100%; display: flex; align-items: center; justify-content: center; height: 55px; font-weight: 700; border-radius: 12px;">
                                            <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/logo-google.svg') }}" alt="" style="width: 18px; margin-right: 10px;" />
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

@push('scripts')
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fi-rs-eye');
            toggleIcon.classList.add('fi-rs-eye-crossed');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fi-rs-eye-crossed');
            toggleIcon.classList.add('fi-rs-eye');
        }
    }
</script>
@endpush
