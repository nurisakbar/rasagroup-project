@extends('layouts.shop')

@section('title', 'Ajukan Penarikan Poin')
@section('page-title', 'Ajukan Penarikan Poin')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-wallet2"></i> Form Penarikan Poin</h5>
                </div>
                <div class="card-body">
                    <!-- Poin Info -->
                    <div class="alert alert-info">
                        <strong><i class="bi bi-star-fill"></i> Poin Tersedia:</strong>
                        <h4 class="mb-0">{{ number_format($user->points, 0, ',', '.') }} poin</h4>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('buyer.point-withdrawals.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="amount" class="form-label">Jumlah Poin yang Ditarik <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('amount') is-invalid @enderror" 
                                   id="amount" 
                                   name="amount" 
                                   value="{{ old('amount') }}" 
                                   min="1" 
                                   max="{{ $user->points }}"
                                   required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Maksimal: {{ number_format($user->points, 0, ',', '.') }} poin</small>
                        </div>

                        <div class="mb-3">
                            <label for="bank_name" class="form-label">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('bank_name') is-invalid @enderror" 
                                   id="bank_name" 
                                   name="bank_name" 
                                   value="{{ old('bank_name') }}" 
                                   required>
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="account_number" class="form-label">Nomor Rekening <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('account_number') is-invalid @enderror" 
                                   id="account_number" 
                                   name="account_number" 
                                   value="{{ old('account_number') }}" 
                                   required>
                            @error('account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="account_name" class="form-label">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('account_name') is-invalid @enderror" 
                                   id="account_name" 
                                   name="account_name" 
                                   value="{{ old('account_name') }}" 
                                   required>
                            @error('account_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i> <strong>Perhatian:</strong> Request penarikan poin akan diproses setelah disetujui oleh admin. Poin akan dikurangi dari akun Anda setelah admin menyelesaikan proses penarikan.
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('buyer.point-withdrawals.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Ajukan Penarikan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection







