@extends('layouts.admin')

@section('title', 'Detail Distributor')
@section('page-title', 'Detail Distributor')
@section('page-description', 'Informasi lengkap Distributor')

@section('breadcrumb')
    <li><a href="{{ route('admin.distributors.index') }}">Distributor</a></li>
    <li class="active">Detail</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-3">
            <!-- Profile Box -->
            <div class="box box-warning">
                <div class="box-body box-profile">
                    <img class="profile-user-img img-responsive img-circle" src="{{ asset('adminlte/img/user2-160x160.jpg') }}" alt="User profile picture">
                    <h3 class="profile-username text-center">{{ $distributor->name }}</h3>
                    <p class="text-muted text-center">
                        <span class="label label-warning">Distributor</span>
                    </p>

                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>Email</b> <a class="pull-right">{{ $distributor->email }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>No. HP</b> <a class="pull-right">{{ $distributor->phone ?? '-' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Level Harga</b> <a class="pull-right">{{ $distributor->priceLevel->name ?? 'Harga Normal' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Terdaftar</b> <a class="pull-right">{{ $distributor->created_at->format('d M Y') }}</a>
                        </li>
                    </ul>

                    <div class="row">
                        <div class="col-xs-12">
                            <a href="{{ route('admin.distributors.edit', $distributor) }}" class="btn btn-warning btn-block">
                                <i class="fa fa-edit"></i> Edit Profil
                            </a>
                            <a href="{{ route('admin.distributors.index') }}" class="btn btn-default btn-block">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            @php
                $totalOrders = \App\Models\Order::where('user_id', $distributor->id)->count();
                $totalSales = \App\Models\Order::where('user_id', $distributor->id)->where('payment_status', 'paid')->sum('total_amount');
            @endphp
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Quick Stats</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-green">
                                    <i class="fa fa-shopping-cart"></i>
                                </span>
                                <h5 class="description-header">{{ number_format($totalOrders, 0, ',', '.') }}</h5>
                                <span class="description-text">TOTAL ORDER</span>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="description-block">
                                <span class="description-percentage text-blue">
                                    <i class="fa fa-money"></i>
                                </span>
                                <h5 class="description-header">Rp {{ number_format($totalSales ?: 0, 0, ',', '.') }}</h5>
                                <span class="description-text">TOTAL PENJUALAN</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Tabbed Content -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_info" data-toggle="tab"><i class="fa fa-info-circle"></i> Info Umum</a></li>
                    <li><a href="#tab_stock" data-toggle="tab"><i class="fa fa-cubes"></i> Monitoring Stock</a></li>
                    <li><a href="#tab_orders" data-toggle="tab"><i class="fa fa-shopping-cart"></i> Riwayat Order</a></li>
                    <li class="pull-right"><a href="#tab_danger" data-toggle="tab" class="text-red"><i class="fa fa-warning"></i> Danger Zone</a></li>
                </ul>
                <div class="tab-content">
                    <!-- Tab: Info Umum -->
                    <div class="tab-pane active" id="tab_info">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="page-header"><i class="fa fa-building"></i> Data Hub</h4>
                                @if($distributor->warehouse)
                                <table class="table table-bordered table-striped">
                                    <tr>
                                        <th width="120">Nama Hub</th>
                                        <td><strong>{{ $distributor->warehouse->name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Lokasi</th>
                                        <td>{{ $distributor->warehouse->full_location }}</td>
                                    </tr>
                                    <tr>
                                        <th>Alamat</th>
                                        <td>{{ $distributor->warehouse->address ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Telepon</th>
                                        <td>{{ $distributor->warehouse->phone ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($distributor->warehouse->is_active)
                                                <span class="label label-success">Aktif</span>
                                            @else
                                                <span class="label label-danger">Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                @else
                                <div class="alert alert-warning">
                                    <i class="icon fa fa-warning"></i> Hub tidak ditemukan.
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h4 class="page-header"><i class="fa fa-bar-chart"></i> Ringkasan Stock</h4>
                                @if($distributor->warehouse && $stockStats)
                                <div class="row">
                                    <div class="col-sm-6 col-xs-12">
                                        <div class="info-box bg-aqua">
                                            <span class="info-box-icon"><i class="fa fa-cubes"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Produk</span>
                                                <span class="info-box-number">{{ number_format($stockStats->total_products ?: 0, 0, ',', '.') }}</span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: 100%"></div>
                                                </div>
                                                <span class="progress-description text-white">Item Produk</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xs-12">
                                        <div class="info-box bg-green">
                                            <span class="info-box-icon"><i class="fa fa-database"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Unit</span>
                                                <span class="info-box-number">{{ number_format($stockStats->total_stock ?: 0, 0, ',', '.') }}</span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: 100%"></div>
                                                </div>
                                                <span class="progress-description text-white">Total Qty Stock</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xs-12">
                                        <div class="info-box bg-blue">
                                            <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Nilai Inventory</span>
                                                <span class="info-box-number">Rp {{ number_format($stockStats->total_value ?: 0, 0, ',', '.') }}</span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: 100%"></div>
                                                </div>
                                                <span class="progress-description text-white">Estimasi Nilai Barang</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Stock Monitoring -->
                    <div class="tab-pane" id="tab_stock">
                        <div class="row" style="margin-bottom: 10px;">
                            <div class="col-md-12 text-right">
                                <form action="{{ route('admin.distributors.sync-products', $distributor) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Sinkronisasi produk akan menambahkan semua produk aktif yang belum ada di stock warehouse dengan stock 0. Lanjutkan?');">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fa fa-refresh"></i> Sinkronisasi Produk
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="stock-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="50px">Gambar</th>
                                        <th>Kode</th>
                                        <th>Nama Produk</th>
                                        <th>Brand/Kategori</th>
                                        <th>Harga</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab: Riwayat Order -->
                    <div class="tab-pane" id="tab_orders">
                        <div class="row" style="margin-bottom: 15px;">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select id="filter-order-status" class="form-control select2" style="width: 100%;">
                                        <option value="">Semua Status</option>
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
                                    <label>Bayar</label>
                                    <select id="filter-payment-status" class="form-control select2" style="width: 100%;">
                                        <option value="">Semua Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                        <option value="failed">Failed</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Dari</label>
                                            <div class="input-group date">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <input type="text" id="filter-start-date" class="form-control datepicker" placeholder="dd-mm-yyyy" value="{{ date('01-m-Y') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Sampai</label>
                                            <div class="input-group date">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <input type="text" id="filter-end-date" class="form-control datepicker" placeholder="dd-mm-yyyy" value="{{ date('d-m-Y') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 text-right">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="button" id="btn-filter-orders" class="btn btn-primary">
                                        <i class="fa fa-filter"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.orders.index', ['user_id' => $distributor->id]) }}" class="btn btn-info">
                                        <i class="fa fa-external-link"></i> Semua
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="orders-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>No. Pesanan</th>
                                        <th>Tanggal</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status Pesanan</th>
                                        <th>Status Pembayaran</th>
                                        <th width="80px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab: Danger Zone -->
                    <div class="tab-pane" id="tab_danger">
                        <div class="callout callout-danger">
                            <h4>Zona Berbahaya</h4>
                            <p>Menghapus Distributor akan menghapus akun dan hub-nya secara permanen. Aksi ini tidak dapat dibatalkan.</p>
                            <br>
                            <form action="{{ route('admin.distributors.destroy', $distributor) }}" method="POST" onsubmit="return confirm('PERINGATAN: Aksi ini tidak dapat dibatalkan. Lanjutkan hapus?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-lg">
                                    <i class="fa fa-trash"></i> Hapus Akun & Hub Distributor
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
    .dataTables_filter { display: none; }
    .box-profile { margin-bottom: 20px; }
    .description-block { padding: 10px 0; }
    .description-header { margin: 5px 0; font-size: 20px; }
    .table-condensed th { padding: 8px 5px; font-size: 13px; }
    .table-condensed td { padding: 8px 5px; font-size: 13px; }
    .box-body .row { margin-bottom: 0; }
    .box-body .row:last-child { margin-bottom: 0; }
    .datepicker { z-index: 1151 !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    if ($.fn.select2) {
        $('.select2').select2();
    }

    // Initialize Datepicker
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true
    });
    
    @if($distributor->warehouse)
    // Stock DataTable
    $('#stock-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.distributors.show', $distributor) }}",
            data: function(d) {
                d.type = 'stock';
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'product_image', name: 'product.image', orderable: false, searchable: false },
            { data: 'product_code', name: 'product.code' },
            { data: 'product_name', name: 'product.name' },
            { data: 'product_info', name: 'product.brand.name', orderable: false },
            { data: 'product_price', name: 'product.price' },
            { data: 'stock_badge', name: 'stock', orderable: true }
        ],
        order: [[6, 'desc']],
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
            emptyTable: "Tidak ada stock tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });
    @endif

    // Orders DataTable
    var ordersTable = $('#orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.distributors.show', $distributor) }}",
            data: function(d) {
                d.type = 'orders';
                d.status = $('#filter-order-status').val();
                d.payment_status = $('#filter-payment-status').val();
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_number_display', name: 'order_number' },
            { data: 'order_date', name: 'created_at' },
            { data: 'customer_info', name: 'user.name', orderable: false },
            { data: 'total_amount_formatted', name: 'total_amount' },
            { data: 'order_status_badge', name: 'order_status', orderable: false },
            { data: 'payment_status_badge', name: 'payment_status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Tidak ada data yang cocok",
            emptyTable: "Tidak ada order tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });

    // Filter handlers for orders
    $('#btn-filter-orders').click(function() {
        ordersTable.draw();
    });

    $('#filter-order-status, #filter-payment-status').change(function() {
        ordersTable.draw();
    });
});
</script>
@endpush

