@extends('layouts.shop')

@php($isEdit = $salesperson->exists)

@section('title', $isEdit ? 'Ubah Sales' : 'Tambah Sales')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('buyer.sales.index') }}">Data Sales</a>
            <span></span> {{ $isEdit ? 'Ubah' : 'Tambah' }}
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
                            <h3 class="mb-0" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">{{ $isEdit ? 'Ubah Data Sales' : 'Tambah Sales' }}</h3>
                            <a href="{{ route('buyer.sales.index') }}" class="font-sm"><i class="fi-rs-arrow-left mr-5"></i>Kembali</a>
                        </div>
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

                        <form autocomplete="off" action="{{ $isEdit ? route('buyer.sales.update', $salesperson) : route('buyer.sales.store') }}" method="POST">
                            @csrf
                            @if($isEdit)
                                @method('PUT')
                            @endif

                            <p class="font-sm mb-20">
                                Kode Sales:
                                @if($isEdit && $salesperson->sales_code)
                                    <strong style="color: #6A1B1B;">{{ $salesperson->sales_code }}</strong>
                                @else
                                    <span class="text-muted">dibuat otomatis saat disimpan (format MCR + nomor urut)</span>
                                @endif
                            </p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label font-sm fw-bold" for="sales_name">Nama Sales <span class="text-danger">*</span></label>
                                    <input type="text" id="sales_name" name="sales_name" class="form-control @error('sales_name') is-invalid @enderror" value="{{ old('sales_name', $salesperson->name) }}" maxlength="150" autocomplete="off" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-sm fw-bold" for="sales_phone">No. HP <span class="text-danger">*</span></label>
                                    <input type="text" id="sales_phone" name="sales_phone" class="form-control @error('sales_phone') is-invalid @enderror" value="{{ old('sales_phone', $salesperson->phone) }}" maxlength="20" placeholder="08xxxxxxxxxx" inputmode="tel" autocomplete="off" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-sm fw-bold" for="sales_email">Email</label>
                                    <input type="email" id="sales_email" name="sales_email" class="form-control @error('sales_email') is-invalid @enderror" value="{{ old('sales_email', $salesperson->exists && ! $salesperson->hasInternalEmail() ? $salesperson->email : '') }}" maxlength="255" placeholder="nama@contoh.com" autocomplete="off">
                                </div>
                            </div>

                            <div class="mt-30 d-flex gap-2">
                                <button type="submit" class="btn px-30" style="background-color: #6A1B1B; color: #fff;">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}</button>
                                <a href="{{ route('buyer.sales.index') }}" class="btn btn-outline-secondary px-30">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
