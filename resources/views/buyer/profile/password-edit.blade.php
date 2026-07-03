@extends('layouts.shop')

@section('title', 'Ubah Password')

@section('content')
<div class="page-content pt-50 pb-80 buyer-profile-edit" style="background-color: #F2EAE1; min-height: 80vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card border-0 shadow-sm border-radius-20 overflow-hidden" style="background-color: #ffffff;">
                    <div class="card-header bg-white border-bottom p-30">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0 fw-bold text-dark">Ubah Password</h3>
                            <a href="{{ route('buyer.account.menu') }}" class="btn btn-sm rounded-pill font-sm px-20 buyer-btn-maroon-outline d-lg-none">
                                <i class="fi-rs-arrow-left mr-5"></i> Kembali
                            </a>
                            <a href="{{ route('buyer.profile') }}" class="btn btn-sm rounded-pill font-sm px-20 buyer-btn-maroon-outline d-none d-lg-inline-flex">
                                <i class="fi-rs-arrow-left mr-5"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-30 p-md-40">
                        @if ($errors->any())
                            <div class="alert border-radius-12 border-0 mb-30" role="alert" style="background-color: #fff5f5; border: 1px solid #feb2b2 !important; color: #742a2a;">
                                <strong class="d-block mb-2">Periksa kembali data berikut:</strong>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session('warning'))
                            <div class="alert border-radius-12 border-0 mb-30" role="alert" style="background-color: #fff8e6; border: 1px solid #f6d365 !important; color: #744210;">
                                <i class="fi-rs-info mr-5"></i>{{ session('warning') }}
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success border-radius-12 border-0 mb-30" style="background-color: #f0fff4; color: #2f855a;">
                                <i class="fi-rs-check mr-5"></i> {{ session('success') }}
                            </div>
                        @endif
                        
                        <form action="{{ route('buyer.profile.password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label class="form-label-custom">Password Lama <span class="text-danger">*</span></label>
                                        <input required class="form-control custom-input" name="current_password" type="password" placeholder="Masukkan password saat ini">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label class="form-label-custom">Password Baru <span class="text-danger">*</span></label>
                                        <input required class="form-control custom-input" name="password" type="password" placeholder="Minimal 8 karakter">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label class="form-label-custom">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                        <input required class="form-control custom-input" name="password_confirmation" type="password" placeholder="Ketik ulang password baru">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-40 pt-30 border-top">
                                <button type="submit" class="btn w-100">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .buyer-profile-edit {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
    }
    .buyer-profile-edit i[class*="fi-rs"] {
        font-family: "uicons-regular-straight" !important;
    }
    .form-label-custom { font-weight: 600; color: #253D4E; margin-bottom: 8px; font-size: 14px; display: block; }
    .custom-input {
        background-color: #F8F9FA !important;
        border: 1px solid #E2E8F0 !important;
        color: #2D3748 !important;
        border-radius: 10px !important;
        padding: 12px 15px !important;
        font-size: 15px !important;
        transition: all 0.3s ease;
    }
    .custom-input:focus {
        background-color: #FFFFFF !important;
        border-color: #6A1B1B !important;
        box-shadow: 0 0 0 3px rgba(106, 27, 27, 0.1) !important;
    }
    .custom-input::placeholder { color: #A0AEC0 !important; }
</style>
@endsection
