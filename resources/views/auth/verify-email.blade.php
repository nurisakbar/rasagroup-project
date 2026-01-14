@extends('layouts.shop')

@section('title', 'Verifikasi Email')

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
    
    .btn-submit {
        background: var(--gradient-primary);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 14px 30px;
        font-size: 1.1rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(231, 76, 60, 0.4);
        color: white;
    }
    
    .btn-logout {
        background: transparent;
        border: 2px solid #dc3545;
        color: #dc3545;
        border-radius: 12px;
        padding: 14px 30px;
        font-size: 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-logout:hover {
        background: #dc3545;
        color: white;
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
    
    .alert-auth {
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 20px;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    @media (max-width: 576px) {
        .action-buttons {
            flex-direction: column;
        }
        
        .action-buttons form {
            width: 100%;
        }
        
        .btn-submit, .btn-logout {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="bi bi-envelope-check-fill"></i>
                <h1>Verifikasi Email</h1>
                <p>Satu langkah lagi untuk mengaktifkan akun Anda</p>
            </div>
            
            <div class="auth-body">
                <div class="info-text">
                    <i class="bi bi-info-circle me-2"></i>
                    Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi alamat email Anda dengan mengklik link yang baru saja kami kirim. 
                    Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirim ulang.
                </div>
                
                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success alert-auth">
                        <i class="bi bi-check-circle me-2"></i>
                        Link verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.
                    </div>
                @endif

                <div class="action-buttons">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-submit">
                            <i class="bi bi-envelope-arrow-up me-2"></i> Kirim Ulang Email Verifikasi
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-logout">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
