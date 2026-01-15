@extends('layouts.admin')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')
@section('page-description', 'Edit master data produk')

@section('breadcrumb')
    <li><a href="{{ route('admin.products.index') }}">Produk</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <form role="form" action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
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
                                    <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $product->code) }}" placeholder="FMF020-CT12">
                                    @error('code')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group @error('name') has-error @enderror">
                                    <label for="name">Nama Produk (Description) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" placeholder="MB Cons 1L-Coconut Milk" required>
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
                                    <input type="text" class="form-control" id="commercial_name" name="commercial_name" value="{{ old('commercial_name', $product->commercial_name) }}" placeholder="Coconut Milk">
                                    @error('commercial_name')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('technical_description') has-error @enderror">
                                    <label for="technical_description">Deskripsi Teknis (Description 2)</label>
                                    <input type="text" class="form-control" id="technical_description" name="technical_description" value="{{ old('technical_description', $product->technical_description) }}" placeholder="(In Bottle) FG Multibev">
                                    @error('technical_description')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Deskripsi Lengkap</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Deskripsi lengkap produk...">{{ old('description', $product->description) }}</textarea>
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
                                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
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
                                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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
                                    <input type="text" class="form-control" id="size" name="size" value="{{ old('size', $product->size) }}" placeholder="1 L">
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
                                        <option value="BT" {{ old('unit', $product->unit) === 'BT' ? 'selected' : '' }}>BT (Bottle)</option>
                                        <option value="PK" {{ old('unit', $product->unit) === 'PK' ? 'selected' : '' }}>PK (Pack)</option>
                                        <option value="BOX" {{ old('unit', $product->unit) === 'BOX' ? 'selected' : '' }}>BOX</option>
                                        <option value="PCS" {{ old('unit', $product->unit) === 'PCS' ? 'selected' : '' }}>PCS (Pieces)</option>
                                        <option value="KG" {{ old('unit', $product->unit) === 'KG' ? 'selected' : '' }}>KG (Kilogram)</option>
                                        <option value="L" {{ old('unit', $product->unit) === 'L' ? 'selected' : '' }}>L (Liter)</option>
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
                                        <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $product->price) }}" step="1" min="0" placeholder="70000" required>
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
                                        <input type="number" class="form-control" id="weight" name="weight" value="{{ old('weight', $product->weight) }}" step="1" min="1" placeholder="500" required>
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
                                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                    @error('status')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('image') has-error @enderror">
                            <label for="image">Gambar Produk</label>
                            @if($product->image)
                                <div class="mb-2" style="margin-bottom: 10px;">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="max-width: 200px; max-height: 200px; border-radius: 5px;">
                                    <p class="help-block">Gambar saat ini</p>
                                </div>
                            @endif
                            <input type="file" id="image" name="image" accept="image/*">
                            <p class="help-block">Format: JPEG, PNG, JPG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah gambar.</p>
                            @error('image')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-clock-o"></i> Info Produk</h3>
                </div>
                <div class="box-body">
                    <dl>
                        <dt>ID Produk</dt>
                        <dd><small>{{ $product->id }}</small></dd>
                        @if($product->code)
                            <dt>Kode Produk</dt>
                            <dd><span class="label label-info">{{ $product->code }}</span></dd>
                        @endif
                        @if($product->brand)
                            <dt>Brand</dt>
                            <dd><span class="label label-primary">{{ $product->brand->name }}</span></dd>
                        @endif
                        @if($product->category)
                            <dt>Kategori</dt>
                            <dd><span class="label label-default">{{ $product->category->name }}</span></dd>
                        @endif
                        <dt>Dibuat Pada</dt>
                        <dd>{{ $product->created_at->format('d M Y, H:i') }}</dd>
                        <dt>Diupdate Pada</dt>
                        <dd>{{ $product->updated_at->format('d M Y, H:i') }}</dd>
                        <dt>Dibuat Oleh</dt>
                        <dd>{{ $product->creator->name ?? 'N/A' }}</dd>
                    </dl>
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
