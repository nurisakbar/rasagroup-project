@extends('layouts.admin')

@section('title', 'Tambah Saluran Informasi')
@section('page-title', 'Tambah Saluran Informasi')
@section('page-description', 'Buat saluran informasi baru untuk pengguna.')

@section('breadcrumb')
    <li><a href="{{ route('admin.information-channels.index') }}">Saluran Informasi</a></li>
    <li class="active">Tambah</li>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <style>
        .note-editable { background: #fff !important; }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Tambah Saluran Informasi</h3>
                </div>
                <form action="{{ route('admin.information-channels.store') }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="form-group @error('title') has-error @enderror">
                            <label for="title">Judul <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('slug') has-error @enderror">
                            <label for="slug">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug') }}" placeholder="Otomatis dari judul">
                            @error('slug')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control summernote" id="description" name="description" rows="5">{{ old('description') }}</textarea>
                            @error('description')
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
                        <a href="{{ route('admin.information-channels.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            $('#title').on('blur', function() {
                if ($('#slug').val() == '') {
                    var title = $(this).val();
                    var slug = title.toLowerCase()
                        .replace(/[^\w ]+/g, '')
                        .replace(/ +/g, '-');
                    $('#slug').val(slug);
                }
            });
        });
    </script>
@endpush
