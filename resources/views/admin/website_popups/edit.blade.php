@extends('layouts.admin')

@section('title', 'Edit Pop Up')
@section('page-title', 'Edit Pop Up Website')
@section('page-description', 'Perbarui data pop up promosi')

@section('breadcrumb')
    <li><a href="{{ route('admin.website-popups.index') }}">Pop Up Website</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
<form action="{{ route('admin.website-popups.update', $popup) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Detail Pop Up</h3>
                </div>
                <div class="box-body">
                    <div class="form-group @error('name') has-error @enderror">
                        <label for="name">Nama Pop Up <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $popup->name) }}" placeholder="Contoh: Promo Ramadhan" required>
                        @error('name')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group @error('url') has-error @enderror">
                        <label for="url">URL Tujuan (Opsional)</label>
                        <input type="url" class="form-control" id="url" name="url" value="{{ old('url', $popup->url) }}" placeholder="https://example.com/promo">
                        <p class="help-block">Tautan yang akan terbuka saat pop up diklik.</p>
                        @error('url')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $popup->is_active) ? 'checked' : '' }}> <strong>Aktif</strong> (Menampilkan pop up di website)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="{{ route('admin.website-popups.index') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary pull-right">
                        <i class="fa fa-save"></i> Perbarui Pop Up
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-image"></i> Gambar Pop Up</h3>
                </div>
                <div class="box-body text-center">
                    <div class="form-group @error('image') has-error @enderror">
                        <div class="image-preview-container" style="margin-bottom: 15px; border: 2px dashed #ddd; border-radius: 5px; padding: 10px; background: #fafafa; min-height: 200px; display: flex; align-items: center; justify-content: center;">
                            <img id="image-preview" src="{{ asset('storage/' . $popup->image) }}" style="max-width: 100%; max-height: 300px;">
                            <div id="image-placeholder" style="display: none;">
                                <i class="fa fa-cloud-upload fa-4x text-muted"></i>
                                <p class="text-muted">Pilih gambar promosi</p>
                            </div>
                        </div>
                        <input type="file" id="image" name="image" accept="image/*" class="form-control" style="display: none;">
                        <button type="button" class="btn btn-warning btn-block" onclick="document.getElementById('image').click();">
                            <i class="fa fa-folder-open"></i> Ganti Gambar
                        </button>
                        <p class="help-block">Rekomendasi ukuran: 600x800px atau 800x400px. Maks: 2MB.</p>
                        @error('image')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>


        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#image').change(function() {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(event) {
                $('#image-preview').attr('src', event.target.result).show();
                $('#image-placeholder').hide();
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush
