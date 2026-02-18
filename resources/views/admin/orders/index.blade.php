@extends('layouts.admin')

@section('title', 'Pesanan')
@section('page-title', 'Manajemen Pesanan')
@section('page-description', 'Kelola semua pesanan')

@section('breadcrumb')
    <li class="active">Pesanan</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
    /* .dataTables_filter { display: none; } */
    .filter-box { margin-bottom: 0; }
    #orders-table_length { margin-bottom: 15px; }
    .datepicker { z-index: 1151 !important; }
</style>
@endpush

@section('content')
    <!-- Filter Box -->
    <div class="box box-default filter-box">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter Pesanan</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <div class="input-group date">
                            <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <input type="text" id="filter-date-from" class="form-control datepicker" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <div class="input-group date">
                            <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <input type="text" id="filter-date-to" class="form-control datepicker" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status Pesanan</label>
                        <select id="filter-status" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Tipe Order</label>
                        <select id="filter-order-type" class="form-control">
                            <option value="">-- Semua Tipe --</option>
                            <option value="regular">Online (Regular)</option>
                            <option value="pos">Offline (POS)</option>
                            <option value="distributor">Distributor</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Sumber Pengiriman</label>
                        <select id="filter-source-warehouse" class="form-control select2" style="width: 100%;">
                            <option value="">-- Semua Hub --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" id="btn-reset" class="btn btn-danger btn-block">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Pesanan</h3>
                </div>
                <div class="box-body">
                    <table id="orders-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pembeli</th>
                                <th>Ekspedisi</th>
                                <th>Sumber Pengiriman</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Pembayaran</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.orders.index') }}",
            data: function(d) {
                d.status = $('#filter-status').val();
                d.order_type = $('#filter-order-type').val();
                d.source_warehouse_id = $('#filter-source-warehouse').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
            }
        },
        columns: [
            { data: 'order_info', name: 'order_number' },
            { data: 'buyer_info', name: 'user.name' },
            { data: 'expedition_info', name: 'expedition.name', orderable: false },
            { data: 'hub_info', name: 'sourceWarehouse.name', orderable: false },
            { data: 'total_formatted', name: 'total_amount' },
            { data: 'status_badge', name: 'order_status' },
            { data: 'payment_badge', name: 'payment_status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari (No. Pesanan / Nama):",
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

    // Initialize Datepicker
    $('.datepicker').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        todayHighlight: true,
        orientation: "bottom auto",
        todayBtn: "linked",
        clearBtn: true
    });

    // Initialize Select2 for source warehouse filter
    $('#filter-source-warehouse').select2({
        placeholder: '-- Semua Hub --',
        allowClear: true
    });

    // Filter handlers
    $('#filter-status, #filter-order-type, #filter-source-warehouse').change(function() {
        table.draw();
    });

    $('#filter-date-from, #filter-date-to').change(function() {
        table.draw();
    });

    // Reset button
    $('#btn-reset').click(function() {
        $('#filter-status').val('');
        $('#filter-order-type').val('');
        $('#filter-source-warehouse').val(null).trigger('change');
        $('#filter-date-from').datepicker('update', '{{ date('Y-m-d') }}');
        $('#filter-date-to').datepicker('update', '{{ date('Y-m-d') }}');
        table.draw();
    });
});
</script>
@endpush
