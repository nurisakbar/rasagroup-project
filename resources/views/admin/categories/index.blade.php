@extends('layouts.admin')

@section('title', 'Kategori')
@section('page-title', 'Master Data Kategori')
@section('page-description', 'Kelola data kategori produk')

@section('breadcrumb')
    <li class="active">Kategori</li>
@endsection

@section('content')
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter & Aksi</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Status</label>
                        <select id="filter-status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-9 text-right">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Tambah Kategori
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Daftar Kategori</h3>
        </div>
        <div class="box-body">
            <table id="categories-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="60px">Icon</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Produk</th>
                        <th>Status</th>
                        <th width="100px">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#categories-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.categories.index') }}",
            data: function(d) {
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'icon_display', name: 'icon', orderable: false, searchable: false },
            { data: 'name_info', name: 'name' },
            { data: 'description', name: 'description', render: function(data) { return data || '-'; } },
            { data: 'products_count_badge', name: 'products_count', orderable: false, searchable: false },
            { data: 'status_badge', name: 'is_active', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'asc']],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            emptyTable: "Tidak ada data kategori",
            zeroRecords: "Tidak ada data yang cocok",
            paginate: { first: "Pertama", previous: "Sebelumnya", next: "Selanjutnya", last: "Terakhir" }
        }
    });

    $('#filter-status').change(function() { table.draw(); });
});
</script>
@endpush

