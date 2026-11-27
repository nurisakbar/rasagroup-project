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

                        <form action="{{ $isEdit ? route('buyer.sales.update', $salesperson) : route('buyer.sales.store') }}" method="POST">
                            @csrf
                            @if($isEdit)
                                @method('PUT')
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label font-sm fw-bold" for="name">Nama Sales <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $salesperson->name) }}" maxlength="150" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-sm fw-bold" for="phone">No. HP <span class="text-danger">*</span></label>
                                    <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $salesperson->phone) }}" maxlength="30" placeholder="08xxxxxxxxxx" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-sm fw-bold" for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $salesperson->email) }}" maxlength="150">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-sm fw-bold" for="area">Area / Wilayah</label>
                                    <input type="text" id="area" name="area" class="form-control @error('area') is-invalid @enderror" value="{{ old('area', $salesperson->area) }}" maxlength="150" placeholder="Contoh: Bekasi Timur">
                                </div>
                                <div class="col-12">
                                    <label class="form-label font-sm fw-bold" for="notes">Catatan</label>
                                    <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" maxlength="1000">{{ old('notes', $salesperson->notes) }}</textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $salesperson->is_active))>
                                        <label class="form-check-label font-sm" for="is_active">Sales aktif</label>
                                    </div>
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
