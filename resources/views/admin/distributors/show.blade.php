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
                            <b>Terdaftar</b> <a class="pull-right">{{ $distributor->created_at->format('d M Y') }}</a>
                        </li>
                        @if($distributor->points)
                        <li class="list-group-item">
                            <b>Points</b> <a class="pull-right"><span class="label label-info">{{ number_format($distributor->points, 0, ',', '.') }}</span></a>
                        </li>
                        @endif
                    </ul>

                    <a href="{{ route('admin.distributors.index') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
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
            <!-- Hub Info & Stock Summary in one row -->
            <div class="row">
                <div class="col-md-6">
                    <!-- Hub Info -->
                    @if($distributor->warehouse)
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-building"></i> Hub Distributor</h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-condensed">
                                <tr>
                                    <th width="100">Nama Hub</th>
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
                        </div>
                    </div>
                    @else
                    <div class="callout callout-warning">
                        <h4>Hub Tidak Ditemukan</h4>
                        <p>Distributor ini tidak memiliki hub yang terhubung.</p>
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <!-- Stock Summary Compact -->
                    @if($distributor->warehouse && $stockStats)
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-bar-chart"></i> Ringkasan Stock</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-aqua"><i class="fa fa-cubes"></i></span>
                                        <h5 class="description-header">{{ number_format($stockStats->total_products ?: 0, 0, ',', '.') }}</h5>
                                        <span class="description-text">TOTAL PRODUK</span>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="description-block">
                                        <span class="description-percentage text-green"><i class="fa fa-check"></i></span>
                                        <h5 class="description-header">{{ number_format($stockStats->products_in_stock ?: 0, 0, ',', '.') }}</h5>
                                        <span class="description-text">TERSEDIA</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-xs-6">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-yellow"><i class="fa fa-exclamation-triangle"></i></span>
                                        <h5 class="description-header">{{ number_format($stockStats->products_low_stock ?: 0, 0, ',', '.') }}</h5>
                                        <span class="description-text">STOCK RENDAH</span>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="description-block">
                                        <span class="description-percentage text-red"><i class="fa fa-times"></i></span>
                                        <h5 class="description-header">{{ number_format($stockStats->products_out_of_stock ?: 0, 0, ',', '.') }}</h5>
                                        <span class="description-text">HABIS</span>
                                    </div>
                                </div>
                            </div>
                            <hr style="margin: 10px 0;">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="description-block">
                                        <span class="description-percentage text-blue"><i class="fa fa-database"></i></span>
                                        <h5 class="description-header">{{ number_format($stockStats->total_stock ?: 0, 0, ',', '.') }} unit</h5>
                                        <span class="description-text">TOTAL UNIT STOCK</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="description-block">
                                        <span class="description-percentage text-blue"><i class="fa fa-money"></i></span>
                                        <h5 class="description-header">Rp {{ number_format($stockStats->total_value ?: 0, 0, ',', '.') }}</h5>
                                        <span class="description-text">NILAI TOTAL STOCK</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Stock Monitoring -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cubes"></i> Monitoring Stock</h3>
                    <div class="box-tools">
                        <form action="{{ route('admin.distributors.sync-products', $distributor) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Sinkronisasi produk akan menambahkan semua produk aktif yang belum ada di stock warehouse dengan stock 0. Lanjutkan?');">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fa fa-refresh"></i> Sync Produk
                            </button>
                        </form>
                    </div>
                </div>
                <div class="box-body">
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

            <!-- Order Report -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Laporan Order</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status Pesanan</label>
                                <select id="filter-order-status" class="form-control">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status Pembayaran</label>
                                <select id="filter-payment-status" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="failed">Failed</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <a href="{{ route('admin.orders.index', ['user_id' => $distributor->id]) }}" class="btn btn-info btn-sm">
                                        <i class="fa fa-external-link"></i> Lihat Semua Order
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table id="orders-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>No. Pesanan</th>
                                <th>Tanggal</th>
                                <th>Customer</th>
                                <th>Tipe</th>
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

            <!-- Danger Zone -->
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Zona Berbahaya</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">Menghapus Distributor akan menghapus akun dan hub-nya secara permanen.</p>
                    <form action="{{ route('admin.distributors.destroy', $distributor) }}" method="POST" onsubmit="return confirm('PERINGATAN: Aksi ini tidak dapat dibatalkan. Lanjutkan hapus?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-trash"></i> Hapus Distributor
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
<style>
    .dataTables_filter { display: none; }
    .box-profile { margin-bottom: 20px; }
    .description-block { padding: 10px 0; }
    .description-header { margin: 5px 0; font-size: 20px; }
    .table-condensed th { padding: 8px 5px; font-size: 13px; }
    .table-condensed td { padding: 8px 5px; font-size: 13px; }
    .box-body .row { margin-bottom: 0; }
    .box-body .row:last-child { margin-bottom: 0; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script>
$(document).ready(function() {
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
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_number_display', name: 'order_number' },
            { data: 'order_date', name: 'created_at' },
            { data: 'customer_info', name: 'user.name', orderable: false },
            { data: 'order_type_badge', name: 'order_type', orderable: false },
            { data: 'total_amount_formatted', name: 'total_amount' },
            { data: 'order_status_badge', name: 'order_status', orderable: false },
            { data: 'payment_status_badge', name: 'payment_status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'desc']],
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
    $('#filter-order-status, #filter-payment-status').change(function() {
        ordersTable.draw();
    });
});
</script>
@endpush

