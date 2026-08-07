@extends('layouts.admin')

@section('title', 'Data Order Sales')
@section('page-title', 'Order - ' . $sale->name)
@section('page-description', 'Daftar order yang menggunakan kode sales: ' . $sale->sales_code)

@section('breadcrumb')
    <li><a href="{{ route('admin.sales.index') }}">Sales</a></li>
    <li class="active">Orders</li>
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
                    <h3 class="box-title">Daftar Order (Kode: {{ $sale->sales_code }})</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali ke Sales
                        </a>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row mb-4" style="margin-bottom: 20px;">
                        <div class="col-md-3">
                            <label>Dari Tanggal</label>
                            <input type="date" id="date_from" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Sampai Tanggal</label>
                            <input type="date" id="date_to" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label><br>
                            <button id="btn-filter" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Filter</button>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label><br>
                            <button id="btn-reset" class="btn btn-default btn-block"><i class="fa fa-refresh"></i> Reset</button>
                        </div>
                    </div>
                    <hr>
                    <table id="sales-orders-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Order Info</th>
                                <th>Pembeli</th>
                                <th>Ekspedisi</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
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
        var table = $('#sales-orders-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.sales.orders', $sale) }}",
                data: function(d) {
                    d.date_from = $('#date_from').val();
                    d.date_to = $('#date_to').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'order_info', name: 'order_number'},
                {data: 'buyer_info', name: 'user.name'},
                {data: 'expedition_info', name: 'expedition_service', orderable: false, searchable: false},
                {data: 'total_amount', name: 'total_amount'},
                {data: 'status_badge', name: 'order_status'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[1, 'desc']],
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

        $('#btn-filter').on('click', function() {
            table.draw();
        });

        $('#btn-reset').on('click', function() {
            $('#date_from').val('');
            $('#date_to').val('');
            table.draw();
        });
    });
</script>
@endpush
