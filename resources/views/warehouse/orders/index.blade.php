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
    .filter-box { margin-bottom: 0; }
    #orders-table_length { margin-bottom: 15px; }
    .dataTables_filter { float: right; margin-bottom: 15px; }
    .dataTables_filter input { width: 230px !important; display: inline-block; margin-left: 5px; }
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
                <button type="button" class="btn btn-success btn-sm" id="btn-export-excel" style="margin-right:8px;">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </button>
                <a href="{{ route('warehouse.orders.create') }}" class="btn btn-primary btn-sm" style="margin-right:8px;"><i class="fa fa-plus"></i> Input Transaksi</a>
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" id="filter-date-from" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" id="filter-date-to" class="form-control" value="{{ now()->format('Y-m-d') }}">
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

    <!-- Modal Export Excel -->
    <div class="modal fade" id="modalExportExcel" tabindex="-1" role="dialog" aria-labelledby="modalExportExcelLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 6px; overflow: hidden;">
                <form id="form-export-excel" method="GET" action="{{ route('warehouse.orders.export') }}">
                    <div class="modal-header" style="background: #fcfcfc; border-bottom: 1px solid #eee; padding: 15px 20px;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="modalExportExcelLabel" style="font-weight: 600;">
                            <i class="fa fa-file-excel-o text-success"></i> Export Data Pesanan Gudang ke Excel
                        </h4>
                    </div>
                    <div class="modal-body" style="padding: 20px;">
                        <div class="alert alert-info" style="font-size: 12px; margin-bottom: 18px; padding: 10px 12px;">
                            <i class="fa fa-info-circle"></i> Data yang di-export khusus untuk pesanan yang masuk ke <strong>{{ $warehouse->name ?? 'Hub Anda' }}</strong>. Kosongkan pilihan tanggal jika ingin mengunduh <strong>seluruh data transaksi</strong>.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="export-date-from">Dari Tanggal</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="date" name="date_from" id="export-date-from" class="form-control" autocomplete="off">
                                    </div>
                                    <p class="help-block text-muted" style="font-size: 11px; margin-top: 4px; margin-bottom: 0;">Kosongkan jika dari awal transaksi.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="export-date-to">Sampai Tanggal</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="date" name="date_to" id="export-date-to" class="form-control" autocomplete="off">
                                    </div>
                                    <p class="help-block text-muted" style="font-size: 11px; margin-top: 4px; margin-bottom: 0;">Kosongkan jika hingga transaksi terbaru.</p>
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <button type="button" class="btn btn-xs btn-default" id="btn-export-today-dates">
                                <i class="fa fa-calendar-check-o text-primary"></i> Pasang Tanggal Hari Ini
                            </button>
                            <button type="button" class="btn btn-xs btn-default" id="btn-export-clear-dates" style="margin-left: 5px;">
                                <i class="fa fa-eraser text-danger"></i> Kosongkan Tanggal (Semua)
                            </button>
                        </div>

                        <div class="form-group" style="margin-bottom: 5px;">
                            <label for="export-status">Status Pesanan</label>
                            <select name="status" id="export-status" class="form-control">
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
                    <div class="modal-footer" style="background: #fcfcfc; border-top: 1px solid #eee; padding: 12px 20px;">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-download"></i> Download Excel</button>
                    </div>
                </form>
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
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_number_display', name: 'order_number' },
            { data: 'order_date', name: 'created_at' },
            { data: 'customer_info', name: 'user.name', orderable: false },
            { data: 'order_type_badge', name: 'order_type', orderable: false, searchable: false },
            { data: 'items_info', name: 'items', orderable: false, searchable: false },
            { data: 'total_amount_formatted', name: 'total_amount' },
            { data: 'order_status_badge', name: 'order_status', orderable: false, searchable: false },
            { data: 'payment_status_badge', name: 'payment_status', orderable: false, searchable: false },
            { data: 'expedition_info', name: 'expedition.name', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari (No. Pesanan / Customer / Resi):",
            searchPlaceholder: "Ketik untuk mencari...",
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
    $('#filter-order-status, #filter-date-from, #filter-date-to').change(function() {
        table.draw();
    });

    // Reset button
    $('#btn-reset').click(function() {
        $('#filter-order-status').val('');
        $('#filter-date-from').val('{{ now()->format("Y-m-d") }}');
        $('#filter-date-to').val('{{ now()->format("Y-m-d") }}');
        table.draw();
    });

    // Modal Export Excel Handlers
    $('#btn-export-excel').click(function(e) {
        e.preventDefault();
        $('#modalExportExcel').modal('show');
    });

    $('#btn-export-today-dates').click(function() {
        var today = '{{ now()->format("Y-m-d") }}';
        $('#export-date-from').val(today);
        $('#export-date-to').val(today);
    });

    $('#btn-export-clear-dates').click(function() {
        $('#export-date-from').val('');
        $('#export-date-to').val('');
    });
});
</script>
@endpush

