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
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <!-- Combined Profile and Stats Box -->
                    <div class="box box-warning">
                        <div class="box-body">
                            <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
                                <!-- Profile Name & Actions -->
                                <div class="col-md-3 text-center" style="border-right: 1px solid #eee; padding: 20px;">
                                    <div style="font-size: 50px; color: #f39c12; margin-bottom: 10px;">
                                        <i class="fa fa-building"></i>
                                    </div>
                                    <h3 class="profile-username" style="font-size: 22px; margin-top: 0; font-weight: bold;">{{ $distributor->name }}</h3>
                                    <p class="text-muted" style="margin-bottom: 15px;">
                                        <span class="label label-warning" style="font-size: 12px; padding: 4px 10px; border-radius: 4px;">Distributor</span>
                                    </p>
                                    <div>
                                        <a href="{{ route('admin.distributors.edit', $distributor) }}" class="btn btn-warning btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                                        <a href="{{ route('admin.distributors.index') }}" class="btn btn-default btn-sm btn-flat"><i class="fa fa-arrow-left"></i> Kembali</a>
                                    </div>
                                </div>

                                <!-- Profile Details -->
                                <div class="col-md-5" style="border-right: 1px solid #eee; padding: 20px;">
                                    <ul class="list-group list-group-unbordered mb-0" style="margin-bottom: 0;">
                                        <li class="list-group-item" style="border-top: 0; padding-top: 0;">
                                            <b><i class="fa fa-envelope-o text-muted" style="width: 20px;"></i> Email</b> <a class="pull-right">{{ $distributor->email }}</a>
                                        </li>
                                        <li class="list-group-item">
                                            <b><i class="fa fa-phone text-muted" style="width: 20px;"></i> No. HP</b> <a class="pull-right">{{ $distributor->phone ?? '-' }}</a>
                                        </li>
                                        <li class="list-group-item">
                                            <b><i class="fa fa-tags text-muted" style="width: 20px;"></i> Level Harga</b> <a class="pull-right">{{ $distributor->priceLevel->name ?? 'Harga Normal' }}</a>
                                        </li>
                                        <li class="list-group-item">
                                            <b><i class="fa fa-money text-muted" style="width: 20px;"></i> Cara Bayar</b> 
                                            <a class="pull-right">
                                                @if($distributor->payment_method == 'TOP')
                                                    TOP ({{ $distributor->term_of_payment ?? '0' }} Hari)
                                                @elseif($distributor->payment_method == 'CIA')
                                                    CIA
                                                @else
                                                    -
                                                @endif
                                            </a>
                                        </li>
                                        <li class="list-group-item" style="border-bottom: 0; padding-bottom: 0;">
                                            <b><i class="fa fa-calendar-check-o text-muted" style="width: 20px;"></i> Terdaftar</b> <a class="pull-right">{{ $distributor->created_at->format('d M Y') }}</a>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Quick Stats -->
                                <div class="col-md-4 text-center" style="padding: 20px;">
                                    @php
                                        $totalOrders = \App\Models\Order::where('user_id', $distributor->id)->count();
                                        $totalSales = \App\Models\Order::where('user_id', $distributor->id)->where('payment_status', 'paid')->sum('total_amount');
                                    @endphp
                                    <h4 style="margin-top: 0; margin-bottom: 25px; font-weight: bold; color: #555;">Riwayat Transaksi</h4>
                                    <div class="row">
                                        <div class="col-xs-6" style="border-right: 1px solid #eee;">
                                            <div class="description-block" style="margin-bottom: 0;">
                                                <span class="description-percentage text-green" style="font-size: 28px; display: block; margin-bottom: 5px;"><i class="fa fa-shopping-cart"></i></span>
                                                <h5 class="description-header" style="font-size: 22px; font-weight: bold;">{{ number_format($totalOrders, 0, ',', '.') }}</h5>
                                                <span class="description-text text-muted">TOTAL ORDER</span>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="description-block" style="margin-bottom: 0;">
                                                <span class="description-percentage text-blue" style="font-size: 28px; display: block; margin-bottom: 5px;"><i class="fa fa-money"></i></span>
                                                <h5 class="description-header" style="font-size: 22px; font-weight: bold;">Rp {{ number_format($totalSales ?: 0, 0, ',', '.') }}</h5>
                                                <span class="description-text text-muted">TOTAL PENJUALAN</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <!-- Tabbed Content -->
            <div class="nav-tabs-custom">
                @php $activeTab = request('tab', 'info'); @endphp
                <ul class="nav nav-tabs">
                    <li class="{{ $activeTab == 'info' ? 'active' : '' }}"><a href="{{ route('admin.distributors.show', ['distributor' => $distributor->id, 'tab' => 'info']) }}"><i class="fa fa-info-circle"></i> Info Umum</a></li>
                    <li class="{{ $activeTab == 'stock' ? 'active' : '' }}"><a href="{{ route('admin.distributors.show', ['distributor' => $distributor->id, 'tab' => 'stock']) }}"><i class="fa fa-cubes"></i> Monitoring Stock</a></li>
                    <li class="{{ $activeTab == 'orders' ? 'active' : '' }}"><a href="{{ route('admin.distributors.show', ['distributor' => $distributor->id, 'tab' => 'orders']) }}"><i class="fa fa-shopping-cart"></i> Riwayat Order</a></li>
                    <li class="{{ $activeTab == 'addresses' ? 'active' : '' }}"><a href="{{ route('admin.distributors.show', ['distributor' => $distributor->id, 'tab' => 'addresses']) }}"><i class="fa fa-map-marker"></i> Alamat Pengiriman</a></li>
                    <li class="{{ $activeTab == 'dokumen' ? 'active' : '' }}"><a href="{{ route('admin.distributors.show', ['distributor' => $distributor->id, 'tab' => 'dokumen']) }}"><i class="fa fa-file-text"></i> Dokumen</a></li>
                    <li class="{{ $activeTab == 'staff' ? 'active' : '' }}"><a href="{{ route('admin.distributors.show', ['distributor' => $distributor->id, 'tab' => 'staff']) }}"><i class="fa fa-users"></i> Kelola Staff</a></li>
                    <li class="{{ $activeTab == 'target_belanja' ? 'active' : '' }}"><a href="{{ route('admin.distributors.show', ['distributor' => $distributor->id, 'tab' => 'target_belanja']) }}"><i class="fa fa-bullseye"></i> Target Belanja</a></li>
                    <li class="{{ $activeTab == 'diskon_kategori' ? 'active' : '' }}"><a href="{{ route('admin.distributors.show', ['distributor' => $distributor->id, 'tab' => 'diskon_kategori']) }}"><i class="fa fa-percent"></i> Diskon Kategori</a></li>
                    <li class="pull-right {{ $activeTab == 'danger' ? 'active' : '' }}"><a href="{{ route('admin.distributors.show', ['distributor' => $distributor->id, 'tab' => 'danger']) }}" class="text-red"><i class="fa fa-warning"></i> Danger Zone</a></li>
                </ul>
                <div class="tab-content">
                    <!-- Tab: Info Umum -->
                    <div class="tab-pane {{ $activeTab == 'info' ? 'active' : '' }}" id="tab_info">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="page-header"><i class="fa fa-building"></i> Data Hub</h4>
                                @if($distributor->warehouse)
                                <table class="table table-bordered table-striped">
                                    <tr>
                                        <th width="120">Nama Hub</th>
                                        <td><strong>{{ $distributor->warehouse->name }}</strong></td>
                                    </tr>
                                    @if($distributor->warehouse->kode_hub)
                                    <tr>
                                        <th>Kode Hub</th>
                                        <td><span class="badge bg-purple">{{ $distributor->warehouse->kode_hub }}</span></td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>Lokasi</th>
                                        <td>{{ $distributor->warehouse->full_location }}</td>
                                    </tr>
                                    <tr>
                                        <th>Alamat</th>
                                        <td>{{ $distributor->warehouse->address ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Kode Pos</th>
                                        <td>{{ $distributor->warehouse->postal_code ?? '-' }}</td>
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
                    <div class="tab-pane {{ $activeTab == 'stock' ? 'active' : '' }}" id="tab_stock">
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
                    <div class="tab-pane {{ $activeTab == 'orders' ? 'active' : '' }}" id="tab_orders">
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

                    <!-- Tab: Alamat Pengiriman -->
                    <div class="tab-pane {{ $activeTab == 'addresses' ? 'active' : '' }}" id="tab_addresses">
                        <div class="row" style="margin-bottom: 15px;">
                            <div class="col-md-12">
                                <h4 class="page-header" style="margin-top: 0;"><i class="fa fa-map-marker"></i> Daftar Alamat Pengiriman</h4>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="addresses-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Label / Nama Toko</th>
                                        <th>Penerima & Kontak</th>
                                        <th>Alamat Lengkap</th>
                                        <th>Keterangan (Notes)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab: Dokumen -->
                    <div class="tab-pane {{ $activeTab == 'dokumen' ? 'active' : '' }}" id="tab_dokumen">
                        <div class="row" style="margin-bottom: 15px;">
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalTambahDokumen">
                                    <i class="fa fa-plus"></i> Tambah dokumen
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="documents-table" class="table table-bordered table-striped table-hover" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th width="4%">No</th>
                                        <th>Nama dokumen</th>
                                        <th>Keterangan</th>
                                        <th width="12%">Diperbarui</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab: Kelola Staff -->
                    <div class="tab-pane {{ $activeTab == 'staff' ? 'active' : '' }}" id="tab_staff">
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-success btn-sm pull-right" style="margin-bottom: 15px;" data-toggle="modal" data-target="#addUserModal">
                                    <i class="fa fa-user-plus"></i> Tambah Akun Pengelola
                                </button>
                                <h4 class="page-header"><i class="fa fa-users"></i> Daftar Pengelola & Staff</h4>
                                
                                @php
                                    $staffList = \App\Models\User::where('warehouse_id', $distributor->warehouse_id)
                                        ->where('role', \App\Models\User::ROLE_DISTRIBUTOR)
                                        ->get();
                                @endphp

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Telepon</th>
                                            <th>Level Akses</th>
                                            <th>Tanggal Gabung</th>
                                            <th width="50">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($staffList as $staff)
                                        <tr>
                                            <td><strong>{{ $staff->name }}</strong></td>
                                            <td>{{ $staff->email }}</td>
                                            <td>{{ $staff->phone ?? '-' }}</td>
                                            <td>
                                                @if($staff->sub_role === 'admin')
                                                    <span class="label label-primary">Admin Distributor</span>
                                                @else
                                                    <span class="label label-default">Staff Distributor</span>
                                                @endif
                                            </td>
                                            <td>{{ $staff->created_at->format('d M Y') }}</td>
                                            <td>
                                                @if($staff->id !== $distributor->id)
                                                <form action="{{ route('admin.distributors.remove-user', [$distributor, $staff]) }}" method="POST" onsubmit="return confirm('Hapus akun ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada staff tambahan.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Target Belanja -->
                    <div class="tab-pane {{ $activeTab == 'target_belanja' ? 'active' : '' }}" id="tab_target_belanja">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="page-header"><i class="fa fa-bullseye"></i> Target Belanja per Bulan</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="box box-solid box-primary">
                                            <div class="box-header with-border">
                                                <h3 class="box-title">Pilih Tahun & Brand</h3>
                                            </div>
                                            <div class="box-body">
                                                <div class="form-group">
                                                    <label>Tahun</label>
                                                    <select id="select-target-year" class="form-control">
                                                        <option value="">-- Pilih Tahun --</option>
                                                        @php
                                                            $currentYear = date('Y');
                                                            $years = range($currentYear - 2, $currentYear + 3);
                                                        @endphp
                                                        @foreach($years as $y)
                                                            <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Brand</label>
                                                    <select id="select-target-brand" class="form-control">
                                                        <option value="all">Semua Brand (Akumulasi)</option>
                                                        @foreach($brands ?? [] as $brand)
                                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="box box-solid" id="box-target-form" style="display: none;">
                                            <div class="box-header with-border">
                                                <h3 class="box-title">Form Target Belanja <span id="target-brand-title"></span> Tahun <span id="target-year-title"></span></h3>
                                            </div>
                                            <div class="box-body">
                                                <form action="{{ route('admin.distributors.target-belanja.update', $distributor) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="year" id="input-target-year">
                                                    <input type="hidden" name="brand_id" id="input-target-brand">
                                                    
                                                    <div class="row">
                                                        @php
                                                            $months = [
                                                                '1' => 'Januari', '2' => 'Februari', '3' => 'Maret',
                                                                '4' => 'April', '5' => 'Mei', '6' => 'Juni',
                                                                '7' => 'Juli', '8' => 'Agustus', '9' => 'September',
                                                                '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                                            ];
                                                        @endphp
                                                        @foreach($months as $num => $name)
                                                            <div class="col-md-6">
                                                                <div class="form-group" style="margin-bottom: 25px; position: relative;">
                                                                    <label>{{ $name }}</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-addon">Rp</span>
                                                                        <input type="text" name="targets[{{ $num }}]" id="target-month-{{ $num }}" class="form-control rupiah target-input" data-month="{{ $num }}" placeholder="0">
                                                                    </div>
                                                                    <span class="help-block target-success-msg" id="target-success-{{ $num }}" style="display: none; position: absolute; left: 0; bottom: -22px; margin: 0; font-size: 12px; color: #00a65a;"><i class="fa fa-check"></i> Tersimpan</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        
                                        <div class="callout callout-info" id="info-select-year">
                                            <h4><i class="fa fa-info-circle"></i> Info</h4>
                                            <p>Silakan pilih tahun dan brand di samping untuk mengatur target belanja bulanan.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Diskon Kategori -->
                    <div class="tab-pane {{ $activeTab == 'diskon_kategori' ? 'active' : '' }}" id="tab_diskon_kategori">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="box box-solid box-primary">
                                            <div class="box-header with-border">
                                                <h3 class="box-title">Filter Brand</h3>
                                            </div>
                                            <div class="box-body">
                                                <div class="form-group">
                                                    <label>Pilih Brand</label>
                                                    <select id="select-discount-brand" class="form-control">
                                                        @foreach($brands as $brand)
                                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-8">
                                        <div class="box box-solid box-primary" id="box-discount-form" style="display: none;">
                                            <div class="box-header with-border">
                                                <h3 class="box-title">Daftar Diskon Kategori - <span id="discount-brand-title"></span></h3>
                                            </div>
                                            <form id="form-discount-kategori" action="{{ route('admin.distributors.category-discounts.update', $distributor) }}" method="POST">
                                                @csrf
                                                <div class="box-body table-responsive p-0">
                                                    <p class="text-muted p-3" style="padding: 10px; margin-bottom: 0;">
                                                        <i class="fa fa-info-circle"></i> Isikan nilai diskon dalam persentase (contoh: 15.5 untuk 15.5%). Diskon otomatis tersimpan saat angka diubah.
                                                    </p>
                                                    
                                                    @foreach($brands as $brand)
                                                    <table class="table table-bordered table-striped discount-brand-table" id="discount-table-{{ $brand->id }}" style="display: none;">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 50px;" class="text-center">No</th>
                                                                <th>Nama Kategori</th>
                                                                <th style="width: 200px;">Diskon (%)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if($brand->categories->isEmpty())
                                                                <tr>
                                                                    <td colspan="3" class="text-center text-muted" style="padding: 20px;">
                                                                        <em>Brand ini belum memiliki kategori yang dipetakan. Silakan atur kategori di menu <a href="{{ route('admin.brands.index') }}">Master Brand</a>.</em>
                                                                    </td>
                                                                </tr>
                                                            @else
                                                                @foreach($brand->categories as $idx => $category)
                                                                @php
                                                                    $discRecord = $distributor->categoryDiscounts
                                                                        ->where('brand_id', $brand->id)
                                                                        ->where('category_id', $category->id)
                                                                        ->first();
                                                                    $discValue = $discRecord ? $discRecord->discount_percentage : '';
                                                                @endphp
                                                                <tr>
                                                                    <td class="text-center text-middle">{{ $idx + 1 }}</td>
                                                                    <td class="text-middle"><strong>{{ $category->name }}</strong></td>
                                                                    <td>
                                                                        <div class="input-group">
                                                                            <input type="number" step="0.01" min="0" max="100" class="form-control discount-input" data-brand="{{ $brand->id }}" data-category="{{ $category->id }}" name="discounts[{{ $brand->id }}][{{ $category->id }}]" value="{{ $discValue }}" placeholder="0.00">
                                                                            <span class="input-group-addon">%</span>
                                                                            <span class="input-group-addon discount-success-msg" id="discount-success-{{ $brand->id }}-{{ $category->id }}" style="display: none; background-color: #00a65a; color: white; border-color: #008d4c; padding: 6px 8px;" title="Tersimpan"><i class="fa fa-check"></i></span>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                    @endforeach

                                                </div>
                                            </form>
                                        </div>

                                        <div class="callout callout-info" id="info-select-discount-brand">
                                            <h4><i class="fa fa-info-circle"></i> Info</h4>
                                            <p>Silakan pilih brand di sebelah kiri untuk mengatur diskon pada kategori-kategori produk tersebut.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Danger Zone -->
                    <div class="tab-pane {{ $activeTab == 'danger' ? 'active' : '' }}" id="tab_danger">
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

    <!-- Modal: Tambah dokumen -->
    <div class="modal fade" id="modalTambahDokumen" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.distributors.documents.store', $distributor) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-file-text"></i> Tambah dokumen</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama dokumen <span class="text-danger">*</span></label>
                            <input type="text" name="nama_dokumen" class="form-control" required maxlength="255" value="{{ old('nama_dokumen') }}">
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3" maxlength="5000" placeholder="Opsional">{{ old('keterangan') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Berkas <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.gif,.webp,.zip,.rar" onchange="validateFileSize(this)">
                            <p class="help-block small">Maks. 10 MB. PDF, Office, gambar, ZIP/RAR.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Edit dokumen -->
    <div class="modal fade" id="modalEditDokumen" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formEditDokumen" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-edit"></i> Ubah dokumen</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama dokumen <span class="text-danger">*</span></label>
                            <input type="text" name="nama_dokumen" id="edit_doc_nama" class="form-control" required maxlength="255">
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea name="keterangan" id="edit_doc_keterangan" class="form-control" rows="3" maxlength="5000"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Ganti berkas</label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.gif,.webp,.zip,.rar" onchange="validateFileSize(this)">
                            <p class="help-block small">Kosongkan jika tidak mengganti file. Maks. 10 MB.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.distributors.add-user', $distributor) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-user-plus"></i> Tambah Staff Distributor</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                        </div>
                        <div class="form-group">
                            <label for="sub_role">Level Akses <span class="text-danger">*</span></label>
                            <select class="form-control" id="sub_role" name="sub_role" required>
                                <option value="admin">Admin Distributor</option>
                                <option value="staff">Staff Distributor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone">No. Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-save"></i> Simpan Staff
                        </button>
                    </div>
                </form>
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
function validateFileSize(input) {
    if (input.files && input.files[0]) {
        var max_size = 10 * 1024 * 1024; // 10MB
        if (input.files[0].size > max_size) {
            alert("Ukuran file maksimal adalah 10 MB. Silakan pilih file yang lebih kecil.");
            input.value = ""; // Clear the input
        }
    }
}

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
            infoEmpty: "Data kosong",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Tidak ada data yang cocok",
            emptyTable: "Tidak ada data produk/stock",
            paginate: {
                first: "Awal",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Akhir"
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
            { data: 'customer_info', name: 'user.name' },
            { data: 'total_amount_formatted', name: 'total_amount' },
            { data: 'order_status_badge', name: 'order_status' },
            { data: 'payment_status_badge', name: 'payment_status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'desc']],
        pageLength: 15,
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari Pesanan:",
            lengthMenu: "Tampilkan _MENU_ pesanan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pesanan",
            infoEmpty: "Tidak ada pesanan",
            emptyTable: "Belum ada riwayat pesanan",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Filter handlers for orders
    $('#btn-filter-orders').click(function() {
        ordersTable.ajax.reload();
    });

    $('#filter-order-status, #filter-payment-status').change(function() {
        ordersTable.draw();
    });

    // Addresses DataTable
    var addressesTable = $('#addresses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.distributors.show', $distributor) }}",
            data: function(d) {
                d.type = 'addresses';
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'store_name_display', name: 'store_name' },
            { data: 'recipient_info', name: 'recipient_name' },
            { data: 'full_address_display', name: 'address_detail' },
            { data: 'notes_display', name: 'notes' }
        ],
        order: [[1, 'asc']],
        pageLength: 10,
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari Alamat:",
            lengthMenu: "Tampilkan _MENU_ alamat",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ alamat",
            infoEmpty: "Tidak ada alamat",
            emptyTable: "Belum ada alamat pengiriman",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Dokumen distributor
    var documentsTable = $('#documents-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.distributors.show', $distributor) }}",
            data: function(d) {
                d.type = 'documents';
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nama_display', name: 'nama_dokumen' },
            { data: 'keterangan_display', name: 'keterangan' },
            { data: 'updated_display', name: 'updated_at', searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        order: [[3, 'desc']],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari:",
            lengthMenu: "Tampil _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_",
            infoEmpty: "Data kosong",
            emptyTable: "Belum ada dokumen",
            paginate: { first: "Awal", last: "Akhir", next: ">", previous: "<" }
        }
    });

    $(document).on('click', '.btn-edit-distributor-doc', function() {
        var $btn = $(this);
        $('#formEditDokumen').attr('action', $btn.attr('data-update-url'));
        $('#edit_doc_nama').val($btn.attr('data-nama'));
        $('#edit_doc_keterangan').val($btn.attr('data-keterangan'));
        $('#modalEditDokumen').modal('show');
    });

    // Target Belanja
    var targetBelanjaData = @json($targetBelanjaData ?? []);
    
    function updateTargetBelanjaForm() {
        var year = $('#select-target-year').val();
        var brandId = $('#select-target-brand').val();
        var brandText = $('#select-target-brand option:selected').text();
        
        if (year && brandId) {
            $('#input-target-year').val(year);
            $('#input-target-brand').val(brandId);
            $('#target-year-title').text(year);
            $('#target-brand-title').text(brandId === 'all' ? '(Akumulasi Semua Brand)' : '- ' + brandText);
            
            // Reset all inputs to empty and set readonly based on brandId
            for(var i=1; i<=12; i++) {
                var $input = $('#target-month-' + i);
                $input.val('');
                if (brandId === 'all') {
                    $input.prop('readonly', true);
                    $input.attr('placeholder', 'Otomatis (Akumulasi)');
                } else {
                    $input.prop('readonly', false);
                    $input.attr('placeholder', '0');
                }
            }
            $('.target-success-msg').hide();
            
            // Fill existing data if any
            if (targetBelanjaData[year]) {
                if (brandId === 'all') {
                    var accumulated = {};
                    for (var bId in targetBelanjaData[year]) {
                        var bData = targetBelanjaData[year][bId];
                        for (var monthStr in bData) {
                            if (!accumulated[monthStr]) accumulated[monthStr] = 0;
                            accumulated[monthStr] += parseFloat(bData[monthStr]) || 0;
                        }
                    }
                    for (var monthStr in accumulated) {
                        var monthInt = parseInt(monthStr, 10);
                        if (accumulated[monthStr] > 0) {
                            $('#target-month-' + monthInt).val(formatRupiah(accumulated[monthStr].toString()));
                        }
                    }
                } else {
                    if (targetBelanjaData[year][brandId]) {
                        var yearBrandData = targetBelanjaData[year][brandId];
                        for(var monthStr in yearBrandData) {
                            var monthInt = parseInt(monthStr, 10);
                            $('#target-month-' + monthInt).val(formatRupiah(parseFloat(yearBrandData[monthStr]).toString()));
                        }
                    }
                }
            }
            
            $('#info-select-year').hide();
            $('#box-target-form').show();
        } else {
            $('#box-target-form').hide();
            $('#info-select-year').show();
        }
    }
    
    $('#select-target-year, #select-target-brand').change(function() {
        updateTargetBelanjaForm();
    });

    $('.rupiah').on('keyup', function(e) {
        $(this).val(formatRupiah($(this).val()));
    });
    
    $('.target-input').on('blur', function() {
        var $input = $(this);
        var month = $input.data('month');
        var amount = $input.val();
        var year = $('#input-target-year').val();
        var brandId = $('#input-target-brand').val();
        var $successMsg = $('#target-success-' + month);
        
        $successMsg.hide();
        
        if (!year || !brandId) return;

        var data = {
            _token: '{{ csrf_token() }}',
            year: year,
            brand_id: brandId,
            targets: {}
        };
        data.targets[month] = amount;

        $.ajax({
            url: "{{ route('admin.distributors.target-belanja.update', $distributor) }}",
            type: "POST",
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $successMsg.fadeIn().delay(3000).fadeOut();
                    
                    if (!targetBelanjaData[year]) {
                        targetBelanjaData[year] = {};
                    }
                    if (!targetBelanjaData[year][brandId]) {
                        targetBelanjaData[year][brandId] = {};
                    }
                    // Ensure proper formatting for month string like '01'
                    var monthStr = month < 10 ? '0' + month : '' + month;
                    targetBelanjaData[year][brandId][monthStr] = amount ? amount.replace(/\./g, '') : 0;
                }
            },
            error: function(xhr) {
                console.error(xhr);
                alert('Gagal menyimpan data target belanja. Silakan coba lagi.');
            }
        });
    });

    function formatRupiah(angka, prefix) {
        var number_string = angka.toString().replace(/[^,\d]/g, ''),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            var separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? prefix + rupiah : '');
    }

    // Trigger on load if there's a selected value
    if ($('#select-target-year').val()) {
        $('#select-target-year').trigger('change');
    }

    // Diskon Kategori - Filter Brand
    $('#select-discount-brand').change(function() {
        var brandId = $(this).val();
        var brandName = $(this).find('option:selected').text();
        
        if (brandId) {
            $('#info-select-discount-brand').hide();
            $('#box-discount-form').show();
            $('#discount-brand-title').text(brandName);
            
            $('.discount-brand-table').hide();
            $('#discount-table-' + brandId).show();
        } else {
            $('#info-select-discount-brand').show();
            $('#box-discount-form').hide();
        }
    });

    // Cek apakah ada brand pertama yang bisa otomatis di load
    if ($('#select-discount-brand').length) {
        $('#select-discount-brand').trigger('change');
    }

    // Auto save untuk Diskon Kategori
    $('.discount-input').off('change').on('change', function() {
        var $input = $(this);
        var brandId = $input.data('brand');
        var categoryId = $input.data('category');
        var val = $input.val();
        
        var $form = $('#form-discount-kategori');
        var url = $form.attr('action');
        var token = $form.find('input[name="_token"]').val();
        
        var data = {
            _token: token,
            discounts: {}
        };
        data.discounts[brandId] = {};
        data.discounts[brandId][categoryId] = val;
        
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function(response) {
                if(response.success) {
                    var $msg = $('#discount-success-' + brandId + '-' + categoryId);
                    $msg.fadeIn().delay(2000).fadeOut();
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Diskon berhasil disimpan');
                    }
                }
            },
            error: function() {
                alert('Gagal menyimpan diskon, silakan coba lagi.');
            }
        });
    });
});
</script>
@endpush

