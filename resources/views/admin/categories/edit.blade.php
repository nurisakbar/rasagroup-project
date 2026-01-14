@extends('layouts.admin')

@section('title', 'Edit Kategori')
@section('page-title', 'Edit Kategori')
@section('page-description', 'Edit data kategori')

@section('breadcrumb')
    <li><a href="{{ route('admin.categories.index') }}">Kategori</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Edit Kategori</h3>
                </div>
                <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        <div class="form-group @error('name') has-error @enderror">
                            <label for="name">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $category->name) }}" required>
                            @error('name')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('slug') has-error @enderror">
                            <label for="slug">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $category->slug) }}">
                            @error('slug')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
                            @error('description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('icon') has-error @enderror">
                            <label for="icon">Icon (Font Awesome)</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-fw {{ $category->icon }}" id="icon-preview"></i></span>
                                <input type="text" class="form-control" id="icon" name="icon" value="{{ old('icon', $category->icon) }}" placeholder="fa-leaf">
                            </div>
                            <p class="help-block">Contoh: fa-leaf, fa-coffee, fa-tint</p>
                            @error('icon')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}> Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
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
                        <dd><small>{{ $category->id }}</small></dd>
                        <dt>Jumlah Produk</dt>
                        <dd>{{ $category->products()->count() }} produk</dd>
                        <dt>Dibuat</dt>
                        <dd>{{ $category->created_at->format('d M Y H:i') }}</dd>
                        <dt>Diupdate</dt>
                        <dd>{{ $category->updated_at->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#icon').on('input', function() {
        var icon = $(this).val();
        $('#icon-preview').attr('class', 'fa fa-fw ' + icon);
    });
});
</script>
@endpush

