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
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Sumber</label>
                        <select id="filter-sync-source" class="form-control">
                            <option value="">Semua Sumber</option>
                            <option value="jubelio">Jubelio</option>
                            <option value="qad">QAD</option>
                            <option value="both">Keduanya</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <button type="button" id="btn-filter" class="btn btn-default">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <button type="button" id="btn-reset" class="btn btn-default">
                            <i class="fa fa-times"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-right">
                    <div class="form-group" style="margin-bottom: 0;">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah
                        </a>
                        @include('admin.partials.sync-qad-jubelio')
                        @if(app()->environment('local'))
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteAllProducts()" title="Hanya tersedia di APP_ENV=local">
                                <i class="fa fa-trash"></i> Hapus Semua
                            </button>
                            <form id="delete-all-products-form" action="{{ route('admin.products.destroy-all') }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="confirm" id="delete-all-confirm-input" value="">
                            </form>
                        @endif
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
                                <th style="width: 50px;">QRCode</th>
                                <th style="width: 100px;">Kode</th>
                                <th>Nama Produk</th>
                                <th>Brand / Kategori</th>
                                <th>Ukuran</th>
                                <th>Harga</th>
                                <th>Poin</th>
                                <th>Sumber</th>
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
                d.sync_source = $('#filter-sync-source').val();
            }
        },
        columns: [
            { data: 'image_display', name: 'image', orderable: false, searchable: false },
            { data: 'qrcode_display', name: 'qrcode', orderable: false, searchable: false },
            { data: 'code_display', name: 'code' },
            { data: 'name_info', name: 'name' },
            { data: 'brand_info', name: 'brand', orderable: false, searchable: true },
            { data: 'size_unit', name: 'size', orderable: false, searchable: true },
            { data: 'price_formatted', name: 'price' },
            { data: 'reseller_point_display', name: 'reseller_point', orderable: true },
            { data: 'sync_sources_info', name: 'sync_sources', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'asc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
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
    $('#filter-status, #filter-brand, #filter-category, #filter-sync-source').change(function() {
        table.draw();
    });

    $('#btn-filter').on('click', function() {
        table.draw();
    });

    $('#btn-reset').on('click', function() {
        $('#filter-status').val('');
        $('#filter-brand').val('').trigger('change');
        $('#filter-category').val('').trigger('change');
        $('#filter-sync-source').val('');
        table.draw();
    });
});

function confirmDeleteAllProducts() {
    if (!confirm('PERINGATAN: Semua produk dan data terkait akan dihapus permanen:\n- Stok gudang & riwayat stok\n- Keranjang (regular & distributor)\n- Pesanan & item pesanan\n- Menu Hari Ini\n- Harga level & gambar produk\n\nLanjutkan?')) {
        return;
    }
    var typed = prompt('Ketik HAPUS SEMUA untuk konfirmasi:');
    if (typed !== 'HAPUS SEMUA') {
        alert('Konfirmasi dibatalkan.');
        return;
    }
    document.getElementById('delete-all-confirm-input').value = typed;
    document.getElementById('delete-all-products-form').submit();
}
</script>
@endpush
