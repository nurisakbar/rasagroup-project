@extends('layouts.admin')

@section('title', 'Tambah Menu')
@section('page-title', 'Tambah Menu Baru')

@section('breadcrumb')
    <li><a href="{{ route('admin.menus.index') }}">Menu</a></li>
    <li class="active">Tambah</li>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('adminlte/plugins/select2/select2.min.css') }}">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<style>
    .select2-container--default .select2-selection--single {
        border-radius: 0;
        border-color: #d2d6de;
        height: 34px;
    }
    .image-preview {
        width: 100%;
        max-height: 200px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px dashed #ddd;
        padding: 5px;
        display: none;
        margin-bottom: 10px;
    }
    .note-editable { background: #fff !important; }
    .box { border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-top: 3px solid var(--rasa-maroon); }
    .btn-primary { background-color: var(--rasa-maroon); border-color: var(--rasa-maroon-dark); }
    .btn-primary:hover { background-color: var(--rasa-maroon-dark); border-color: var(--rasa-maroon-dark); }
</style>
@endpush

@section('content')
    <form action="{{ route('admin.menus.store') }}" method="POST" id="menu-form" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-5">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle text-maroon"></i> Informasi Utama</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group @error('nama_menu') has-error @enderror">
                            <label for="nama_menu">Nama Menu <span class="text-danger">*</span></label>
                            <input type="text" name="nama_menu" id="nama_menu" class="form-control input-lg" value="{{ old('nama_menu') }}" required placeholder="Contoh: Menu Paket Hemat">
                            @error('nama_menu')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('deskripsi') has-error @enderror">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control summernote" rows="5" placeholder="Deskripsi menu...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('gambar') has-error @enderror">
                            <label for="gambar">Gambar Menu</label>
                            <img id="preview" class="image-preview">
                            <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" onchange="previewImage(this)">
                            <p class="help-block"><i class="fa fa-info-circle"></i> Format: JPG, PNG, GIF. Max: 2MB</p>
                            @error('gambar')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <div class="checkbox">
                                <label class="text-bold">
                                    <input type="checkbox" name="status_aktif" value="1" {{ old('status_aktif', '1') == '1' ? 'checked' : '' }}> 
                                    <span class="text-success">Aktifkan Menu Ini</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-cubes text-maroon"></i> Detail Item Menu</h3>
                        <div class="box-tools">
                            <button type="button" class="btn btn-success btn-sm btn-flat" id="add-item">
                                <i class="fa fa-plus"></i> Tambah Item
                            </button>
                        </div>
                    </div>
                    <div class="box-body no-padding">
                        <table class="table table-striped" id="details-table">
                            <thead>
                                <tr class="bg-gray">
                                    <th style="padding-left: 15px;">Produk <span class="text-danger">*</span></th>
                                    <th width="120px">Jumlah <span class="text-danger">*</span></th>
                                    <th width="50px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(old('details'))
                                    @foreach(old('details') as $index => $detail)
                                        <tr class="item-row">
                                            <td style="padding-left: 15px;">
                                                <select name="details[{{ $index }}][product_id]" class="form-control select2" required style="width: 100%;">
                                                    <option value="">Pilih Produk</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" {{ $detail['product_id'] == $product->id ? 'selected' : '' }}>
                                                            {{ $product->full_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="details[{{ $index }}][jumlah]" class="form-control text-center" value="{{ $detail['jumlah'] }}" min="1" required>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-link text-danger remove-item"><i class="fa fa-times-circle fa-lg"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="item-row">
                                        <td style="padding-left: 15px;">
                                            <select name="details[0][product_id]" class="form-control select2" required style="width: 100%;">
                                                <option value="">Pilih Produk</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->full_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="details[0][jumlah]" class="form-control text-center" value="1" min="1" required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-link text-danger remove-item"><i class="fa fa-times-circle fa-lg"></i></button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        @error('details')
                            <div class="pad text-danger"><i class="fa fa-exclamation-triangle"></i> {{ $message }}</div>
                        @enderror
                    </div>
                    <div class="box-footer">
                        <p class="text-muted small"><i class="fa fa-info-circle"></i> Pastikan semua item produk telah benar sebelum menyimpan.</p>
                    </div>
                </div>

                <div class="text-right">
                    <a href="{{ route('admin.menus.index') }}" class="btn btn-default btn-lg">Batal</a>
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-save"></i> Simpan Menu</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script src="{{ asset('adminlte/plugins/select2/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2();

    $('.summernote').summernote({
        height: 250,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });

    let rowIndex = {{ old('details') ? count(old('details')) : 1 }};

    $('#add-item').click(function() {
        let newRow = `
            <tr class="item-row">
                <td style="padding-left: 15px;">
                    <select name="details[${rowIndex}][product_id]" class="form-control select2" required style="width: 100%;">
                        <option value="">Pilih Produk</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->full_name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="details[${rowIndex}][jumlah]" class="form-control text-center" value="1" min="1" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-link text-danger remove-item"><i class="fa fa-times-circle fa-lg"></i></button>
                </td>
            </tr>
        `;
        
        let $newRow = $(newRow);
        $('#details-table tbody').append($newRow);
        $newRow.find('.select2').select2();
        rowIndex++;
    });

    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
        } else {
            alert('Minimal harus ada 1 item menu.');
        }
    });
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#preview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
