@extends('layouts.shop')

@section('title', 'Profil')

@section('content')
<div class="container">
    <h2 class="my-4">Profil Saya</h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Informasi Profil</h5>
                    <a href="{{ route('buyer.profile.edit') }}" class="btn btn-sm btn-primary">Edit Profil</a>
                </div>
                <div class="card-body">
                    <p><strong>Nama:</strong> {{ Auth::user()->name }}</p>
                    <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                    <p><strong>Telepon:</strong> {{ Auth::user()->phone ?? '-' }}</p>
                    <p><strong>Role:</strong> {{ ucfirst(Auth::user()->role) }}</p>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Ubah Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('buyer.profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Lama</label>
                            <input type="password" name="current_password" id="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Ubah Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection









