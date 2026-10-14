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
    .filter-box { margin-bottom: 15px; }
    #orders-table_length { margin-bottom: 15px; }
    .datepicker-dropdown { z-index: 2000 !important; }
</style>
@endpush

@section('content')
    <!-- Filter Box -->
    <div class="box box-default filter-box">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter Pesanan</h3>
            <div class="box-tools pull-right">
                <a href="#" id="btn-export-excel" class="btn btn-success btn-sm" style="margin-right:8px;"><i class="fa fa-file-excel-o"></i> Export Excel</a>
                <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm" style="margin-right:8px;"><i class="fa fa-plus"></i> Input Transaksi</a>
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tahapan Pesanan</label>
                        <select id="filter-tab-status" class="form-control">
                            <option value="">-- Semua Tahapan ({{ number_format($countSemua) }}) --</option>
                            <option value="menunggu_pembayaran" {{ request('tab') == 'menunggu_pembayaran' ? 'selected' : '' }}>Menunggu Pembayaran ({{ number_format($countMenungguPembayaran) }})</option>
                            <option value="menunggu_persetujuan_finance" {{ request('tab') == 'menunggu_persetujuan_finance' ? 'selected' : '' }}>Menunggu Approval Finance ({{ number_format($countMenungguPersetujuanFinance) }})</option>
                            <option value="menunggu_konfirmasi" {{ request('tab') == 'menunggu_konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi Pembayaran ({{ number_format($countMenungguKonfirmasi) }})</option>
                            <option value="sedang_diproses" {{ request('tab') == 'sedang_diproses' ? 'selected' : '' }}>Sedang Diproses ({{ number_format($countSedangDiproses) }})</option>
                            <option value="dikirim" {{ request('tab') == 'dikirim' ? 'selected' : '' }}>Dikirim ({{ number_format($countDikirim) }})</option>
                            <option value="selesai" {{ request('tab') == 'selesai' ? 'selected' : '' }}>Selesai ({{ number_format($countSelesai ?? 0) }})</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-3">
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
                <div class="col-md-3">
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
            </div>
            <div class="row">
                <div class="col-md-3">
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
                <div class="col-md-3">
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
                <div class="col-md-3 col-md-offset-3" style="padding-top: 24px;">
                    <button type="button" id="btn-reset" class="btn btn-default btn-block">
                        <i class="fa fa-refresh"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table Box -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Daftar Pesanan</h3>
                </div>
                <div class="box-body table-responsive">
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

    <!-- Modal Export Excel -->
    <div class="modal fade" id="modalExportExcel" tabindex="-1" role="dialog" aria-labelledby="modalExportExcelLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 6px; overflow: hidden;">
                <form id="form-export-excel" method="GET" action="{{ route('admin.orders.export') }}">
                    <div class="modal-header" style="background: #fcfcfc; border-bottom: 1px solid #eee; padding: 15px 20px;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="modalExportExcelLabel" style="font-weight: 600;">
                            <i class="fa fa-file-excel-o text-success"></i> Export Data Pesanan ke Excel
                        </h4>
                    </div>
                    <div class="modal-body" style="padding: 20px;">
                        <div class="alert alert-info" style="font-size: 12px; margin-bottom: 18px; padding: 10px 12px;">
                            <i class="fa fa-info-circle"></i> Kosongkan pilihan tanggal jika ingin mengunduh <strong>seluruh data transaksi</strong>.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="export-date-from">Dari Tanggal</label>
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" name="date_from" id="export-date-from" class="form-control export-datepicker" placeholder="YYYY-MM-DD" autocomplete="off">
                                    </div>
                                    <p class="help-block text-muted" style="font-size: 11px; margin-top: 4px; margin-bottom: 0;">Kosongkan jika dari awal transaksi.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="export-date-to">Sampai Tanggal</label>
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" name="date_to" id="export-date-to" class="form-control export-datepicker" placeholder="YYYY-MM-DD" autocomplete="off">
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
                d.tab_status = $('#filter-tab-status').val() || '';
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
        drawCallback: function(settings) {
            var json = table.ajax.json();
            if (json && json.counts) {
                $('#filter-tab-status option[value=""]').text('-- Semua Tahapan (' + (json.counts.semua || 0).toLocaleString('id-ID') + ') --');
                $('#filter-tab-status option[value="menunggu_pembayaran"]').text('Menunggu Pembayaran (' + (json.counts.menunggu_pembayaran || 0).toLocaleString('id-ID') + ')');
                $('#filter-tab-status option[value="menunggu_persetujuan_finance"]').text('Menunggu Approval Finance (' + (json.counts.menunggu_persetujuan_finance || 0).toLocaleString('id-ID') + ')');
                $('#filter-tab-status option[value="menunggu_konfirmasi"]').text('Menunggu Konfirmasi Pembayaran (' + (json.counts.menunggu_konfirmasi || 0).toLocaleString('id-ID') + ')');
                $('#filter-tab-status option[value="sedang_diproses"]').text('Sedang Diproses (' + (json.counts.sedang_diproses || 0).toLocaleString('id-ID') + ')');
                $('#filter-tab-status option[value="dikirim"]').text('Dikirim (' + (json.counts.dikirim || 0).toLocaleString('id-ID') + ')');
                $('#filter-tab-status option[value="selesai"]').text('Selesai (' + (json.counts.selesai || 0).toLocaleString('id-ID') + ')');
            }
        },
        order: [[0, 'desc']],
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari (No. Pesanan / Pembeli / Resi):",
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

    // Initialize Datepicker specifically for Export Modal with container attached to modal
    $('.export-datepicker').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        todayHighlight: true,
        orientation: "bottom auto",
        todayBtn: "linked",
        clearBtn: true,
        container: '#modalExportExcel'
    });

    // Initialize Select2 for source warehouse filter
    $('#filter-source-warehouse').select2({
        placeholder: '-- Semua Hub --',
        allowClear: true
    });

    // Filter handlers
    $('#filter-tab-status, #filter-status, #filter-order-type, #filter-source-warehouse').change(function() {
        table.draw();
    });

    $('#filter-date-from, #filter-date-to').change(function() {
        table.draw();
    });

    // Reset button
    $('#btn-reset').click(function() {
        $('#filter-tab-status').val('');
        $('#filter-status').val('');
        $('#filter-order-type').val('');
        $('#filter-source-warehouse').val(null).trigger('change');
        $('#filter-date-from').datepicker('update', '{{ date('Y-m-d') }}');
        $('#filter-date-to').datepicker('update', '{{ date('Y-m-d') }}');
        table.draw();
    });

    $('#btn-export-excel').on('click', function(e) {
        e.preventDefault();
        $('#export-date-from').datepicker('clearDates');
        $('#export-date-to').datepicker('clearDates');
        $('#export-status').val($('#filter-status').val() || '');
        $('#modalExportExcel').modal('show');
    });

    $('#btn-export-today-dates').on('click', function() {
        var today = '{{ date('Y-m-d') }}';
        $('#export-date-from').datepicker('update', today);
        $('#export-date-to').datepicker('update', today);
    });

    $('#btn-export-clear-dates').on('click', function() {
        $('#export-date-from').datepicker('clearDates');
        $('#export-date-to').datepicker('clearDates');
    });

    $('#form-export-excel').on('submit', function() {
        setTimeout(function() {
            $('#modalExportExcel').modal('hide');
        }, 500);
    });
});
</script>
@endpush
