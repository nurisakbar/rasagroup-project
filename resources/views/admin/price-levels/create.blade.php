@extends('layouts.admin')

@section('title', 'Tambah Level Harga')
@section('page-title', 'Tambah Level Harga')
@section('page-description', 'Tambah level harga baru untuk distributor')

@section('breadcrumb')
    <li><a href="{{ route('admin.price-levels.index') }}">Level Harga</a></li>
    <li class="active">Tambah</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Tambah Level Harga</h3>
                </div>
                <form action="{{ route('admin.price-levels.store') }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label for="name">Nama Level <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="Contoh: Level 1, Silver, Gold" 
                                   required>
                            @error('name')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Deskripsi level harga...">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="discount_percentage">Persentase Diskon (%) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('discount_percentage') is-invalid @enderror" 
                                   id="discount_percentage" 
                                   name="discount_percentage" 
                                   value="{{ old('discount_percentage') }}" 
                                   min="0" 
                                   max="100" 
                                   step="0.01"
                                   placeholder="0.00" 
                                   required>
                            @error('discount_percentage')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                            <span class="help-block">Masukkan persentase diskon dari harga normal. Contoh: 5 untuk diskon 5%, 10 untuk diskon 10%</span>
                        </div>

                        <div class="form-group">
                            <label for="order">Urutan</label>
                            <input type="number" 
                                   class="form-control @error('order') is-invalid @enderror" 
                                   id="order" 
                                   name="order" 
                                   value="{{ old('order', 0) }}" 
                                   min="0" 
                                   placeholder="0">
                            @error('order')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                            <span class="help-block">Urutan untuk sorting (semakin kecil semakin awal)</span>
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    Aktif
                                </label>
                            </div>
                            <span class="help-block">Centang untuk mengaktifkan level harga ini</span>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('admin.price-levels.index') }}" class="btn btn-default">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection







