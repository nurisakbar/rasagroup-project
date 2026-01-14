@extends('layouts.shop')

@section('title', 'Konfirmasi Password')

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
    
    .btn-submit {
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
    
    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(231, 76, 60, 0.4);
        color: white;
    }
    
    .invalid-feedback {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 5px;
    }
    
    .is-invalid {
        border-color: #dc3545 !important;
    }
    
    .info-text {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        color: #6c757d;
        line-height: 1.7;
    }
    
    .info-text i {
        color: var(--primary-color);
    }
</style>
@endpush

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="bi bi-shield-check"></i>
                <h1>Konfirmasi Password</h1>
                <p>Area ini memerlukan verifikasi keamanan tambahan</p>
            </div>
            
            <div class="auth-body">
                <div class="info-text">
                    <i class="bi bi-info-circle me-2"></i>
                    Ini adalah area aman dari aplikasi. Silakan konfirmasi password Anda sebelum melanjutkan.
                </div>

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

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
                                   placeholder="Masukkan password Anda"
                                   required 
                                   autocomplete="current-password">
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-submit">
                        <i class="bi bi-check-circle me-2"></i> Konfirmasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
