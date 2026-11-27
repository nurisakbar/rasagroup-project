@extends('layouts.shop')

@php
    $isEdit = $account->exists;
@endphp

@section('title', $isEdit ? 'Ubah Rekening' : 'Tambah Rekening')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('distributor.profile') }}">Profil Distributor</a>
            <span></span> {{ $isEdit ? 'Ubah Rekening' : 'Tambah Rekening' }}
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
                <div class="card border-0 shadow-sm border-radius-20 overflow-hidden mb-40" style="background-color: #ffffff;">
                    <div class="card-header bg-white border-bottom p-30">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">{{ $isEdit ? 'Ubah Rekening' : 'Tambah Rekening' }}</h3>
                            <a href="{{ route('distributor.profile') }}" class="font-sm"><i class="fi-rs-arrow-left mr-5"></i>Kembali</a>
                        </div>
                        <p class="text-muted font-sm mb-0 mt-10">Rekening aktif tampil saat subdist dan outlet milik Anda membayar dengan transfer ke rekening distributor.</p>
                    </div>
                    <div class="card-body p-30">
                        @if ($errors->any())
                            <div class="alert alert-danger border-radius-12 border-0 mb-25 font-sm" style="background-color: #fff5f5; color: #c53030;">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ $isEdit ? route('distributor.bank-accounts.update', $account) : route('distributor.bank-accounts.store') }}" method="POST" autocomplete="off">
                            @csrf
                            @if($isEdit)
                                @method('PUT')
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label font-sm fw-bold" for="bank_name">Nama Bank <span class="text-danger">*</span></label>
                                    <input type="text" id="bank_name" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name', $account->bank_name) }}" maxlength="100" placeholder="Contoh: BCA" list="bank-options" required>
                                    <datalist id="bank-options">
                                        @foreach(['BCA', 'BRI', 'BNI', 'Mandiri', 'BSI', 'CIMB Niaga', 'Permata', 'Danamon', 'BTN', 'OCBC'] as $bank)
                                            <option value="{{ $bank }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-sm fw-bold" for="account_number">Nomor Rekening <span class="text-danger">*</span></label>
                                    <input type="text" id="account_number" name="account_number" class="form-control @error('account_number') is-invalid @enderror" value="{{ old('account_number', $account->account_number) }}" maxlength="40" inputmode="numeric" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label font-sm fw-bold" for="account_name">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                                    <input type="text" id="account_name" name="account_name" class="form-control @error('account_name') is-invalid @enderror" value="{{ old('account_name', $account->account_name) }}" maxlength="150" placeholder="Sesuai buku tabungan" required>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $account->is_active))>
                                        <label class="form-check-label font-sm" for="is_active">Tampilkan rekening ini saat pembayaran</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-30 d-flex gap-2">
                                <button type="submit" class="btn px-30" style="background-color: #6A1B1B; color: #fff;">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}</button>
                                <a href="{{ route('distributor.profile') }}" class="btn btn-outline-secondary px-30">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
