@extends('layouts.admin')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')
@section('page-description', 'Edit master data produk yang sudah ada')

@section('breadcrumb')
    <li><a href="{{ route('admin.products.index') }}">Produk</a></li>
    <li class="active">Edit</li>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <style>
        .note-editable { background: #fff !important; }
    </style>
@endpush

@section('content')
    <form role="form" action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-9">
                <!-- Identitas Produk -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-tag"></i> Identitas Produk</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group @error('code') has-error @enderror">
                                    <label for="code">Kode Produk (SKU)</label>
                                    <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $product->code) }}" placeholder="SKU">
                                    @error('code')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('brand_id') has-error @enderror">
                                    <label for="brand_id">Brand</label>
                                    <select class="form-control select2" id="brand_id" name="brand_id" style="width: 100%;">
                                        <option value="">-- Pilih Brand --</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('category_id') has-error @enderror">
                                    <label for="category_id">Kategori</label>
                                    <select class="form-control select2" id="category_id" name="category_id" style="width: 100%;">
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('unit') has-error @enderror">
                                    <label for="unit">Satuan (UM)</label>
                                    <select class="form-control" id="unit" name="unit">
                                        <option value="">-- Pilih --</option>
                                        <option value="BT" {{ old('unit', $product->unit) === 'BT' ? 'selected' : '' }}>Bottle (BT)</option>
                                        <option value="PK" {{ old('unit', $product->unit) === 'PK' ? 'selected' : '' }}>Pack (PK)</option>
                                        <option value="BOX" {{ old('unit', $product->unit) === 'BOX' ? 'selected' : '' }}>BOX</option>
                                        <option value="PCS" {{ old('unit', $product->unit) === 'PCS' ? 'selected' : '' }}>Pieces (PCS)</option>
                                        <option value="KG" {{ old('unit', $product->unit) === 'KG' ? 'selected' : '' }}>Kilogram (KG)</option>
                                        <option value="L" {{ old('unit', $product->unit) === 'L' ? 'selected' : '' }}>Liter (L)</option>
                                    </select>
                                    @error('unit')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('name') has-error @enderror">
                            <label for="name">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" placeholder="Contoh: MB Cons 1L-Coconut Milk" required>
                            @error('name')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('commercial_name') has-error @enderror">
                            <label for="commercial_name">Nama Komersial</label>
                            <input type="text" class="form-control" id="commercial_name" name="commercial_name" value="{{ old('commercial_name', $product->commercial_name) }}" placeholder="Contoh: Coconut Milk (Nama yang tampil di website)">
                            @error('commercial_name')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group @error('size') has-error @enderror">
                                    <label for="size">Ukuran (Size)</label>
                                    <input type="text" class="form-control" id="size" name="size" value="{{ old('size', $product->size) }}" placeholder="Contoh: 1 L">
                                    @error('size')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('price') has-error @enderror">
                                    <label for="price">Harga Jual <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon">Rp</span>
                                        <input type="number" class="form-control" id="price" name="price" value="{{ old('price', (int)$product->price) }}" step="1" min="0" placeholder="0" required>
                                    </div>
                                    @error('price')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('weight') has-error @enderror">
                                    <label for="weight">Berat (gram) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="weight" name="weight" value="{{ old('weight', $product->weight) }}" step="1" min="1" required>
                                        <span class="input-group-addon">g</span>
                                    </div>
                                    @error('weight')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('status') has-error @enderror">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                                    </select>
                                    @error('status')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('technical_description') has-error @enderror">
                            <label for="technical_description">Deskripsi Pendek</label>
                            <textarea class="form-control" id="technical_description" name="technical_description" rows="2" placeholder="Penjelasan singkat teknis">{{ old('technical_description', $product->technical_description) }}</textarea>
                            @error('technical_description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Deskripsi Lengkap</label>
                            <textarea class="form-control summernote" id="description" name="description" rows="4" placeholder="Tuliskan deskripsi lengkap mengenai produk ini...">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="box-footer text-right">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-default pull-left">
                            <i class="fa fa-arrow-left"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <!-- Media -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-image"></i> Gambar Produk</h3>
                    </div>
                    <div class="box-body text-center">
                        <div class="form-group @error('image') has-error @enderror">
                            <div class="image-preview-container" style="margin-bottom: 15px; border: 2px dashed #ddd; border-radius: 5px; padding: 10px; background: #fafafa; min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                <img id="image-preview" src="{{ $product->image_url ?: asset('adminlte/img/default-50x50.gif') }}" style="max-width: 100%; max-height: 200px; {{ $product->image ? '' : 'display: none;' }}">
                                <div id="image-placeholder" style="{{ $product->image ? 'display: none;' : '' }}">
                                    <i class="fa fa-cloud-upload fa-4x text-muted"></i>
                                    <p class="text-muted">Klik pilih gambar</p>
                                </div>
                            </div>
                            <input type="file" id="image" name="image" accept="image/*" class="form-control" style="display: none;">
                            <button type="button" class="btn btn-warning btn-block" onclick="document.getElementById('image').click();">
                                <i class="fa fa-folder-open"></i> Ganti Gambar
                            </button>
                            <p class="help-block">Maksimal 2MB (JPG, PNG)</p>
                            @error('image')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="box box-solid box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Bantuan</h3>
                    </div>
                    <div class="box-body">
                        <p class="text-muted small">ID Produk: <code style="word-break: break-all;">{{ $product->id }}</code></p>
                        <hr style="margin: 10px 0;">
                        <ul class="list-unstyled">
                            <li style="margin-bottom: 5px;">
                                <a href="{{ route('admin.brands.index') }}" target="_blank" class="text-primary">
                                    <i class="fa fa-bookmark"></i> Kelola Brand
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.categories.index') }}" target="_blank" class="text-primary">
                                    <i class="fa fa-folder"></i> Kelola Kategori
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: function() {
            return $(this).data('placeholder') || '-- Pilih --';
        },
        allowClear: true
    });

    // Summernote initialization
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
