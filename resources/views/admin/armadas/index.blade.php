@extends('layouts.admin')

@section('title', 'Armada Pengiriman')
@section('page-title', 'Manajemen Armada Pengiriman')
@section('page-description', 'Kelola data armada pengiriman')

@section('breadcrumb')
    <li class="active">Armada Pengiriman</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Armada Pengiriman</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.armadas.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah Armada
                        </a>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body table-responsive">
                    <table id="armadas-table" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Armada</th>
                                <th>Hub / Gudang</th>
                                <th>Keterangan</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#armadas-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.armadas.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'hub_name', name: 'hub.name' },
            { data: 'description', name: 'description' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        language: {
            processing: '<i class="fa fa-spinner fa-spin"></i> Memuat...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            emptyTable: 'Belum ada data armada pengiriman.',
            zeroRecords: 'Tidak ada data yang cocok'
        }
    });
});
</script>
@endpush
