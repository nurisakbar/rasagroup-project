@extends('layouts.shop')

@section('title', 'Login')

@push('styles')
<style>
    .auth-section {
        min-height: calc(100vh - 400px);
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 60px 0;
    }
    
    .auth-card {
        background: white;
        border-radius: 25px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        overflow: hidden;
        max-width: 500px;
        margin: 0 auto;
    }
    
    .auth-header {
        background: var(--gradient-primary);
        padding: 40px 30px;
        text-align: center;
        color: white;
    }
    
    .auth-header i {
        font-size: 4rem;
        margin-bottom: 15px;
        display: block;
    }
    
    .auth-header h1 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .auth-header p {
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    .auth-body {
        padding: 40px;
    }
    
    .form-label {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 8px;
    }
    
    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 14px 18px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.1);
    }
    
    .input-group-text {
        background: transparent;
        border: 2px solid #e9ecef;
        border-right: none;
        border-radius: 12px 0 0 12px;
        color: #6c757d;
    }
    
    .input-group .form-control {
        border-left: none;
        border-radius: 0 12px 12px 0;
    }
    
    .input-group:focus-within .input-group-text {
        border-color: var(--primary-color);
    }
    
    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-login {
        background: var(--gradient-primary);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 14px 30px;
        font-size: 1.1rem;
        font-weight: 600;
        width: 100%;
        transition: all 0.3s ease;
    }
    
    .btn-login:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(231, 76, 60, 0.4);
        color: white;
    }
    
    .auth-footer {
        text-align: center;
        padding-top: 25px;
        border-top: 1px solid #e9ecef;
        margin-top: 25px;
    }
    
    .auth-footer a {
        color: var(--primary-color);
        font-weight: 600;
        text-decoration: none;
    }
    
    .auth-footer a:hover {
        text-decoration: underline;
    }
    
    .forgot-password {
        color: var(--primary-color);
        text-decoration: none;
        font-size: 0.9rem;
    }
    
    .forgot-password:hover {
        text-decoration: underline;
    }
    
    .alert-auth {
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 20px;
    }
    
    .invalid-feedback {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 5px;
    }
    
    .is-invalid {
        border-color: #dc3545 !important;
    }
</style>
@endpush

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="bi bi-person-circle"></i>
                <h1>Selamat Datang Kembali</h1>
                <p>Silakan masuk ke akun Anda untuk melanjutkan belanja</p>
            </div>
            
            <div class="auth-body">
                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success alert-auth">
                        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-4">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-1"></i> Email
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="Masukkan email Anda"
                                   required 
                                   autofocus 
                                   autocomplete="username">
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-1"></i> Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Masukkan password"
                                   required 
                                   autocomplete="current-password">
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                            <label class="form-check-label" for="remember_me">
                                Ingat saya
                            </label>
                        </div>
                        
                        @if (Route::has('password.request'))
                            <a class="forgot-password" href="{{ route('password.request') }}">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p class="mb-0">Belum punya akun? <a href="{{ route('register') }}">Daftar Sekarang</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
