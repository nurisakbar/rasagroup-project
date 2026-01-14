@extends('layouts.admin')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')
@section('page-description', 'Tambah master data produk baru')

@section('breadcrumb')
    <li><a href="{{ route('admin.products.index') }}">Produk</a></li>
    <li class="active">Tambah</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <form role="form" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Basic Info -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi Dasar</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group @error('code') has-error @enderror">
                                    <label for="code">Kode Produk</label>
                                    <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" placeholder="FMF020-CT12">
                                    @error('code')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group @error('name') has-error @enderror">
                                    <label for="name">Nama Produk (Description) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="MB Cons 1L-Coconut Milk" required>
                                    @error('name')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('commercial_name') has-error @enderror">
                                    <label for="commercial_name">Nama Komersial (Commercial Name)</label>
                                    <input type="text" class="form-control" id="commercial_name" name="commercial_name" value="{{ old('commercial_name') }}" placeholder="Coconut Milk">
                                    @error('commercial_name')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('technical_description') has-error @enderror">
                                    <label for="technical_description">Deskripsi Teknis (Description 2)</label>
                                    <input type="text" class="form-control" id="technical_description" name="technical_description" value="{{ old('technical_description') }}" placeholder="(In Bottle) FG Multibev">
                                    @error('technical_description')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Deskripsi Lengkap</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Deskripsi lengkap produk...">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-tags"></i> Detail Produk</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group @error('brand_id') has-error @enderror">
                                    <label for="brand_id">Brand</label>
                                    <select class="form-control select2" id="brand_id" name="brand_id" style="width: 100%;">
                                        <option value="">-- Pilih Brand --</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="help-block">
                                        <a href="{{ route('admin.brands.create') }}" target="_blank">
                                            <i class="fa fa-plus"></i> Tambah Brand Baru
                                        </a>
                                    </p>
                                    @error('brand_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group @error('category_id') has-error @enderror">
                                    <label for="category_id">Kategori</label>
                                    <select class="form-control select2" id="category_id" name="category_id" style="width: 100%;">
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="help-block">
                                        <a href="{{ route('admin.categories.create') }}" target="_blank">
                                            <i class="fa fa-plus"></i> Tambah Kategori Baru
                                        </a>
                                    </p>
                                    @error('category_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group @error('size') has-error @enderror">
                                    <label for="size">Ukuran (Size)</label>
                                    <input type="text" class="form-control" id="size" name="size" value="{{ old('size') }}" placeholder="1 L">
                                    @error('size')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group @error('unit') has-error @enderror">
                                    <label for="unit">Satuan (UM)</label>
                                    <select class="form-control" id="unit" name="unit">
                                        <option value="">-- Pilih --</option>
                                        <option value="BT" {{ old('unit') === 'BT' ? 'selected' : '' }}>BT (Bottle)</option>
                                        <option value="PK" {{ old('unit') === 'PK' ? 'selected' : '' }}>PK (Pack)</option>
                                        <option value="BOX" {{ old('unit') === 'BOX' ? 'selected' : '' }}>BOX</option>
                                        <option value="PCS" {{ old('unit') === 'PCS' ? 'selected' : '' }}>PCS (Pieces)</option>
                                        <option value="KG" {{ old('unit') === 'KG' ? 'selected' : '' }}>KG (Kilogram)</option>
                                        <option value="L" {{ old('unit') === 'L' ? 'selected' : '' }}>L (Liter)</option>
                                    </select>
                                    @error('unit')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('price') has-error @enderror">
                                    <label for="price">Harga <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon">Rp</span>
                                        <input type="number" class="form-control" id="price" name="price" value="{{ old('price') }}" step="1" min="0" placeholder="70000" required>
                                    </div>
                                    @error('price')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group @error('weight') has-error @enderror">
                                    <label for="weight">Berat <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="weight" name="weight" value="{{ old('weight', 500) }}" step="1" min="1" placeholder="500" required>
                                        <span class="input-group-addon">gram</span>
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
                                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                    @error('status')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('image') has-error @enderror">
                            <label for="image">Gambar Produk</label>
                            <input type="file" id="image" name="image" accept="image/*">
                            <p class="help-block">Format: JPEG, PNG, JPG, GIF. Maksimal 2MB</p>
                            @error('image')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Panduan</h3>
                </div>
                <div class="box-body">
                    <p><strong>Format Field:</strong></p>
                    <table class="table table-condensed">
                        <tr>
                            <td><strong>Kode Produk</strong></td>
                            <td>Kode unik produk (A)</td>
                        </tr>
                        <tr>
                            <td><strong>Nama Produk</strong></td>
                            <td>Description dari Excel (B)</td>
                        </tr>
                        <tr>
                            <td><strong>Deskripsi Teknis</strong></td>
                            <td>Description 2 dari Excel (C)</td>
                        </tr>
                        <tr>
                            <td><strong>Nama Komersial</strong></td>
                            <td>Commercial Name (D)</td>
                        </tr>
                        <tr>
                            <td><strong>Brand</strong></td>
                            <td>Brand (E)</td>
                        </tr>
                        <tr>
                            <td><strong>Ukuran</strong></td>
                            <td>Size (F)</td>
                        </tr>
                        <tr>
                            <td><strong>Kategori</strong></td>
                            <td>Category (G)</td>
                        </tr>
                        <tr>
                            <td><strong>Satuan</strong></td>
                            <td>UM / Unit of Measure (H)</td>
                        </tr>
                        <tr>
                            <td><strong>Harga</strong></td>
                            <td>Price (I)</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-database"></i> Master Data</h3>
                </div>
                <div class="box-body">
                    <p>Kelola master data:</p>
                    <ul>
                        <li><a href="{{ route('admin.brands.index') }}"><i class="fa fa-bookmark"></i> Brand</a> ({{ $brands->count() }} data)</li>
                        <li><a href="{{ route('admin.categories.index') }}"><i class="fa fa-folder"></i> Kategori</a> ({{ $categories->count() }} data)</li>
                    </ul>
                </div>
            </div>

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cubes"></i> Stock</h3>
                </div>
                <div class="box-body">
                    <p>Stock produk dikelola di menu <a href="{{ route('admin.warehouses.index') }}"><strong>Hub</strong></a>.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: function() {
            return $(this).data('placeholder') || '-- Pilih --';
        },
        allowClear: true
    });
});
</script>
@endpush
