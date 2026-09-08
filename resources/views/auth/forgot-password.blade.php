@extends('themes.nest.layouts.app')

@section('title', 'Lupa Kata Sandi')

@section('content')
<main class="main pages">
    <div class="page-header breadcrumb-wrap">
        <div class="container">
            <div class="breadcrumb">
                <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
                <span></span> Lupa Kata Sandi
            </div>
        </div>
    </div>
    <div class="page-content pt-150 pb-150" style="background-color: #F2EAE1;">
        <div class="container">
            <div class="row">
                <div class="col-xl-6 col-lg-8 col-md-12 m-auto">
                    <div class="login_wrap p-40 background-white" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                        <div class="row align-items-center">
                            <div class="heading_s1 text-center w-100">
                                <img class="border-radius-15" src="{{ asset('themes/nest-frontend/assets/imgs/page/reset_password.svg') }}" alt="Reset Password" style="max-width: 100px; margin: 0 auto;" />
                                <h1 class="mb-10 mt-20" style="font-family: 'Fira Sans', sans-serif; font-weight: 800; color: #253D4E; font-size: 32px;">Lupa Kata Sandi?</h1>
                                <p class="mb-30" style="font-family: 'Lato', sans-serif; color: #7E7E7E; font-size: 15px;">Jangan khawatir, masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.</p>
                            </div>
                            <div class="col-lg-10 col-md-12 m-auto">
                                <div class="padding_eight_all">
                                    
                                    @if (session('status'))
                                        <div class="alert alert-success border-0 mb-30" style="border-radius: 12px; font-weight: 600;" role="alert">
                                            {{ session('status') }}
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('password.email') }}">
                                        @csrf
                                        
                                        <div class="form-group mb-25">
                                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="Alamat Email Anda *" autofocus style="background: #ffffff; border: 1px solid #ececec; border-radius: 12px; padding: 15px 25px; height: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02); width: 100%; transition: all 0.3s ease;" />
                                            @error('email')
                                                <span class="text-danger small mt-2 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-heading btn-block hover-up" name="login" style="width: 100%; background-color: #6f1715; color: #ffffff; border-radius: 10px; height: 50px; font-weight: 600; border: none; font-size: 16px;">Kirim Tautan Atur Ulang</button>
                                        </div>
                                    </form>
                                    
                                    <div class="text-muted text-center mt-30" style="font-family: 'Lato', sans-serif; color: #7E7E7E; font-size: 15px;">
                                        Sudah ingat kata sandi Anda? <br>
                                        <a href="{{ route('login') }}" style="color: #6A1B1B; font-weight: 600; display: inline-block; margin-top: 5px;">Masuk sekarang</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
