@extends('layouts.shop')

@section('title', 'Profil')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Akun Saya
            <span></span> Profil
        </div>
    </div>
</div>

<div class="page-content pt-50 pb-80" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-lg-8">
                <div class="tab-content account dashboard-content">
                    <div class="tab-pane fade show active" role="tabpanel">
                        <div class="card border-0 shadow-sm border-radius-20 overflow-hidden" style="background-color: #ffffff;">
                            <div class="card-header bg-white border-bottom-0 p-30 pb-0">
                                <h3 class="mb-0" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Detail Akun</h3>
                            </div>
                            <div class="card-body p-30 pt-10">
                                @if(session('success'))
                                    <div class="alert alert-success border-radius-12 border-0 mb-30" style="background-color: #f0fff4; color: #2f855a;">
                                        <i class="fi-rs-check mr-5"></i> {{ session('success') }}
                                    </div>
                                @endif

                                <div class="mb-40">
                                    <div class="p-25 border-radius-15" style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;">
                                        <div class="d-flex justify-content-between align-items-center mb-20">
                                            <h5 class="mb-0" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;">Informasi Profil</h5>
                                            <a href="{{ route('buyer.profile.edit') }}" class="btn btn-sm rounded-pill buyer-btn-maroon-outline">
                                                <i class="fi-rs-edit mr-5"></i> Edit Profil
                                            </a>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="font-xs text-muted mb-1 d-block">Nama Lengkap</label>
                                                <p class="font-md fw-bold mb-0 text-maroon">{{ Auth::user()->name }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="font-xs text-muted mb-1 d-block">Alamat Email</label>
                                                <p class="font-md fw-bold mb-0" style="color: #253D4E;">{{ Auth::user()->email }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="font-xs text-muted mb-1 d-block">Nomor Telepon</label>
                                                <p class="font-md fw-bold mb-0" style="color: #253D4E;">{{ Auth::user()->phone ?? '-' }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="font-xs text-muted mb-1 d-block">Tanggal Lahir</label>
                                                <p class="font-md fw-bold mb-0" style="color: #253D4E;">
                                                    {{ Auth::user()->date_of_birth ? Auth::user()->date_of_birth->format('d M Y') : '-' }}
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="font-xs text-muted mb-1 d-block">Pekerjaan</label>
                                                <p class="font-md fw-bold mb-0" style="color: #253D4E;">{{ Auth::user()->occupation ?: '-' }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="font-xs text-muted mb-1 d-block">Tipe Akun</label>
                                                <p class="mb-0"><span class="badge rounded-pill px-3 py-2" style="background-color: #6A1B1B; color: #fff; font-weight: 600;">{{ ucfirst(Auth::user()->role) }}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(Auth::user()->isDistributor())
                                    @php
                                        Auth::user()->loadMissing(['warehouse.province', 'warehouse.regency', 'warehouse.district']);
                                        $hub = Auth::user()->warehouse;
                                    @endphp
                                    @if($hub)
                                        <div class="mb-40">
                                            <div class="p-25 border-radius-15" style="background-color: #fffaf8; border: 1.5px solid #edd6d0;">
                                                <h5 class="mb-20" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;">
                                                    <i class="fi-rs-building mr-5"></i> Hub distributor
                                                </h5>
                                                <p class="font-xs text-muted mb-20">Lokasi hub dari admin (untuk stok &amp; pesanan masuk). Untuk alamat pengiriman belanja, kelola di menu <a href="{{ route('buyer.addresses.index') }}" class="text-brand fw-bold">Alamat</a>.</p>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="font-xs text-muted mb-1 d-block">Nama hub</label>
                                                        <p class="font-md fw-bold mb-0 text-maroon">{{ $hub->name }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="font-xs text-muted mb-1 d-block">Wilayah</label>
                                                        <p class="font-md fw-bold mb-0" style="color: #253D4E;">{{ $hub->full_location ?: '—' }}</p>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="font-xs text-muted mb-1 d-block">Alamat hub</label>
                                                        <p class="font-md fw-bold mb-0" style="color: #253D4E;">{{ $hub->address ?: '—' }}</p>
                                                    </div>
                                                    @if($hub->postal_code)
                                                        <div class="col-md-6">
                                                            <label class="font-xs text-muted mb-1 d-block">Kode pos</label>
                                                            <p class="font-md fw-bold mb-0" style="color: #253D4E;">{{ $hub->postal_code }}</p>
                                                        </div>
                                                    @endif
                                                    @if($hub->phone)
                                                        <div class="col-md-6">
                                                            <label class="font-xs text-muted mb-1 d-block">Telepon hub</label>
                                                            <p class="font-md fw-bold mb-0" style="color: #253D4E;">{{ $hub->phone }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                <div class="mt-50 pt-30 border-top">
                                    <h4 class="mb-25" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Ubah Password</h4>
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
                                            <div class="col-md-6">
                                                <div class="form-group mb-0">
                                                    <label class="form-label-custom">Password Baru <span class="text-danger">*</span></label>
                                                    <input required class="form-control custom-input" name="password" type="password" placeholder="Minimal 8 karakter">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-0">
                                                    <label class="form-label-custom">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                                    <input required class="form-control custom-input" name="password_confirmation" type="password" placeholder="Ulangi password baru">
                                                </div>
                                            </div>
                                            <div class="col-md-12 mt-30">
                                                <button type="submit" class="btn w-100">
                                                    Simpan Password Baru
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-maroon { color: #6A1B1B !important; }

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
    .buyer-btn-maroon-outline {
        color: #6A1B1B !important;
        border: 2px solid #6A1B1B !important;
        background: transparent !important;
        font-weight: 600;
    }
    .buyer-btn-maroon-outline:hover {
        background: #6A1B1B !important;
        color: #fff !important;
    }
</style>

@endsection

@push('styles')
<style>
    .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    .required { color: #fd3d11; }
</style>
@endpush









