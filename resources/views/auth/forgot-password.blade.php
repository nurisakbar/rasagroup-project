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
    <div class="page-content pt-150 pb-150">
        <div class="container">
            <div class="row">
                <div class="col-xl-6 col-lg-8 col-md-12 m-auto">
                    <div class="row">
                        <div class="heading_s1 text-center">
                            <img class="border-radius-15" src="{{ asset('themes/nest-frontend/assets/imgs/page/reset_password.svg') }}" alt="" style="max-width: 100px; margin: 0 auto;" />
                            <h2 class="mb-15 mt-15">Lupa Kata Sandi?</h2>
                            <p class="mb-30">Jangan khawatir, masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.</p>
                        </div>
                        <div class="col-lg-8 col-md-10 m-auto">
                            <div class="login_wrap widget-taber-content background-white">
                                <div class="padding_eight_all bg-white">
                                    
                                    @if (session('status'))
                                        <div class="alert alert-success mb-4" role="alert">
                                            {{ session('status') }}
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('password.email') }}">
                                        @csrf
                                        
                                        <div class="form-group">
                                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="Alamat Email Anda *" autofocus />
                                            @error('email')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-heading btn-block hover-up" name="login" style="width: 100%; height: 64px; font-weight: 700;">Kirim Tautan Atur Ulang</button>
                                        </div>
                                    </form>
                                    <div class="text-muted text-center mt-30">
                                        Sudah ingat kata sandi Anda? <a href="{{ route('login') }}">Masuk sekarang</a>
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
