@extends('layouts.shop')

@section('title', 'Edit Profil')

@section('content')
<div class="page-content pt-50 pb-80" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm border-radius-20 overflow-hidden" style="background-color: #ffffff;">
                    <div class="card-header bg-white border-bottom p-30">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Edit Profil</h3>
                            <a href="{{ route('buyer.profile') }}" class="btn btn-sm btn-outline-secondary rounded-pill font-sm px-20">
                                <i class="fi-rs-arrow-left mr-5"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-30 p-md-40">
                        <form action="{{ route('buyer.profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-40">
                                <h5 class="mb-20" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;"><i class="fi-rs-user mr-10"></i> Informasi Dasar</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Nama Lengkap</label>
                                            <input type="text" name="name" id="name" class="form-control custom-input" value="{{ old('name', Auth::user()->name) }}" required placeholder="Contoh: Budi Santoso">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Email</label>
                                            <input type="email" name="email" id="email" class="form-control custom-input" value="{{ old('email', Auth::user()->email) }}" required placeholder="email@contoh.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Telepon</label>
                                            <input type="text" name="phone" id="phone" class="form-control custom-input" value="{{ old('phone', Auth::user()->phone) }}" placeholder="081234567890">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-40">
                                <h5 class="mb-20" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;"><i class="fi-rs-bank mr-10"></i> Informasi Rekening Bank</h5>
                                <p class="text-muted small mb-20">Data ini digunakan untuk pengajuan penarikan poin hasil afiliasi Anda.</p>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Nama Bank</label>
                                            <input type="text" name="bank_name" id="bank_name" class="form-control custom-input" value="{{ old('bank_name', Auth::user()->bank_name) }}" placeholder="Pilih Bank (BCA, Mandiri, BRI, dll)">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Nomor Rekening</label>
                                            <input type="text" name="bank_account_number" id="bank_account_number" class="form-control custom-input" value="{{ old('bank_account_number', Auth::user()->bank_account_number) }}" placeholder="Nomor Rekening Anda">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Atas Nama</label>
                                            <input type="text" name="bank_account_name" id="bank_account_name" class="form-control custom-input" value="{{ old('bank_account_name', Auth::user()->bank_account_name) }}" placeholder="Nama sesuai buku tabungan">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-30 pt-30 border-top">
                                <button type="submit" class="btn btn-maroon-lg w-100">
                                    Simpan Perubahan
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
    .form-label-custom { font-family: 'Fira Sans', sans-serif; font-weight: 600; color: #253D4E; margin-bottom: 8px; font-size: 14px; display: block; }
    .custom-input {
        background-color: #F8F9FA !important;
        border: 1.5px solid #ECECEC !important;
        border-radius: 12px !important;
        padding: 15px 20px !important;
        transition: all 0.3s ease;
    }
    .custom-input:focus {
        border-color: #6A1B1B !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(106, 27, 27, 0.05) !important;
    }

    .btn-maroon-lg {
        background-color: #6A1B1B !important;
        color: #fff !important;
        padding: 18px !important;
        border-radius: 15px !important;
        font-family: 'Fira Sans', sans-serif !important;
        font-weight: 700 !important;
        border: none !important;
        transition: all 0.3s;
    }
    .btn-maroon-lg:hover {
        background-color: #4D1313 !important;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(106, 27, 27, 0.2);
    }
</style>
@endsection









