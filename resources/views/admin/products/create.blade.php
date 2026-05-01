@extends('layouts.admin')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')
@section('page-description', 'Tambah master data produk baru')

@section('breadcrumb')
    <li><a href="{{ route('admin.products.index') }}">Produk</a></li>
    <li class="active">Tambah</li>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <style>
        .note-editable { background: #fff !important; }
    </style>
@endpush

@section('content')
    <form role="form" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <div class="col-md-9">
                <!-- Identitas Produk -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-tag"></i> Identitas Produk</h3>
                    </div>
                    <div class="box-body">
                        <!-- Group 1: General Info -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group @error('code') has-error @enderror">
                                    <label for="code"><i class="fa fa-barcode"></i> Kode Produk (SKU)</label>
                                    <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" placeholder="Contoh: FDA010-AP01">
                                    @error('code')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('brand_id') has-error @enderror">
                                    <label for="brand_id"><i class="fa fa-bookmark"></i> Brand</label>
                                    <select class="form-control select2" id="brand_id" name="brand_id" style="width: 100%;">
                                        <option value="">-- Pilih Brand --</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
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
                                    <label for="category_id"><i class="fa fa-folder-open"></i> Kategori</label>
                                    <select class="form-control select2" id="category_id" name="category_id" style="width: 100%;">
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                <div class="form-group @error('status') has-error @enderror">
                                    <label for="status"><i class="fa fa-toggle-on"></i> Status <span class="text-danger">*</span></label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                                    </select>
                                    @error('status')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group @error('name') has-error @enderror">
                                    <label for="name"><i class="fa fa-info-circle"></i> Nama Produk (QID) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control input-lg" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: MB Cons 1L-Coconut Milk" required style="font-weight: bold;">
                                    @error('name')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group @error('commercial_name') has-error @enderror">
                                    <label for="commercial_name"><i class="fa fa-globe"></i> Nama Komersial (Tampil di Website)</label>
                                    <input type="text" class="form-control" id="commercial_name" name="commercial_name" value="{{ old('commercial_name') }}" placeholder="Contoh: Coconut Milk">
                                    <p class="help-block"><small>Jika dikosongkan, Nama Produk (QID) akan digunakan di website.</small></p>
                                    @error('commercial_name')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>
                        <!-- Group 2: Measurement & Pricing -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group @error('price') has-error @enderror">
                                    <label for="price"><i class="fa fa-money"></i> Harga Jual <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon">Rp</span>
                                        <input type="number" class="form-control" id="price" name="price" value="{{ old('price') }}" step="1" min="0" placeholder="0" required style="font-size: 1.1em; font-weight: bold; color: #6A1B1B;">
                                    </div>
                                    @error('price')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('reseller_point') has-error @enderror">
                                    <label for="reseller_point"><i class="fa fa-star"></i> Poin per Unit</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="reseller_point" name="reseller_point" value="{{ old('reseller_point', 0) }}" step="1" min="0" placeholder="0">
                                        <span class="input-group-addon">PTS</span>
                                    </div>
                                    @error('reseller_point')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('weight') has-error @enderror">
                                    <label for="weight"><i class="fa fa-balance-scale"></i> Berat <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="weight" name="weight" value="{{ old('weight', 1000) }}" step="1" min="1" required>
                                        <span class="input-group-addon">gram</span>
                                    </div>
                                    @error('weight')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('unit') has-error @enderror">
                                    <label for="unit"><i class="fa fa-cube"></i> Satuan (UoM)</label>
                                    <input type="text" class="form-control" id="unit" name="unit" value="{{ old('unit') }}" placeholder="Contoh: BT">
                                    @error('unit')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group @error('size') has-error @enderror">
                                    <label for="size"><i class="fa fa-arrows-h"></i> Ukuran Sizing</label>
                                    <input type="text" class="form-control" id="size" name="size" value="{{ old('size') }}" placeholder="Contoh: 760ml">
                                    @error('size')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('large_unit') has-error @enderror">
                                    <label for="large_unit"><i class="fa fa-th-large"></i> Satuan Besar</label>
                                    <input type="text" class="form-control" id="large_unit" name="large_unit" value="{{ old('large_unit', 'CTN') }}" placeholder="Contoh: CTN">
                                    @error('large_unit')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('units_per_large') has-error @enderror">
                                    <label for="units_per_large"><i class="fa fa-exchange"></i> Isi per satuan besar</label>
                                    <input type="number" class="form-control" id="units_per_large" name="units_per_large" value="{{ old('units_per_large') }}" min="2" step="1" placeholder="Contoh: 12">
                                    <p class="help-block text-muted small mb-0">Jumlah satuan UoM dalam 1 satuan besar. Kosongkan jika tidak dipakai.</p>
                                    @error('units_per_large')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>
                        <!-- Group 3: Description -->
                        <div class="form-group @error('technical_description') has-error @enderror">
                            <label for="technical_description"><i class="fa fa-list-alt"></i> Deskripsi Pendek / Teknis</label>
                            <textarea class="form-control" id="technical_description" name="technical_description" rows="2" placeholder="Penjelasan singkat teknis">{{ old('technical_description') }}</textarea>
                            @error('technical_description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description"><i class="fa fa-align-left"></i> Deskripsi Produk (Website)</label>
                            <textarea class="form-control summernote" id="description" name="description" rows="4" placeholder="Tuliskan deskripsi lengkap mengenai produk ini...">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="box-footer text-right">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-default pull-left">
                            <i class="fa fa-arrow-left"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg btn-maroon">
                            <i class="fa fa-save"></i> Simpan Produk
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <!-- Media: Primary Image -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-image"></i> Gambar Utama</h3>
                    </div>
                    <div class="box-body text-center">
                        <div class="form-group @error('image') has-error @enderror">
                            <div class="image-preview-container" style="margin-bottom: 15px; border: 2px dashed #ddd; border-radius: 5px; padding: 10px; background: #fafafa; min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                <img id="image-preview" src="{{ asset('adminlte/img/default-50x50.gif') }}" style="max-width: 100%; max-height: 200px; display: none;">
                                <div id="image-placeholder">
                                    <i class="fa fa-cloud-upload fa-4x text-muted"></i>
                                    <p class="text-muted">Klik pilih gambar</p>
                                </div>
                            </div>
                            <input type="file" id="image" name="image" accept="image/*" class="form-control" style="display: none;">
                            <button type="button" class="btn btn-warning btn-block" onclick="document.getElementById('image').click();">
                                <i class="fa fa-folder-open"></i> Pilih Gambar Utama
                            </button>
                            <p class="help-block">Maksimal 2MB (JPG, PNG)</p>
                            @error('image')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Media: Gallery -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-images"></i> Galeri Produk</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>Tambah Gambar Galeri</label>
                            <input type="file" name="images[]" multiple class="form-control" accept="image/*">
                            <p class="help-block">Bisa pilih lebih dari satu gambar.</p>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="box box-solid box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Bantuan</h3>
                    </div>
                    <div class="box-body">
                        <p class="text-muted small">Pastikan data yang diinput sesuai dengan master data Excel.</p>
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

    // Unit Conversion Label Helper
    $('#small_unit').on('input', function() {
        var val = $(this).val() || 'satuan';
        $('#small_unit_label').text(val);
    });
});
</script>
@endpush
