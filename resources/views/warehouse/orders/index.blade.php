@extends('layouts.warehouse')

@section('title', 'Laporan Pemesanan')
@section('page-title', 'Laporan Pemesanan')
@section('page-description', 'Daftar pemesanan yang masuk ke warehouse')

@section('breadcrumb')
    <li><a href="{{ route('warehouse.dashboard') }}">Dashboard</a></li>
    <li class="active">Laporan Pemesanan</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
<style>
    .dataTables_filter { display: none; }
    .filter-box { margin-bottom: 0; }
    #orders-table_length { margin-bottom: 15px; }
</style>
@endpush

@section('content')
    <!-- Statistics -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Pesanan</span>
                    <span class="info-box-number">{{ number_format($totalOrders) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number">{{ number_format($pendingOrders) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fa fa-cog"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Processing</span>
                    <span class="info-box-number">{{ number_format($processingOrders) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Revenue</span>
                    <span class="info-box-number">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

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
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Status Pesanan</label>
                        <select id="filter-order-status" class="form-control">
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
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Status Pembayaran</label>
                        <select id="filter-payment-status" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" id="filter-date-from" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" id="filter-date-to" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="button" id="btn-reset" class="btn btn-default">
                        <i class="fa fa-refresh"></i> Reset Filter
                    </button>
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
                                <th width="5%">No</th>
                                <th>No. Pesanan</th>
                                <th>Tanggal</th>
                                <th>Customer</th>
                                <th>Tipe</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status Pesanan</th>
                                <th>Status Pembayaran</th>
                                <th>Ekspedisi</th>
                                <th width="80px">Action</th>
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
<script>
$(document).ready(function() {
    var table = $('#orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('warehouse.orders.index') }}",
            data: function(d) {
                d.order_status = $('#filter-order-status').val();
                d.payment_status = $('#filter-payment-status').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_number_display', name: 'order_number' },
            { data: 'order_date', name: 'created_at' },
            { data: 'customer_info', name: 'user.name', orderable: false },
            { data: 'order_type_badge', name: 'order_type', orderable: false },
            { data: 'items_info', name: 'items', orderable: false },
            { data: 'total_amount_formatted', name: 'total_amount' },
            { data: 'order_status_badge', name: 'order_status', orderable: false },
            { data: 'payment_status_badge', name: 'payment_status', orderable: false },
            { data: 'expedition_info', name: 'expedition.name', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
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

    // Filter handlers
    $('#filter-order-status, #filter-payment-status').change(function() {
        table.draw();
    });

    $('#filter-date-from, #filter-date-to').change(function() {
        table.draw();
    });

    // Reset button
    $('#btn-reset').click(function() {
        $('#filter-order-status').val('');
        $('#filter-payment-status').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        table.draw();
    });
});
</script>
@endpush

