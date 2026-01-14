@extends('layouts.admin')

@section('title', 'Level Harga')
@section('page-title', 'Level Harga Distributor')
@section('page-description', 'Kelola level harga untuk distributor')

@section('breadcrumb')
    <li class="active">Level Harga</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
<style>
    .dataTables_filter { display: none; }
</style>
@endpush

@section('content')
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter & Aksi</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-9"></div>
                <div class="col-md-3 text-right">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <a href="{{ route('admin.price-levels.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Tambah Level Harga
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Daftar Level Harga</h3>
        </div>
        <div class="box-body">
            <table id="price-levels-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Level</th>
                        <th>Deskripsi</th>
                        <th>Diskon</th>
                        <th>Urutan</th>
                        <th>Jumlah Produk</th>
                        <th>Status</th>
                        <th width="120px">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#price-levels-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.price-levels.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'description', name: 'description', render: function(data) { return data || '-'; } },
            { data: 'discount_formatted', name: 'discount_percentage' },
            { data: 'order', name: 'order' },
            { data: 'products_count', name: 'products_count', orderable: false },
            { data: 'status_badge', name: 'is_active', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[4, 'asc']],
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
});
</script>
@endpush







