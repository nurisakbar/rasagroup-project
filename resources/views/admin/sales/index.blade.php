@extends('layouts.admin')

@section('title', 'Data Sales')
@section('page-title', 'Manajemen Sales')
@section('page-description', 'Kelola data pengguna dengan peran Sales')

@section('breadcrumb')
    <li class="active">Sales</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0;
        margin: 0;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Sales</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.sales.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah Sales
                        </a>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="sales-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Sales</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Target Bulanan</th>
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan di-load via DataTables server-side -->
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
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script>
    $(function () {
        $('#sales-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.sales.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'sales_code', name: 'sales_code'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'monthly_target', name: 'monthly_target'},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[6, 'desc']],
            language: {
                processing: "Memproses...",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        $(document).on('submit', '.delete-form', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus sales ini?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
