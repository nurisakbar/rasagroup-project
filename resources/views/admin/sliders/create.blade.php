@extends('layouts.admin')

@section('title', 'Tambah Slider')
@section('page-title', 'Tambah Slider')
@section('page-description', 'Tambah data slider baru')

@section('breadcrumb')
    <li><a href="{{ route('admin.sliders.index') }}">Slider</a></li>
    <li class="active">Tambah</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Tambah Slider</h3>
                </div>
                <form action="{{ route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="box-body">
                        <div class="form-group @error('title') has-error @enderror">
                            <label for="title">Judul</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}">
                            @error('title')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('link') has-error @enderror">
                            <label for="link">Link Url</label>
                            <input type="text" class="form-control" id="link" name="link" value="{{ old('link') }}" placeholder="https://example.com/promo">
                            @error('link')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('image') has-error @enderror">
                            <label for="image">Gambar Slider <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="image" name="image" required>
                            <p class="help-block">Format: jpg, png, jpeg. Maks: 2MB. Rekomendasi ukuran: 1920x600px</p>
                            @error('image')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('sort_order') has-error @enderror">
                            <label for="sort_order">Urutan</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                            @error('sort_order')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}> Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                        <a href="{{ route('admin.sliders.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
