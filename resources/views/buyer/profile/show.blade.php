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

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4">
                    @include('buyer.partials.sidebar')
                </div>
                <div class="col-lg-8">
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <h3 class="mb-0">Detail Akun</h3>
                                </div>
                                <div class="card-body p-4 pt-0">
                                    @if(session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="fi-rs-check mr-5"></i> {{ session('success') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif

                                    <div class="row mb-5">
                                        <div class="col-lg-12">
                                            <div class="bg-light p-4 border-radius-10">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h5 class="mb-0">Informasi Profil</h5>
                                                    <a href="{{ route('buyer.profile.edit') }}" class="btn btn-sm btn-outline-primary rounded font-sm">
                                                        <i class="fi-rs-edit mr-5"></i> Edit Profil
                                                    </a>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="font-sm text-muted mb-1">Nama Lengkap</label>
                                                        <p class="font-md fw-bold mb-0 text-brand">{{ Auth::user()->name }}</p>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="font-sm text-muted mb-1">Alamat Email</label>
                                                        <p class="font-md fw-bold mb-0">{{ Auth::user()->email }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="font-sm text-muted mb-1">Nomor Telepon</label>
                                                        <p class="font-md fw-bold mb-0">{{ Auth::user()->phone ?? '-' }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="font-sm text-muted mb-1">Tipe Akun</label>
                                                        <p class="font-md fw-bold mb-0"><span class="badge rounded-pill bg-info-light text-info">{{ ucfirst(Auth::user()->role) }}</span></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <h4 class="mb-4">Ubah Password</h4>
                                            <form action="{{ route('buyer.profile.password') }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="row">
                                                    <div class="form-group col-md-12 mb-3">
                                                        <label>Password Lama <span class="required">*</span></label>
                                                        <input required class="form-control" name="current_password" type="password">
                                                    </div>
                                                    <div class="form-group col-md-12 mb-3">
                                                        <label>Password Baru <span class="required">*</span></label>
                                                        <input required class="form-control" name="password" type="password">
                                                    </div>
                                                    <div class="form-group col-md-12 mb-4">
                                                        <label>Konfirmasi Password Baru <span class="required">*</span></label>
                                                        <input required class="form-control" name="password_confirmation" type="password">
                                                    </div>
                                                    <div class="col-md-12">
                                                        <button type="submit" class="btn btn-fill-out submit font-weight-bold" name="submit" value="Submit">Simpan Password Baru</button>
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
    </div>
</div>

@endsection

@push('styles')
<style>
    .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    .required { color: #fd3d11; }
</style>
@endpush









