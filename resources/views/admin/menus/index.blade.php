@extends('layouts.admin')

@section('title', 'Menu')
@section('page-title', 'Kelola Menu')
@section('page-description', 'Daftar menu dan detail menu')

@section('breadcrumb')
    <li class="active">Menu</li>
@endsection

@section('content')
    <div class="box box-default">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12 text-right">
                    <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Tambah Menu
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Daftar Menu</h3>
        </div>
        <div class="box-body">
            <table id="menus-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="60px">Gambar</th>
                        <th>Nama Menu</th>
                        <th>Slug</th>
                        <th>Daftar Produk</th>
                        <th>Status</th>
                        <th width="100px">Aksi</th>
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
    var table = $('#menus-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.menus.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'image_display', name: 'gambar', orderable: false, searchable: false },
            { data: 'nama_menu', name: 'nama_menu' },
            { data: 'slug', name: 'slug' },
            { data: 'product_names', name: 'product_names', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status_aktif', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'asc']],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            paginate: { previous: "Sebelumnya", next: "Selanjutnya" }
        }
    });
});
</script>
@endpush
