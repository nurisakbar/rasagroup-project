@extends('layouts.admin')

@section('title', 'Promo')
@section('page-title', 'Promo')
@section('page-description', 'Kelola data promo dan diskon')

@section('breadcrumb')
    <li class="active">Promo</li>
@endsection

@section('content')
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Aksi</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12 text-right">
                    <a href="{{ route('admin.promos.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Tambah Promo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Daftar Promo</h3>
        </div>
        <div class="box-body">
            <table id="promos-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="80px">Gambar</th>
                        <th>Kode Promo</th>
                        <th>Judul Promo</th>
                        <th>Harga</th>
                        <th>Masa Berlaku</th>
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
    var table = $('#promos-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.promos.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'kode_promo', name: 'kode_promo' },
            { data: 'judul_promo', name: 'judul_promo' },
            { data: 'harga_format', name: 'harga' },
            { data: 'masa_berlaku', name: 'awal', searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            emptyTable: "Tidak ada data promo",
            zeroRecords: "Tidak ada data yang cocok",
            paginate: { first: "Pertama", previous: "Sebelumnya", next: "Selanjutnya", last: "Terakhir" }
        }
    });
});
</script>
@endpush
