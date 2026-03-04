@extends('layouts.warehouse')

@section('title', 'Pengaturan Hub')
@section('page-title', 'Pengaturan Hub')
@section('page-description', 'Perbarui informasi dasar Hub/Warehouse Anda')

@section('breadcrumb')
    <li><a href="{{ route('warehouse.dashboard') }}">Dashboard</a></li>
    <li class="active">Pengaturan Hub</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-building"></i> Informasi Hub</h3>
            </div>
            
            <form action="{{ route('warehouse.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="box-body">
                    <div class="form-group @error('hub_name') has-error @enderror">
                        <label for="hub_name">Nama Hub <span class="text-danger">*</span></label>
                        <input type="text" name="hub_name" id="hub_name" class="form-control" 
                               value="{{ old('hub_name', $warehouse->name) }}" required>
                        @error('hub_name')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group @error('phone') has-error @enderror">
                        <label for="phone">Nomor Telepon Hub</label>
                        <input type="text" name="phone" id="phone" class="form-control" 
                               value="{{ old('phone', $warehouse->phone) }}" placeholder="Contoh: 08123456789">
                        @error('phone')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group @error('address') has-error @enderror">
                        <label for="address">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea name="address" id="address" class="form-control" rows="4" required>{{ old('address', $warehouse->address) }}</textarea>
                        @error('address')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group @error('description') has-error @enderror">
                        <label for="description">Deskripsi / Jam Operasional</label>
                        <textarea name="description" id="description" class="form-control" rows="3" 
                                  placeholder="Contoh: Buka Senin-Jumat 08:00 - 17:00">{{ old('description', $warehouse->description) }}</textarea>
                        @error('description')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('warehouse.dashboard') }}" class="btn btn-default">Batal</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Catatan</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">
                    Informasi Hub ini akan ditampilkan kepada pembeli saat mereka memilih Hub terdekat atau di halaman profil Hub.
                </p>
                <p class="text-muted">
                    Pastikan alamat dan nomor telepon yang dimasukkan sudah benar untuk memudahkan proses pengiriman dan komunikasi dengan kurir/pembeli.
                </p>
                <hr>
                <p><strong>Lokasi Administratif:</strong><br>
                {{ $warehouse->full_location }}</p>
                <p><small class="text-danger">* Untuk perubahan Provinsi/Kabupaten, silakan hubungi Admin Pusat.</small></p>
            </div>
        </div>
    </div>
</div>
@endsection
