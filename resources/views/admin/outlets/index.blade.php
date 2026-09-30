@extends('layouts.admin')

@section('title', 'Data User Outlet')
@section('page-title', 'Manajemen Data User Outlet')
@section('page-description', 'Kelola data pengguna dengan role outlet')

@section('breadcrumb')
    <li class="active">Data Outlet</li>
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
                    <h3 class="box-title">Daftar Outlet</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.outlets.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah Outlet
                        </a>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-4">
                            <label for="filter_sales">Filter by Sales:</label>
                            <select id="filter_sales" class="form-control select2" style="width: 100%;">
                                <option value="">Semua Sales</option>
                                <option value="none">-- Tanpa Sales --</option>
                                @foreach($salesList as $sales)
                                    <option value="{{ $sales->sales_code }}">{{ $sales->name }} ({{ $sales->sales_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <table id="users-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode QAD</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Nama Sales</th>
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
        var table = $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.outlets.index') }}",
                data: function (d) {
                    d.sales_code = $('#filter_sales').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'qad_customer_code', name: 'qad_customer_code'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'phone', name: 'phone'},
                {data: 'sales_name', name: 'salesPerson.name', orderable: false, searchable: false},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[5, 'desc']],
            language: {
                processing: "Memproses...",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ data keseluruhan)",
                paginate: {
                    first: "Pertama",
                    previous: "Sebelumnya",
                    next: "Selanjutnya",
                    last: "Terakhir"
                }
            }
        });

        $('#filter_sales').select2({
            placeholder: "Pilih Sales",
            allowClear: true
        });

        $('#filter_sales').on('change', function() {
            table.draw();
        });

        $(document).on('submit', '.delete-form', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus outlet ini?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
