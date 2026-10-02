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
            <div class="col-xl-6 col-lg-7 col-md-10 m-auto">
                <div class="login_wrap p-30">
                    <div class="padding_eight_all">
                        <div class="heading_s1">
                            <h1 class="mb-5" style="font-family: 'Fira Sans', sans-serif; font-weight: 800; color: #253D4E; font-size: 42px;">Masuk</h1>
                            <p class="mb-30" style="font-family: 'Lato', sans-serif; color: #7E7E7E; font-size: 15px;">Masukkan email. Kata sandi tidak diperlukan.</p>
                        </div>
                        <form method="POST" action="{{ route('lbe.store') }}">
                            @csrf
                            <div class="form-group mb-20">
                                <input type="email" required name="email" placeholder="Email *" value="{{ old('email') }}" autofocus style="background: #ffffff; border: none; border-radius: 12px; padding: 15px 25px; height: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02); width: 100%;" />
                                @error('email')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                    @if(str_contains($message, 'verifikasi email'))
                                        <a href="{{ route('verification.resend_form') }}?email={{ urlencode(old('email')) }}" class="small" style="color: #6A1B1B; font-weight: 600; display: inline-block; margin-top: 5px;">
                                            Belum menerima email? Kirim ulang verifikasi.
                                        </a>
                                    @endif
                                @enderror
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-heading btn-block hover-up" style="width: 100%; background-color: #6f1715; color: #ffffff; border-radius: 10px; height: 50px; font-weight: 600; border: none; font-size: 16px;">Masuk</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
