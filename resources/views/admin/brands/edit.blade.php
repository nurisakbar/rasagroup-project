@extends('layouts.admin')

@section('title', 'Edit Brand')
@section('page-title', 'Edit Brand')
@section('page-description', 'Edit data brand')

@section('breadcrumb')
    <li><a href="{{ route('admin.brands.index') }}">Brand</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Edit Brand</h3>
                </div>
                <form action="{{ route('admin.brands.update', $brand) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        <div class="form-group @error('name') has-error @enderror">
                            <label for="name">Nama Brand <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $brand->name) }}" required>
                            @error('name')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('slug') has-error @enderror">
                            <label for="slug">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $brand->slug) }}">
                            @error('slug')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $brand->description) }}</textarea>
                            @error('description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('logo') has-error @enderror">
                            <label for="logo">Logo</label>
                            @if($brand->logo)
                                <div style="margin-bottom: 10px;">
                                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" style="max-height: 60px;">
                                </div>
                            @endif
                            <input type="file" id="logo" name="logo" accept="image/*">
                            <p class="help-block">Kosongkan jika tidak ingin mengubah logo</p>
                            @error('logo')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $brand->is_active) ? 'checked' : '' }}> Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Info</h3>
                </div>
                <div class="box-body">
                    <dl>
                        <dt>ID</dt>
                        <dd><small>{{ $brand->id }}</small></dd>
                        <dt>Jumlah Produk</dt>
                        <dd>{{ $brand->products()->count() }} produk</dd>
                        <dt>Dibuat</dt>
                        <dd>{{ $brand->created_at->format('d M Y H:i') }}</dd>
                        <dt>Diupdate</dt>
                        <dd>{{ $brand->updated_at->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection

