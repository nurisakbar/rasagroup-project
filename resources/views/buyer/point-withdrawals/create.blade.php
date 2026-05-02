@extends('layouts.shop')

@section('title', 'Ajukan Penarikan Poin')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('buyer.point-withdrawals.index') }}">Penarikan Poin</a>
            <span></span> Ajukan Penarikan
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
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3 class="mb-0">Ajukan Penarikan Poin</h3>
                                        <a href="{{ route('buyer.point-withdrawals.index') }}" class="btn btn-sm btn-outline-secondary rounded font-sm">
                                            <i class="fi-rs-arrow-left mr-5"></i> Kembali
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body p-4 pt-0">
                                    <!-- Poin Info -->
                                    <div class="alert alert-info border-0 bg-info-light mb-4 text-center">
                                        <h6 class="text-brand mb-1">Poin Tersedia</h6>
                                        <h2 class="mb-0 text-brand">{{ number_format($user->points, 0, ',', '.') }} <span class="font-sm text-medium">Poin</span></h2>
                                    </div>

                                    @if(session('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="fi-rs-cross-circle mr-5"></i> {{ session('error') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif

                                    <form action="{{ route('buyer.point-withdrawals.store') }}" method="POST">
                                        @csrf

                                        <div class="form-group mb-3">
                                            <label>Jumlah Poin yang Ditarik <span class="required">*</span></label>
                                            <input type="number" 
                                                   class="form-control @error('amount') is-invalid @enderror" 
                                                   name="amount" 
                                                   value="{{ old('amount') }}" 
                                                   min="1" 
                                                   max="{{ $user->points }}"
                                                   placeholder="0"
                                                   required>
                                            @error('amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="font-xs text-muted">Maksimal penarikan: {{ number_format($user->points, 0, ',', '.') }} poin</small>
                                        </div>

                                        <div class="row bg-light p-3 rounded mb-4">
                                            <div class="col-md-12">
                                                <h6 class="mb-3">Informasi Rekening Tujuan:</h6>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <label class="text-muted font-xs">Nama Bank</label>
                                                <p class="font-weight-bold mb-0">{{ $user->bank_name }}</p>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="text-muted font-xs">Nomor Rekening</label>
                                                <p class="font-weight-bold mb-0">{{ $user->bank_account_number }}</p>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="text-muted font-xs">Atas Nama</label>
                                                <p class="font-weight-bold mb-0">{{ $user->bank_account_name }}</p>
                                            </div>
                                            <div class="col-md-12 mt-2">
                                                <small class="text-muted font-xs italic text-info">* Informasi rekening sesuai data di halaman <a href="{{ route('buyer.affiliate.index') }}" class="text-brand font-weight-bold">Program Afiliasi</a>. Ubah data rekening di sana jika perlu.</small>
                                            </div>
                                        </div>

                                        <div class="alert alert-warning border-0 bg-warning-light mb-4">
                                            <p class="font-sm mb-0">
                                                <i class="fi-rs-info mr-5"></i> 
                                                <strong>Penting:</strong> Permintaan penarikan akan diproses oleh tim admin. Poin Anda akan dikurangi secara otomatis setelah status penarikan diselesaikan.
                                            </p>
                                        </div>

                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-fill-out submit font-weight-bold">
                                                <i class="fi-rs-check-circle mr-5"></i> Ajukan Penarikan
                                            </button>
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

@endsection

@push('styles')
<style>
    .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
    .required { color: #fd3d11; }
</style>
@endpush







