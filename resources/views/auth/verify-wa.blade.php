@extends('themes.nest.layouts.app')

@section('title', 'Verifikasi WhatsApp')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Verifikasi WhatsApp
        </div>
    </div>
</div>
<div class="page-content pt-100 pb-100" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-xl-6 col-lg-8 col-md-10 m-auto">
                <div class="login_wrap widget-taber-content background-white p-30 border-radius-15 shadow-sm">
                    <div class="padding_eight_all">
                        <div class="heading_s1 text-center">
                            <i class="fi-rs-smartphone display-1 mb-20 text-brand"></i>
                            <h1 class="mb-5">Verifikasi WhatsApp</h1>
                            <p class="mb-30">Kami telah mengirimkan kode verifikasi 6 digit ke nomor WhatsApp Anda: <strong>{{ auth()->user()->phone }}</strong></p>
                        </div>
                        
                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success mt-10">
                                <i class="fi-rs-check-circle me-2"></i>
                                Kode verifikasi baru telah dikirim ke nomor WhatsApp Anda.
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger mt-10">
                                <i class="fi-rs-cross-circle me-2"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('wa.verify.post') }}" id="otp-form">
                            @csrf
                            <input type="hidden" name="code" id="verification_code">
                            <div class="form-group mb-30 text-center">
                                <label class="mb-20 font-weight-bold d-block">Masukkan 6 Digit Kode Verifikasi</label>
                                <div class="otp-container d-flex justify-content-center gap-2">
                                    <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                                    <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                                    <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                                    <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                                    <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                                    <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
                                </div>
                                @error('code')
                                    <span class="text-danger small d-block mt-10">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-heading btn-block hover-up" style="width: 100%; background-color: #6A1B1B; color: #ffffff; border-radius: 12px; height: 55px; font-weight: 700; border: none;">Verifikasi Sekarang</button>
                            </div>
                        </form>

                        <div class="text-center mt-30">
                            <p class="text-muted">Tidak menerima kode atau nomor salah?</p>
                            <div class="d-flex flex-column align-items-center gap-3">
                                <div class="d-flex justify-content-center gap-3">
                                    <form method="POST" action="{{ route('wa.resend') }}">
                                        @csrf
                                        <button type="submit" class="btn-link font-weight-bold border-0 bg-transparent" style="color: #6A1B1B;">Kirim Ulang Kode</button>
                                    </form>
                                    <span class="text-muted">|</span>
                                    <button type="button" class="btn-link text-muted border-0 bg-transparent" onclick="togglePhoneForm()">Ubah Nomor HP</button>
                                </div>
                                
                                <form method="POST" action="{{ route('wa.update-phone') }}" id="update-phone-form" class="mt-20 d-none" style="width: 100%;">
                                    @csrf
                                    <div class="form-group">
                                        <input type="text" name="phone" class="form-control" placeholder="Contoh: 08123456789" value="{{ auth()->user()->phone }}" required style="border-radius: 12px; height: 50px;">
                                    </div>
                                    <button type="submit" class="btn btn-sm mt-10" style="border-radius: 8px; background-color: #6A1B1B; color: white;">Simpan & Kirim Kode Baru</button>
                                </form>

                                <form method="POST" action="{{ route('logout') }}" class="mt-10">
                                    @csrf
                                    <button type="submit" class="btn-link text-muted border-0 bg-transparent small">Logout</button>
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

@push('styles')
<style>
    .otp-input {
        width: 50px;
        height: 60px;
        text-align: center;
        font-size: 1.5rem;
        font-weight: 700;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        background-color: #fff;
        transition: all 0.2s;
    }
    .otp-input:focus {
        border-color: #1a1a1a;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
        outline: none;
    }
    @media (max-width: 480px) {
        .otp-input {
            width: 40px;
            height: 50px;
            font-size: 1.2rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.otp-input');
        const form = document.getElementById('otp-form');
        const hiddenInput = document.getElementById('verification_code');
        
        inputs.forEach((input, index) => {
            // Focus on first input
            if (index === 0) input.focus();
            
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateCode();
            });
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            // Allow only numbers
            input.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        });

        function updateCode() {
            let code = '';
            inputs.forEach(input => {
                code += input.value;
            });
            hiddenInput.value = code;
        }

        form.addEventListener('submit', function(e) {
            updateCode();
            if (hiddenInput.value.length !== 6) {
                e.preventDefault();
                alert('Silakan masukkan 6 digit kode verifikasi.');
            }
        });
    });

    function togglePhoneForm() {
        const form = document.getElementById('update-phone-form');
        form.classList.toggle('d-none');
    }
</script>
@endpush
