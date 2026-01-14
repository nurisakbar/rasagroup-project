@extends('layouts.admin')

@section('title', 'Tambah Kategori')
@section('page-title', 'Tambah Kategori')
@section('page-description', 'Tambah data kategori baru')

@section('breadcrumb')
    <li><a href="{{ route('admin.categories.index') }}">Kategori</a></li>
    <li class="active">Tambah</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Tambah Kategori</h3>
                </div>
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="form-group @error('name') has-error @enderror">
                            <label for="name">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('slug') has-error @enderror">
                            <label for="slug">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug') }}" placeholder="Otomatis dari nama">
                            @error('slug')
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

                        <div class="form-group @error('icon') has-error @enderror">
                            <label for="icon">Icon (Font Awesome)</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-fw" id="icon-preview"></i></span>
                                <input type="text" class="form-control" id="icon" name="icon" value="{{ old('icon') }}" placeholder="fa-leaf">
                            </div>
                            <p class="help-block">Contoh: fa-leaf, fa-coffee, fa-tint. <a href="https://fontawesome.com/v4/icons/" target="_blank">Lihat daftar icon</a></p>
                            @error('icon')
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
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Icon Populer</h3>
                </div>
                <div class="box-body">
                    <p>Klik untuk menggunakan:</p>
                    <div class="icon-picker">
                        @foreach(['fa-leaf', 'fa-coffee', 'fa-tint', 'fa-cutlery', 'fa-glass', 'fa-beer', 'fa-lemon-o', 'fa-apple', 'fa-envira', 'fa-pagelines', 'fa-tree', 'fa-cube', 'fa-cubes', 'fa-shopping-basket', 'fa-shopping-bag'] as $icon)
                            <button type="button" class="btn btn-default btn-sm icon-btn" data-icon="{{ $icon }}" style="margin: 2px;">
                                <i class="fa {{ $icon }}"></i>
                            </button>
                        @endforeach
                    </div>
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

    $('.icon-btn').click(function() {
        var icon = $(this).data('icon');
        $('#icon').val(icon).trigger('input');
    });
});
</script>
@endpush

