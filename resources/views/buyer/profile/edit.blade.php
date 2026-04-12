@extends('layouts.shop')

@section('title', 'Edit Profil')

@section('content')
<div class="container">
    <h2 class="my-4">Edit Profil</h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('buyer.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', Auth::user()->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', Auth::user()->email) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Telepon</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', Auth::user()->phone) }}">
                        </div>
                        <hr>
                        <h5>Informasi Rekening Bank</h5>
                        <p class="text-muted small">Data ini digunakan untuk pengajuan penarikan poin.</p>
                        <div class="mb-3">
                            <label for="bank_name" class="form-label">Nama Bank</label>
                            <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ old('bank_name', Auth::user()->bank_name) }}" placeholder="Contoh: BCA, Mandiri, dll">
                        </div>
                        <div class="mb-3">
                            <label for="bank_account_number" class="form-label">Nomor Rekening</label>
                            <input type="text" name="bank_account_number" id="bank_account_number" class="form-control" value="{{ old('bank_account_number', Auth::user()->bank_account_number) }}">
                        </div>
                        <div class="mb-3">
                            <label for="bank_account_name" class="form-label">Atas Nama</label>
                            <input type="text" name="bank_account_name" id="bank_account_name" class="form-control" value="{{ old('bank_account_name', Auth::user()->bank_account_name) }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('buyer.profile') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection









