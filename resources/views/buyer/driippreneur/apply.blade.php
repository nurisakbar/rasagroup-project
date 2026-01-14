@extends('layouts.shop')

@section('title', 'Daftar DRiiPPreneur')

@push('styles')
<style>
    .form-select-lg, .form-control-lg {
        font-size: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('buyer.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Daftar DRiiPPreneur</li>
                </ol>
            </nav>

            <!-- Info Banner -->
            <div class="card bg-info bg-opacity-25 border-info mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-star fs-1 text-info"></i>
                        </div>
                        <div class="ms-4">
                            <h5 class="card-title mb-1">Jadilah DRiiPPreneur Kami!</h5>
                            <p class="card-text mb-0">Dapatkan keuntungan lebih dengan menjadi mitra DRiiPPreneur. Kelola stock sendiri dan raih penghasilan lebih besar.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Form -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Formulir Pendaftaran DRiiPPreneur</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('buyer.driippreneur.apply') }}" method="POST">
                        @csrf

                        <!-- Informasi Pribadi (readonly) -->
                        <h6 class="text-muted mb-3"><i class="bi bi-person"></i> Informasi Akun</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">No. HP</label>
                                <input type="text" class="form-control" value="{{ $user->phone ?? '-' }}" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Dokumen Verifikasi -->
                        <h6 class="text-muted mb-3"><i class="bi bi-card-text"></i> Dokumen Verifikasi</h6>
                        
                        <div class="mb-3">
                            <label for="no_ktp" class="form-label">Nomor KTP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('no_ktp') is-invalid @enderror" 
                                   id="no_ktp" name="no_ktp" value="{{ old('no_ktp') }}" 
                                   placeholder="Masukkan 16 digit nomor KTP" maxlength="16" required>
                            <div class="form-text">Masukkan 16 digit nomor KTP Anda sesuai e-KTP.</div>
                            @error('no_ktp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="no_npwp" class="form-label">Nomor NPWP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('no_npwp') is-invalid @enderror" 
                                   id="no_npwp" name="no_npwp" value="{{ old('no_npwp') }}" 
                                   placeholder="Contoh: 12.345.678.9-012.345" maxlength="20" required>
                            <div class="form-text">Masukkan nomor NPWP Anda (wajib untuk DRiiPPreneur).</div>
                            @error('no_npwp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Catatan:</strong> Setelah pengajuan dikirim, tim kami akan memverifikasi data Anda dalam 1-3 hari kerja. 
                            Anda akan mendapatkan notifikasi setelah pengajuan diproses. Setelah disetujui, Anda tetap dapat berbelanja seperti biasa 
                            dan akan mendapatkan poin untuk setiap item yang dibeli.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send"></i> Kirim Pengajuan
                            </button>
                            <a href="{{ route('buyer.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Benefits -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-star"></i> Keuntungan Menjadi DRiiPPreneur</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Dapatkan poin untuk setiap item yang dibeli</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Poin dapat ditukar dengan berbagai reward menarik</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Berbelanja seperti biasa sebagai buyer</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Sistem poin yang mudah dan transparan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


