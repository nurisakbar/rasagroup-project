@extends('layouts.admin')

@section('title', 'Produk')
@section('page-title', 'Master Data Produk')
@section('page-description', 'Kelola master data produk')

@section('breadcrumb')
    <li class="active">Produk</li>
@endsection

@push('styles')
<style>
    .filter-box { margin-bottom: 0; }
    #products-table_length { margin-bottom: 15px; }
</style>
@endpush

@section('content')
    @if(session('import_errors'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-warning"></i> Peringatan Import</h4>
            <ul>
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Filter Box -->
    <div class="box box-default filter-box">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter & Aksi</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <select id="filter-status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Brand</label>
                        <select id="filter-brand" class="form-control select2" style="width: 100%;">
                            <option value="">Semua Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select id="filter-category" class="form-control select2" style="width: 100%;">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-default btn-sm" title="Master Brand">
                            <i class="fa fa-bookmark"></i>
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-default btn-sm" title="Master Kategori">
                            <i class="fa fa-folder"></i>
                        </a>
                        <a href="{{ route('admin.products.template') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-download"></i> Template
                        </a>
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#importModal">
                            <i class="fa fa-upload"></i> Import
                        </button>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Produk</h3>
                </div>
                <div class="box-body">
                    <table id="products-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Gambar</th>
                                <th style="width: 100px;">Kode</th>
                                <th>Nama Produk</th>
                                <th>Brand / Kategori</th>
                                <th>Ukuran</th>
                                <th>Harga</th>
                                <th>Status</th>
                                <th style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title"><i class="fa fa-upload"></i> Import Produk dari Excel</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <h4><i class="fa fa-info-circle"></i> Format Excel yang Didukung</h4>
                            <p>Gunakan header kolom berikut:</p>
                            <table class="table table-bordered table-condensed" style="background: white;">
                                <thead>
                                    <tr class="bg-primary">
                                        <th>Header</th>
                                        <th>Keterangan</th>
                                        <th>Contoh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>Product Code</code></td>
                                        <td>Kode unik produk</td>
                                        <td>FMF020-CT12</td>
                                    </tr>
                                    <tr>
                                        <td><code>Description</code></td>
                                        <td>Nama produk</td>
                                        <td>MB Cons 1L-Coconut Milk</td>
                                    </tr>
                                    <tr>
                                        <td><code>Description 2</code></td>
                                        <td>Deskripsi teknis</td>
                                        <td>(In Bottle) FG Multibev</td>
                                    </tr>
                                    <tr>
                                        <td><code>Commercial Name</code></td>
                                        <td>Nama komersial</td>
                                        <td>Coconut Milk</td>
                                    </tr>
                                    <tr>
                                        <td><code>Brand</code></td>
                                        <td>Nama brand (auto-create)</td>
                                        <td>Multibev</td>
                                    </tr>
                                    <tr>
                                        <td><code>Size</code></td>
                                        <td>Ukuran</td>
                                        <td>1 L</td>
                                    </tr>
                                    <tr>
                                        <td><code>Category</code></td>
                                        <td>Nama kategori (auto-create)</td>
                                        <td>Coconut</td>
                                    </tr>
                                    <tr>
                                        <td><code>UM</code></td>
                                        <td>Satuan</td>
                                        <td>BT, PK, BOX</td>
                                    </tr>
                                    <tr>
                                        <td><code>Price</code></td>
                                        <td><strong>Harga (Wajib)</strong></td>
                                        <td>70000</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="text-muted">
                                <i class="fa fa-lightbulb-o"></i> Brand dan Kategori yang belum ada akan otomatis dibuat.
                            </p>
                            <p>
                                <a href="{{ route('admin.products.template') }}" class="btn btn-sm btn-default">
                                    <i class="fa fa-download"></i> Download Template CSV
                                </a>
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="file">Pilih File Excel/CSV</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            <p class="help-block">Format: .xlsx, .xls, .csv (Maks. 10MB)</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-upload"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        allowClear: true
    });

    var table = $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.products.index') }}",
            data: function(d) {
                d.status = $('#filter-status').val();
                d.brand_id = $('#filter-brand').val();
                d.category_id = $('#filter-category').val();
            }
        },
        columns: [
            { data: 'image_display', name: 'image', orderable: false, searchable: false },
            { data: 'code_display', name: 'code' },
            { data: 'name_info', name: 'name' },
            { data: 'brand_info', name: 'brand', orderable: false, searchable: true },
            { data: 'size_unit', name: 'size', orderable: false, searchable: true },
            { data: 'price_formatted', name: 'price' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'asc']],
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Tidak ada data yang cocok",
            emptyTable: "Tidak ada data tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });

    // Filter handlers
    $('#filter-status, #filter-brand, #filter-category').change(function() {
        table.draw();
    });
});
</script>
@endpush
