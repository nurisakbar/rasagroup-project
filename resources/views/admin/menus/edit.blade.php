@extends('layouts.admin')

@section('title', 'Edit Menu')
@section('page-title', 'Edit Menu: ' . $menu->nama_menu)

@section('breadcrumb')
    <li><a href="{{ route('admin.menus.index') }}">Menu</a></li>
    <li class="active">Edit</li>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('adminlte/plugins/select2/select2.min.css') }}">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
@include('admin.menus.partials.form-styles')
<style>
    .btn-primary { background-color: var(--rasa-maroon); border-color: var(--rasa-maroon-dark); }
    .btn-primary:hover { background-color: var(--rasa-maroon-dark); border-color: var(--rasa-maroon-dark); }
</style>
@endpush

@section('content')
    <form action="{{ route('admin.menus.update', $menu) }}" method="POST" id="menu-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-7">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-align-left"></i> Informasi menu</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group @error('nama_menu') has-error @enderror">
                            <label for="nama_menu">Nama menu <span class="text-danger">*</span></label>
                            <input type="text" name="nama_menu" id="nama_menu" class="form-control input-lg" value="{{ old('nama_menu', $menu->nama_menu) }}" required placeholder="Contoh: Menu Paket Hemat">
                            @error('nama_menu')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('deskripsi') has-error @enderror">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control summernote" rows="5" placeholder="Deskripsi menu...">{{ old('deskripsi', $menu->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-photo"></i> Media &amp; pengaturan</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group @error('gambar') has-error @enderror">
                            <label for="gambar">Gambar menu</label>
                            @if($menu->gambar)
                                <img id="preview" src="{{ $menu->image_url }}" alt="{{ $menu->nama_menu }}" class="img-responsive" style="max-height: 200px; margin-bottom: 10px; border: 1px solid #ddd; padding: 3px;">
                            @else
                                <img id="preview" class="img-responsive" style="display: none; max-height: 200px; margin-bottom: 10px; border: 1px solid #ddd; padding: 3px;" alt="">
                            @endif
                            <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" onchange="previewImage(this)">
                            <p class="help-block"><small>JPG, PNG, GIF · Maks. 2MB. Kosongkan bila tidak mengubah gambar.</small></p>
                            @error('gambar')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="status_aktif" value="1" {{ old('status_aktif', $menu->status_aktif) ? 'checked' : '' }}>
                                    Menu aktif
                                </label>
                            </div>
                            <p class="help-block"><small>Nonaktifkan untuk menyembunyikan menu dari tampilan publik.</small></p>
                        </div>

                        <hr>

                        @include('admin.menus.partials.display-window-fields', ['menu' => $menu])
                    </div>
                </div>
            </div>
        </div>

        @php
            $details = old('details') ?? $menu->details;
        @endphp

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-cubes"></i> Komposisi produk</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-success btn-sm" id="add-item">
                                <i class="fa fa-plus"></i> Tambah item
                            </button>
                        </div>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-bordered table-hover" id="details-table">
                            <thead>
                                <tr>
                                    <th>Produk <span class="text-danger">*</span></th>
                                    <th width="130">Jumlah <span class="text-danger">*</span></th>
                                    <th width="56"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($details as $index => $detail)
                                    @php
                                        $productId = old('details') ? $detail['product_id'] : $detail->product_id;
                                        $jumlah = old('details') ? $detail['jumlah'] : $detail->jumlah;
                                    @endphp
                                    <tr class="item-row">
                                        <td>
                                            <select name="details[{{ $index }}][product_id]" class="form-control select2" required style="width: 100%;">
                                                <option value="">Pilih produk</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" {{ $productId == $product->id ? 'selected' : '' }}>
                                                        {{ $product->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="details[{{ $index }}][jumlah]" class="form-control text-center" value="{{ $jumlah }}" min="1" required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-link text-danger remove-item p-0" title="Hapus baris"><i class="fa fa-times-circle fa-lg"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @error('details')
                        <div class="box-body text-danger"><i class="fa fa-exclamation-triangle"></i> {{ $message }}</div>
                    @enderror
                    <div class="box-footer">
                        <p class="text-muted small mb-0"><i class="fa fa-info-circle"></i> Pastikan produk dan jumlah sudah benar sebelum menyimpan.</p>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-right">
            <a href="{{ route('admin.menus.index') }}" class="btn btn-default">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Perbarui menu</button>
        </p>
    </form>
@endsection

@push('scripts')
<script src="{{ asset('adminlte/plugins/select2/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2();

    $('.summernote').summernote({
        height: 220,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });

    let rowIndex = {{ count($details) }};

    $('#add-item').click(function() {
        let newRow = `
            <tr class="item-row">
                <td>
                    <select name="details[${rowIndex}][product_id]" class="form-control select2" required style="width: 100%;">
                        <option value="">Pilih produk</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->full_name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="details[${rowIndex}][jumlah]" class="form-control text-center" value="1" min="1" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-link text-danger remove-item p-0" title="Hapus baris"><i class="fa fa-times-circle fa-lg"></i></button>
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
