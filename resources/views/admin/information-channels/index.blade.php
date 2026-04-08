@extends('layouts.admin')

@section('title', 'Saluran Informasi')
@section('page-title', 'Manajemen Saluran Informasi')
@section('page-description', 'Kelola daftar saluran informasi untuk pengguna.')

@section('breadcrumb')
    <li class="active">Saluran Informasi</li>
@endsection

@section('content')
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter</h3>
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
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Target</label>
                        <select id="filter-target" class="form-control">
                            <option value="">Semua Target</option>
                            <option value="all">Semua</option>
                            <option value="distributor">Distributor</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <button id="btn-reset" class="btn btn-default"><i class="fa fa-refresh"></i> Reset</button>
                        <a href="{{ route('admin.information-channels.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Tambah Saluran
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Daftar Saluran Informasi</h3>
        </div>
        <div class="box-body">
            <table id="channels-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Judul</th>
                        <th>Target</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
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
    var table = $('#channels-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.information-channels.index') }}",
            data: function(d) {
                d.status = $('#filter-status').val();
                d.target = $('#filter-target').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'title_info', name: 'title' },
            { data: 'audience', name: 'target_audience' },
            { data: 'date_start', name: 'start_date' },
            { data: 'date_end', name: 'end_date' },
            { data: 'status_badge', name: 'is_active', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            emptyTable: "Tidak ada data saluran informasi",
            zeroRecords: "Tidak ada data yang cocok",
            paginate: { first: "Pertama", previous: "Sebelumnya", next: "Selanjutnya", last: "Terakhir" }
        }
    });

    $('select').change(function() { table.draw(); });
    
    $('#btn-reset').click(function() {
        $('select').val('');
        table.draw();
    });
});
</script>
@endpush
