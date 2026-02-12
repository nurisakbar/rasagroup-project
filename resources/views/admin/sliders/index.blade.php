@extends('layouts.admin')

@section('title', 'Slider')
@section('page-title', 'Slider')
@section('page-description', 'Kelola data slider homepage')

@section('breadcrumb')
    <li class="active">Slider</li>
@endsection

@section('content')
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Aksi</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12 text-right">
                    <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Tambah Slider
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Daftar Slider</h3>
        </div>
        <div class="box-body">
            <table id="sliders-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="120px">Gambar</th>
                        <th>Judul</th>
                        <th>Link</th>
                        <th>Urutan</th>
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
    var table = $('#sliders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.sliders.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'image_display', name: 'image', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'link', name: 'link' },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'status_badge', name: 'is_active' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[4, 'asc']],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            emptyTable: "Tidak ada data slider",
            zeroRecords: "Tidak ada data yang cocok",
            paginate: { first: "Pertama", previous: "Sebelumnya", next: "Selanjutnya", last: "Terakhir" }
        }
    });
});
</script>
@endpush
