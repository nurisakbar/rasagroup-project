@extends('layouts.shop')

@section('title', 'Daftar Akun')

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
        max-width: 550px;
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
    
    .btn-register {
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
    
    .btn-register:hover {
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
    
    .invalid-feedback {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 5px;
    }
    
    .is-invalid {
        border-color: #dc3545 !important;
    }
    
    .benefits-list {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 25px;
    }
    
    .benefits-list h6 {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 15px;
    }
    
    .benefits-list ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .benefits-list li {
        padding: 8px 0;
        color: #6c757d;
    }
    
    .benefits-list li i {
        color: var(--primary-color);
        margin-right: 10px;
    }
    
    .form-text {
        color: #6c757d;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="bi bi-person-plus-fill"></i>
                <h1>Buat Akun Baru</h1>
                <p>Daftar sekarang dan nikmati kemudahan berbelanja</p>
            </div>
            
            <div class="auth-body">
                <div class="benefits-list">
                    <h6><i class="bi bi-star-fill text-warning me-2"></i>Keuntungan Menjadi Member:</h6>
                    <ul>
                        <li><i class="bi bi-check-circle-fill"></i> Lacak pesanan dengan mudah</li>
                        <li><i class="bi bi-check-circle-fill"></i> Akses riwayat pembelian</li>
                        <li><i class="bi bi-check-circle-fill"></i> Promo eksklusif untuk member</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="form-label">
                            <i class="bi bi-person me-1"></i> Nama Lengkap
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="Masukkan nama lengkap"
                                   required 
                                   autofocus 
                                   autocomplete="name">
                        </div>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

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
                                   placeholder="Masukkan email aktif"
                                   required 
                                   autocomplete="username">
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="mb-4">
                        <label for="phone" class="form-label">
                            <i class="bi bi-telephone me-1"></i> No. Telepon <span class="text-muted">(Opsional)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   placeholder="Contoh: 08123456789"
                                   autocomplete="tel">
                        </div>
                        @error('phone')
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
                                   placeholder="Minimal 8 karakter"
                                   required 
                                   autocomplete="new-password">
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text">Password minimal 8 karakter</small>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">
                            <i class="bi bi-lock-fill me-1"></i> Konfirmasi Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Ulangi password"
                                   required 
                                   autocomplete="new-password">
                        </div>
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-register">
                        <i class="bi bi-person-plus me-2"></i> Daftar Sekarang
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p class="mb-0">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
