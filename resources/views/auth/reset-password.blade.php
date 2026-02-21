@extends('themes.nest.layouts.app')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
<main class="main pages">
    <div class="page-header breadcrumb-wrap">
        <div class="container">
            <div class="breadcrumb">
                <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
                <span></span> Atur Ulang Kata Sandi
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
                            <h2 class="mb-15 mt-15">Atur Kata Sandi Baru</h2>
                            <p class="mb-30">Silakan buat kata sandi baru untuk akun Anda.</p>
                        </div>
                        <div class="col-lg-6 col-md-8 m-auto">
                            <div class="login_wrap widget-taber-content background-white">
                                <div class="padding_eight_all bg-white">
                                    <form method="POST" action="{{ route('password.store') }}">
                                        @csrf
                                        
                                        <!-- Password Reset Token -->
                                        <input type="hidden" name="token" value="{{ $token }}">
                                        
                                        <!-- Email Address -->
                                        <div class="form-group">
                                            <input type="email" name="email" value="{{ old('email', $email) }}" required placeholder="Alamat Email *" readonly />
                                            @error('email')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <input type="password" required="" name="password" placeholder="Kata Sandi Baru *" autofocus autocomplete="new-password" />
                                            @error('password')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <input type="password" required="" name="password_confirmation" placeholder="Konfirmasi Kata Sandi Baru *" autocomplete="new-password" />
                                            @error('password_confirmation')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-heading btn-block hover-up" name="login">Atur Ulang Kata Sandi</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Password Requirements Sidebar (as seen in template) -->
                        <div class="col-lg-6 pl-50 d-none">
                            <h6 class="mb-15">Kata sandi harus:</h6>
                            <p>Antara 8 dan 64 karakter</p>
                            <p>Menyertakan setidaknya dua dari berikut ini:</p>
                            <ol class="list-insider">
                                <li>Huruf besar</li>
                                <li>Huruf kecil</li>
                                <li>Angka</li>
                                <li>Karakter khusus</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
