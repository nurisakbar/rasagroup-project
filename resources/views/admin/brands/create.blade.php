@extends('layouts.admin')

@section('title', 'Tambah Brand')
@section('page-title', 'Tambah Brand')
@section('page-description', 'Tambah data brand baru')

@section('breadcrumb')
    <li><a href="{{ route('admin.brands.index') }}">Brand</a></li>
    <li class="active">Tambah</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Tambah Brand</h3>
                </div>
                <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="box-body">
                        <div class="form-group @error('name') has-error @enderror">
                            <label for="name">Nama Brand <span class="text-danger">*</span></label>
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
                        
                        <div class="form-group @error('categories') has-error @enderror">
                            <label for="categories">Kategori (Opsional)</label>
                            <div class="pull-right" style="margin-bottom: 5px;">
                                <button type="button" class="btn btn-xs btn-default btn-select-all" data-target="#categories">Pilih Semua</button>
                                <button type="button" class="btn btn-xs btn-default btn-deselect-all" data-target="#categories">Hapus Semua</button>
                            </div>
                            <select class="form-control select2" id="categories" name="categories[]" multiple="multiple" data-placeholder="Pilih Kategori">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (is_array(old('categories')) && in_array($cat->id, old('categories'))) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <p class="help-block">Pilih kategori yang dimiliki oleh Brand ini.</p>
                            @error('categories')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('logo') has-error @enderror">
                            <label for="logo">Logo</label>
                            <input type="file" id="logo" name="logo" accept="image/*">
                            <p class="help-block">Format: JPEG, PNG, JPG, GIF, SVG. Maksimal 1MB</p>
                            @error('logo')
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
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({
                width: '100%'
            });
        }

        $('.btn-select-all').click(function() {
            var target = $(this).data('target');
            var $select = $(target);
            $select.find('option').prop('selected', true);
            $select.trigger('change');
        });

        $('.btn-deselect-all').click(function() {
            var target = $(this).data('target');
            var $select = $(target);
            $select.find('option').prop('selected', false);
            $select.trigger('change');
        });
    });
</script>
@endpush

