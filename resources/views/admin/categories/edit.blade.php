@extends('layouts.admin')

@section('title', 'Edit Kategori')
@section('page-title', 'Edit Kategori')
@section('page-description', 'Edit data kategori')

@section('breadcrumb')
    <li><a href="{{ route('admin.categories.index') }}">Kategori</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-folder"></i> Data Kategori</h3>
                    </div>
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
                            <textarea class="form-control" id="description" name="description" rows="5" placeholder="Tuliskan deskripsi singkat kategori...">{{ old('description', $category->description) }}</textarea>
                            @error('description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}> <strong>Aktif</strong> (Menampilkan kategori di website)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary pull-right">
                            <i class="fa fa-save"></i> Perbarui Kategori
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <!-- Gambar Kategori -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-image"></i> Gambar Kategori</h3>
                    </div>
                    <div class="box-body text-center">
                        <div class="form-group @error('image') has-error @enderror">
                            <div class="image-preview-container" style="margin-bottom: 15px; border: 2px dashed #ddd; border-radius: 5px; padding: 10px; background: #fafafa; min-height: 150px; display: flex; align-items: center; justify-content: center; position: relative;">
                                @if($category->image)
                                    <img id="image-preview" src="{{ asset('storage/' . $category->image) }}" style="max-width: 100%; max-height: 200px; border-radius: 5px;">
                                @else
                                    <img id="image-preview" src="{{ asset('adminlte/img/default-50x50.gif') }}" style="max-width: 100%; max-height: 200px; display: none;">
                                @endif
                                
                                <div id="image-placeholder" style="{{ $category->image ? 'display: none;' : '' }}">
                                    <i class="fa fa-cloud-upload fa-4x text-muted"></i>
                                    <p class="text-muted">Klik pilih gambar</p>
                                </div>
                            </div>
                            <input type="file" id="image" name="image" accept="image/*" class="form-control" style="display: none;">
                            <button type="button" class="btn btn-warning btn-block" onclick="document.getElementById('image').click();">
                                <i class="fa fa-folder-open"></i> {{ $category->image ? 'Ganti Gambar' : 'Pilih Gambar' }}
                            </button>
                            <p class="help-block">Maksimal 2MB (JPG, PNG)</p>
                            @error('image')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Statistik</h3>
                    </div>
                    <div class="box-body">
                        <dl>
                            <dt>ID Kategori</dt>
                            <dd><small style="word-break: break-all;">{{ $category->id }}</small></dd>
                            <dt>Jumlah Produk</dt>
                            <dd><span class="label label-primary">{{ $category->products()->count() }} Produk</span></dd>
                            <dt>Dibuat</dt>
                            <dd>{{ $category->created_at->format('d M Y, H:i') }}</dd>
                            <dt>Terakhir Update</dt>
                            <dd>{{ $category->updated_at->format('d M Y, H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Image Preview logic
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

