@extends('layouts.warehouse')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-description', 'Kelola informasi akun dan keamanan kata sandi Anda')

@section('breadcrumb')
    <li><a href="{{ route('warehouse.dashboard') }}">Dashboard</a></li>
    <li class="active">Profil Saya</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-user"></i> Informasi Akun & Keamanan (Ganti Password)</h3>
            </div>
            
            <form action="{{ route('warehouse.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group @error('name') has-error @enderror">
                                <label for="name">Nama Pengelola <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" 
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group @error('phone') has-error @enderror">
                                <label for="phone">Nomor Telepon Pribadi / WA</label>
                                <input type="text" name="phone" id="phone" class="form-control" 
                                       value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 08123456789">
                                @error('phone')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group @error('email') has-error @enderror">
                        <label for="email">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" 
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <hr>
                    <h4 class="text-muted" style="margin-bottom: 15px;"><i class="fa fa-lock"></i> Ganti Password <small>(Kosongkan jika tidak ingin mengganti password)</small></h4>

                    <div class="form-group @error('current_password') has-error @enderror">
                        <label for="current_password">Password Saat Ini</label>
                        <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Masukkan password lama Anda">
                        @error('current_password')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group @error('new_password') has-error @enderror">
                                <label for="new_password">Password Baru</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Minimal 8 karakter">
                                @error('new_password')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" placeholder="Ulangi password baru">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan Perubahan Profil
                    </button>
                    <a href="{{ route('warehouse.dashboard') }}" class="btn btn-default">Batal</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Catatan Keamanan</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">
                    Pastikan alamat email dan nomor telepon yang dimasukkan aktif dan dapat dihubungi untuk keperluan notifikasi atau pemulihan akun.
                </p>
                <p class="text-muted">
                    Jika Anda mengubah password, pastikan untuk menggunakan kombinasi huruf dan angka minimal 8 karakter demi keamanan akun Anda.
                </p>
                <hr>
                <p><strong>Hub Terhubung:</strong><br>
                {{ $warehouse->name ?? '-' }} ({{ $warehouse->full_location ?? '-' }})</p>
                <p><small class="text-muted">Untuk perubahan informasi alamat fisik atau nama Hub, silakan hubungi Admin Pusat.</small></p>
            </div>
        </div>
    </div>
</div>
@endsection
