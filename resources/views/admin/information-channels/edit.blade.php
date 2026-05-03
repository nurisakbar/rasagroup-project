@extends('layouts.admin')

@section('title', 'Edit Saluran Informasi')
@section('page-title', 'Edit Saluran Informasi')
@section('page-description', 'Perbarui detail saluran informasi.')

@section('breadcrumb')
    <li><a href="{{ route('admin.information-channels.index') }}">Saluran Informasi</a></li>
    <li class="active">Edit</li>
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
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Edit Saluran Informasi</h3>
                </div>
                <form action="{{ route('admin.information-channels.update', $channel) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        <div class="form-group @error('title') has-error @enderror">
                            <label for="title">Judul <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $channel->title) }}" required>
                            @error('title')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group @error('target_audience') has-error @enderror">
                                    <label for="target_audience">Target Audiens <span class="text-danger">*</span></label>
                                    <select class="form-control" name="target_audience" id="target_audience" required>
                                        <option value="all" {{ old('target_audience', $channel->target_audience) == 'all' ? 'selected' : '' }}>Semua Pengguna</option>
                                        <option value="distributor" {{ old('target_audience', $channel->target_audience) == 'distributor' ? 'selected' : '' }}>Khusus Distributor</option>
                                        <option value="customer" {{ old('target_audience', $channel->target_audience) == 'customer' ? 'selected' : '' }}>Khusus Customer (Non-Distributor)</option>
                                    </select>
                                    @error('target_audience')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group @error('start_date') has-error @enderror">
                                    <label for="start_date">Berlaku Dari</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $channel->start_date ? $channel->start_date->format('Y-m-d') : '') }}">
                                    @error('start_date')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group @error('end_date') has-error @enderror">
                                    <label for="end_date">Berlaku Sampai</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', $channel->end_date ? $channel->end_date->format('Y-m-d') : '') }}">
                                    @error('end_date')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control summernote" id="description" name="description" rows="5">{{ old('description', $channel->description) }}</textarea>
                            @error('description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        @if($channel->image_url)
                            <div class="form-group">
                                <label>Gambar saat ini</label>
                                <div>
                                    <img src="{{ $channel->image_url }}" alt="" class="img-thumbnail" style="max-height: 160px;">
                                </div>
                            </div>
                        @endif

                        <div class="form-group @error('image') has-error @enderror">
                            <label for="image">Ganti gambar sampul</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                            <p class="help-block"><small>Kosongkan jika tidak ingin mengganti. Maks. 2 MB.</small></p>
                            @error('image')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $channel->is_active) ? 'checked' : '' }}> Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Perbarui</button>
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
        });
    </script>
@endpush
