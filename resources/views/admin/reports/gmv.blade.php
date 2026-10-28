@extends('layouts.admin')

@section('title', 'Laporan & Analisis GMV')
@section('page-title', 'Laporan & Analisis GMV')
@section('page-description', 'Gross Merchandise Value, Diskon, Net Revenue, dan Tren Penjualan')

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">Laporan GMV</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
    .kpi-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        padding: 16px 18px;
        margin-bottom: 20px;
        border-left: 4px solid #3c8dbc;
        transition: transform .2s, box-shadow .2s;
        position: relative;
        overflow: hidden;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(0,0,0,0.1);
    }
    .kpi-card.kpi-maroon { border-left-color: #6f1715; }
    .kpi-card.kpi-green  { border-left-color: #00a65a; }
    .kpi-card.kpi-orange { border-left-color: #f39c12; }
    .kpi-card.kpi-purple { border-left-color: #605ca8; }
    .kpi-card.kpi-blue   { border-left-color: #00c0ef; }
    .kpi-card.kpi-teal   { border-left-color: #39cccc; }

    .kpi-title {
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: .5px;
        color: #777;
        margin-bottom: 6px;
    }
    .kpi-value {
        font-size: 22px;
        font-weight: 800;
        color: #222;
        margin-bottom: 4px;
        line-height: 1.2;
    }
    .kpi-subtext {
        font-size: 11px;
        color: #888;
    }
    .kpi-badge {
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 12px;
        font-weight: 600;
        display: inline-block;
    }
    .kpi-badge.badge-up { background: #e8f5e9; color: #2e7d32; }
    .kpi-badge.badge-down { background: #ffebee; color: #c62828; }

    .kpi-icon-bg {
        position: absolute;
        right: 12px;
        top: 14px;
        font-size: 44px;
        color: rgba(0,0,0,0.05);
        pointer-events: none;
    }

    .filter-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        padding: 18px 20px;
        margin-bottom: 22px;
    }
    .quick-date-btn {
        margin-right: 4px;
        margin-bottom: 6px;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 4px;
    }
    .quick-date-btn.active {
        background-color: #6f1715 !important;
        border-color: #6f1715 !important;
        color: #fff !important;
    }

    .waterfall-step {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 6px;
        background: #f7f9fa;
        margin: 4px;
        font-size: 12px;
        border: 1px solid #e2e8f0;
    }
    .waterfall-step strong { display: block; font-size: 14px; color: #333; }
    .waterfall-op {
        font-size: 16px;
        font-weight: 700;
        color: #888;
        display: inline-block;
        margin: 0 6px;
        vertical-align: middle;
    }

    .chart-container {
        position: relative;
        height: 310px;
        width: 100%;
    }
    .table-ranking tr th {
        font-size: 11px;
        text-transform: uppercase;
        background: #f8fafc;
    }
    .table-ranking tr td {
        vertical-align: middle !important;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
    <!-- FILTER BAR -->
    <div class="filter-card">
        <form id="filter-form" method="GET" action="{{ route('admin.reports.gmv.index') }}">
            <div class="row">
                <div class="col-md-12" style="margin-bottom: 12px;">
                    <strong style="color: #444; font-size: 13px;"><i class="fa fa-calendar-check-o text-maroon"></i> Periode Cepat:</strong>&nbsp;&nbsp;
                    <button type="button" class="btn btn-default btn-xs quick-date-btn" data-range="today">Hari Ini</button>
                    <button type="button" class="btn btn-default btn-xs quick-date-btn" data-range="yesterday">Kemarin</button>
                    <button type="button" class="btn btn-default btn-xs quick-date-btn" data-range="last7">7 Hari Terakhir</button>
                    <button type="button" class="btn btn-default btn-xs quick-date-btn" data-range="last30">30 Hari Terakhir</button>
                    <button type="button" class="btn btn-default btn-xs quick-date-btn active" data-range="this_month">Bulan Ini</button>
                    <button type="button" class="btn btn-default btn-xs quick-date-btn" data-range="last_month">Bulan Lalu</button>
                    <button type="button" class="btn btn-default btn-xs quick-date-btn" data-range="this_year">Tahun Ini</button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 600;">DARI TANGGAL</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" name="start_date" id="filter-start-date" class="form-control datepicker input-sm" 
                                   value="{{ $metrics['period']['start_date'] }}" autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 600;">SAMPAI TANGGAL</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" name="end_date" id="filter-end-date" class="form-control datepicker input-sm" 
                                   value="{{ $metrics['period']['end_date'] }}" autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 600;">STATUS PESANAN</label>
                        <select name="status" id="filter-status" class="form-control input-sm">
                            <option value="valid" {{ ($filters['status'] ?? 'valid') === 'valid' ? 'selected' : '' }}>Valid (Kecuali Batal)</option>
                            <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>Sudah Lunas (Paid)</option>
                            <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved Finance</option>
                            <option value="all" {{ ($filters['status'] ?? '') === 'all' ? 'selected' : '' }}>Semua Transaksi</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 600;">GUDANG / HUB</label>
                        <select name="warehouse_id" id="filter-warehouse" class="form-control input-sm select2" style="width: 100%;">
                            <option value="all">-- Semua Hub --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ ($filters['warehouse_id'] ?? '') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} ({{ $wh->kode_hub ?: 'HUB' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 600;">CHANNEL</label>
                        <select name="channel" id="filter-channel" class="form-control input-sm">
                            <option value="all" {{ ($filters['channel'] ?? 'all') === 'all' ? 'selected' : '' }}>Semua Channel</option>
                            <option value="online" {{ ($filters['channel'] ?? '') === 'online' ? 'selected' : '' }}>Online (Reguler)</option>
                            <option value="sales" {{ ($filters['channel'] ?? '') === 'sales' ? 'selected' : '' }}>Direct Sales (Sales Code)</option>
                            <option value="pos" {{ ($filters['channel'] ?? '') === 'pos' ? 'selected' : '' }}>Offline (POS / Walk-in)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 6px;">
                <div class="col-md-4 col-sm-6">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 11px; font-weight: 600;">SALESPERSON / KODE SALES</label>
                        <select name="sales_code" id="filter-sales-code" class="form-control input-sm select2" style="width: 100%;">
                            <option value="all">-- Semua Sales --</option>
                            @foreach($salesList as $sales)
                                <option value="{{ $sales->sales_code }}" {{ ($filters['sales_code'] ?? '') === $sales->sales_code ? 'selected' : '' }}>
                                    {{ $sales->name }} ({{ $sales->sales_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 11px; font-weight: 600;">TIPE PELANGGAN</label>
                        <select name="user_role" id="filter-user-role" class="form-control input-sm">
                            <option value="all">-- Semua Tipe Pelanggan --</option>
                            <option value="buyer" {{ ($filters['user_role'] ?? '') === 'buyer' ? 'selected' : '' }}>Buyer / Retail</option>
                            <option value="reseller" {{ ($filters['user_role'] ?? '') === 'reseller' ? 'selected' : '' }}>Reseller</option>
                            <option value="distributor" {{ ($filters['user_role'] ?? '') === 'distributor' ? 'selected' : '' }}>Distributor</option>
                            <option value="driippreneur" {{ ($filters['user_role'] ?? '') === 'driippreneur' ? 'selected' : '' }}>Affiliator / DRiiPPreneur</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-5 col-sm-12 text-right" style="padding-top: 22px;">
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-apply-filter">
                        <i class="fa fa-filter"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.reports.gmv.index') }}" class="btn btn-default btn-sm" style="margin-left: 5px;">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                    <a href="#" id="btn-export-excel" class="btn btn-success btn-sm" style="margin-left: 5px;">
                        <i class="fa fa-file-excel-o"></i> Export Excel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- WATERFALL FORMULA BANNER -->
    <div class="box box-solid" style="background: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 22px;">
        <div class="box-body" style="padding: 14px 20px;">
            <div style="font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 6px;">
                <i class="fa fa-calculator text-primary"></i> Struktur Rekonsiliasi GMV & Pendapatan (Periode: <span id="label-period">{{ $metrics['period']['label'] }}</span>)
            </div>
            <div style="overflow-x: auto; white-space: nowrap;">
                <div class="waterfall-step">
                    <span style="color: #666;">Gross GMV (Kotor)</span>
                    <strong id="waterfall-gross" style="color: #6f1715;">Rp {{ number_format($metrics['gross_gmv'], 0, ',', '.') }}</strong>
                </div>
                <span class="waterfall-op">-</span>
                <div class="waterfall-step">
                    <span style="color: #666;">Total Diskon / Kupon</span>
                    <strong id="waterfall-discount" style="color: #d9534f;">Rp {{ number_format($metrics['effective_discount'], 0, ',', '.') }}</strong>
                </div>
                <span class="waterfall-op">=</span>
                <div class="waterfall-step" style="background: #eef9f2; border-color: #c3e6cb;">
                    <span style="color: #155724; font-weight: 600;">Net Merchandise Value (NMV)</span>
                    <strong id="waterfall-nmv" style="color: #28a745;">Rp {{ number_format($metrics['net_merchandise_value'], 0, ',', '.') }}</strong>
                </div>
                <span class="waterfall-op">+</span>
                <div class="waterfall-step">
                    <span style="color: #666;">Ongkir & PPN</span>
                    <strong id="waterfall-shipping-ppn" style="color: #17a2b8;">Rp {{ number_format($metrics['total_shipping'] + $metrics['estimated_ppn'], 0, ',', '.') }}</strong>
                </div>
                <span class="waterfall-op">=</span>
                <div class="waterfall-step" style="background: #f0f7ff; border-color: #b8daff;">
                    <span style="color: #004085; font-weight: 600;">Total Nilai Pesanan</span>
                    <strong id="waterfall-total" style="color: #007bff;">Rp {{ number_format($metrics['total_order_amount'], 0, ',', '.') }}</strong>
                </div>
                <span class="waterfall-op" style="margin: 0 12px;">|</span>
                <div class="waterfall-step" style="background: #fbf5e6; border-color: #ffeeba;">
                    <span style="color: #856404; font-weight: 600;"><i class="fa fa-check-circle"></i> Realized Revenue (Lunas)</span>
                    <strong id="waterfall-realized" style="color: #e08e0b;">Rp {{ number_format($metrics['realized_revenue'], 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI CARDS ROW -->
    <div class="row">
        <!-- Gross GMV -->
        <div class="col-lg-3 col-sm-6">
            <div class="kpi-card kpi-maroon">
                <i class="fa fa-line-chart kpi-icon-bg"></i>
                <div class="kpi-title">Gross GMV (Total Kotor)</div>
                <div class="kpi-value text-maroon" id="kpi-gross-gmv">Rp {{ number_format($metrics['gross_gmv'], 0, ',', '.') }}</div>
                <div class="kpi-subtext">
                    @if($metrics['growth_percentage'] >= 0)
                        <span class="kpi-badge badge-up" id="kpi-growth"><i class="fa fa-arrow-up"></i> +{{ $metrics['growth_percentage'] }}%</span>
                    @else
                        <span class="kpi-badge badge-down" id="kpi-growth"><i class="fa fa-arrow-down"></i> {{ $metrics['growth_percentage'] }}%</span>
                    @endif
                    <span style="margin-left: 4px;">vs periode sebelumnya</span>
                </div>
            </div>
        </div>

        <!-- Net Merchandise Value -->
        <div class="col-lg-3 col-sm-6">
            <div class="kpi-card kpi-green">
                <i class="fa fa-shopping-bag kpi-icon-bg"></i>
                <div class="kpi-title">Net Merchandise Value (NMV)</div>
                <div class="kpi-value text-green" id="kpi-nmv">Rp {{ number_format($metrics['net_merchandise_value'], 0, ',', '.') }}</div>
                <div class="kpi-subtext">
                    Diskon Efektif: <strong class="text-danger" id="kpi-discount">Rp {{ number_format($metrics['effective_discount'], 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <!-- Realized Revenue -->
        <div class="col-lg-3 col-sm-6">
            <div class="kpi-card kpi-orange">
                <i class="fa fa-money kpi-icon-bg"></i>
                <div class="kpi-title">Realized Revenue (Lunas)</div>
                <div class="kpi-value text-yellow" id="kpi-realized">Rp {{ number_format($metrics['realized_revenue'], 0, ',', '.') }}</div>
                <div class="kpi-subtext">
                    <span id="kpi-paid-orders">{{ number_format($metrics['paid_orders_count']) }}</span> pesanan lunas dari <span id="kpi-total-orders">{{ number_format($metrics['total_orders']) }}</span>
                </div>
            </div>
        </div>

        <!-- AOV & Total Unit -->
        <div class="col-lg-3 col-sm-6">
            <div class="kpi-card kpi-purple">
                <i class="fa fa-bar-chart kpi-icon-bg"></i>
                <div class="kpi-title">Average Order Value (AOV)</div>
                <div class="kpi-value" style="color: #605ca8;" id="kpi-aov">Rp {{ number_format($metrics['aov'], 0, ',', '.') }}</div>
                <div class="kpi-subtext">
                    Total Unit Terjual: <strong id="kpi-units">{{ number_format($metrics['total_units_sold']) }}</strong> pcs
                </div>
            </div>
        </div>
    </div>

    <!-- CHARTS ROW -->
    <div class="row">
        <!-- Daily Trend Line Chart -->
        <div class="col-md-8">
            <div class="box box-primary" style="border-radius: 8px;">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-area-chart text-primary"></i> Tren Harian: Gross GMV vs Realized Revenue</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="chart-container">
                        <canvas id="gmvTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warehouse Share Donut Chart -->
        <div class="col-md-4">
            <div class="box box-danger" style="border-radius: 8px;">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-pie-chart text-danger"></i> Kontribusi GMV per Hub</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="chart-container">
                        <canvas id="warehousePieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP PERFORMERS ROW -->
    <div class="row">
        <!-- Top Products -->
        <div class="col-md-6">
            <div class="box box-success" style="border-radius: 8px;">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-trophy text-yellow"></i> Top 5 Produk Kontributor GMV Tertinggi</h3>
                </div>
                <div class="box-body no-padding table-responsive">
                    <table class="table table-striped table-ranking" id="table-top-products">
                        <thead>
                            <tr>
                                <th style="width: 10px;">#</th>
                                <th>Produk</th>
                                <th class="text-center" style="width: 90px;">Qty Terjual</th>
                                <th class="text-right" style="width: 140px;">Gross GMV</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $index => $prod)
                                <tr>
                                    <td><span class="badge {{ $index === 0 ? 'bg-yellow' : 'bg-gray' }}">{{ $index + 1 }}</span></td>
                                    <td>
                                        <strong>{{ $prod['name'] }}</strong><br>
                                        <small class="text-muted">Kode: {{ $prod['code'] ?: '-' }}</small>
                                    </td>
                                    <td class="text-center"><span class="label label-info">{{ number_format($prod['total_qty']) }} pcs</span></td>
                                    <td class="text-right font-weight-bold text-maroon"><strong>{{ $prod['gmv_formatted'] }}</strong></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted" style="padding: 20px;">Tidak ada data produk pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Sales Code -->
        <div class="col-md-6">
            <div class="box box-warning" style="border-radius: 8px;">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-users text-orange"></i> Top 5 Salesperson / Kode Sales</h3>
                </div>
                <div class="box-body no-padding table-responsive">
                    <table class="table table-striped table-ranking" id="table-top-sales">
                        <thead>
                            <tr>
                                <th style="width: 10px;">#</th>
                                <th>Salesperson</th>
                                <th class="text-center" style="width: 90px;">Pesanan</th>
                                <th class="text-right" style="width: 140px;">Gross GMV</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSales as $index => $sl)
                                <tr>
                                    <td><span class="badge {{ $index === 0 ? 'bg-yellow' : 'bg-gray' }}">{{ $index + 1 }}</span></td>
                                    <td>
                                        <strong>{{ $sl['salesperson_name'] }}</strong><br>
                                        <small class="text-muted">Kode: <span class="label label-default">{{ $sl['sales_code'] }}</span></small>
                                    </td>
                                    <td class="text-center"><span class="label label-primary">{{ number_format($sl['orders_count']) }} order</span></td>
                                    <td class="text-right font-weight-bold text-maroon"><strong>{{ $sl['gmv_formatted'] }}</strong></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted" style="padding: 20px;">Tidak ada data transaksi sales pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TRANSACTIONS DATATABLE -->
    <div class="box box-default" style="border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list text-primary"></i> Rincian Transaksi GMV</h3>
            <div class="box-tools pull-right">
                <span class="label label-primary" id="table-info-counter">Server-Side DataTables</span>
            </div>
        </div>
        <div class="box-body table-responsive">
            <table id="gmv-orders-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Hub / Lokasi</th>
                        <th>Kode Sales</th>
                        <th class="text-right">Gross GMV</th>
                        <th class="text-right">Diskon</th>
                        <th class="text-right">Ongkir</th>
                        <th class="text-right">Total Order</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Datepicker
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });

    // Inisialisasi Select2 jika tersedia
    if ($.fn.select2) {
        $('.select2').select2();
    }

    // Helper formatter Rupiah
    function formatRupiah(num) {
        num = Math.round(Number(num) || 0);
        return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Chart.js Instances
    var trendChart = null;
    var pieChart = null;

    var initialTrendData = @json($trend);
    var initialBreakdownData = @json($warehouseBreakdown);

    function initTrendChart(data) {
        var ctx = document.getElementById('gmvTrendChart').getContext('2d');
        if (trendChart) {
            trendChart.destroy();
        }
        trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Gross GMV',
                        data: data.gmv,
                        borderColor: '#6f1715',
                        backgroundColor: 'rgba(111, 23, 21, 0.08)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#6f1715',
                        pointBorderColor: '#fff',
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Realized Revenue (Lunas)',
                        data: data.revenue,
                        borderColor: '#00a65a',
                        backgroundColor: 'rgba(0, 166, 90, 0.04)',
                        borderWidth: 2,
                        pointBackgroundColor: '#00a65a',
                        pointBorderColor: '#fff',
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        borderDash: [4, 4],
                        fill: false,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            callback: function(val) {
                                if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + ' jt';
                                if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + ' rb';
                                return 'Rp ' + val;
                            }
                        },
                        gridLines: { color: 'rgba(0, 0, 0, 0.05)' }
                    }],
                    xAxes: [{
                        gridLines: { display: false }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(tooltipItem, chartData) {
                            var datasetLabel = chartData.datasets[tooltipItem.datasetIndex].label || '';
                            return datasetLabel + ': ' + formatRupiah(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });
    }

    function initWarehousePieChart(breakdown) {
        var ctx = document.getElementById('warehousePieChart').getContext('2d');
        if (pieChart) {
            pieChart.destroy();
        }

        var labels = breakdown.map(function(item) { return item.name; });
        var values = breakdown.map(function(item) { return item.gmv; });
        var bgColors = [
            '#6f1715', '#3c8dbc', '#00a65a', '#f39c12', '#605ca8', '#39cccc', '#d81b60', '#001f3f'
        ];

        pieChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: bgColors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, fontSize: 11 }
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, chartData) {
                            var label = chartData.labels[tooltipItem.index] || '';
                            var val = chartData.datasets[0].data[tooltipItem.index];
                            return label + ': ' + formatRupiah(val);
                        }
                    }
                }
            }
        });
    }

    initTrendChart(initialTrendData);
    initWarehousePieChart(initialBreakdownData);

    // DataTables Server-Side Setup
    var gmvTable = $('#gmv-orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.reports.gmv.datatable') }}",
            data: function(d) {
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
                d.status = $('#filter-status').val();
                d.warehouse_id = $('#filter-warehouse').val();
                d.channel = $('#filter-channel').val();
                d.sales_code = $('#filter-sales-code').val();
                d.user_role = $('#filter-user-role').val();
            }
        },
        order: [[1, 'desc']],
        columns: [
            { data: 'order_number', name: 'order_number' },
            { data: 'created_at', name: 'created_at' },
            { data: 'customer_name', name: 'user.name' },
            { data: 'warehouse_name', name: 'sourceWarehouse.name' },
            { 
                data: 'sales_code', 
                name: 'sales_code',
                render: function(val) {
                    return val ? '<span class="label label-default">' + val + '</span>' : '-';
                }
            },
            { data: 'gross_gmv', name: 'gross_gmv', className: 'text-right' },
            { data: 'discount_amount', name: 'discount_amount', className: 'text-right' },
            { data: 'shipping_cost', name: 'shipping_cost', className: 'text-right' },
            { data: 'total_amount', name: 'total_amount', className: 'text-right' },
            { data: 'status_badge', name: 'payment_status', className: 'text-center', orderable: false, searchable: false },
            { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
        ],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i> Memuat data...',
            lengthMenu: "Tampilkan _MENU_ transaksi",
            zeroRecords: "Tidak ditemukan data yang sesuai kriteria",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ transaksi",
            infoEmpty: "Menampilkan 0 transaksi",
            infoFiltered: "(disaring dari _MAX_ total data)",
            search: "Cari:",
            paginate: {
                first: "Awal",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Akhir"
            }
        }
    });

    // Quick Date Selection Buttons
    $('.quick-date-btn').on('click', function() {
        $('.quick-date-btn').removeClass('active');
        $(this).addClass('active');

        var range = $(this).data('range');
        var now = new Date();
        var start = new Date();
        var end = new Date();

        function formatDate(d) {
            var month = '' + (d.getMonth() + 1);
            var day = '' + d.getDate();
            var year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
        }

        switch(range) {
            case 'today':
                // start and end are today
                break;
            case 'yesterday':
                start.setDate(now.getDate() - 1);
                end.setDate(now.getDate() - 1);
                break;
            case 'last7':
                start.setDate(now.getDate() - 6);
                break;
            case 'last30':
                start.setDate(now.getDate() - 29);
                break;
            case 'this_month':
                start = new Date(now.getFullYear(), now.getMonth(), 1);
                end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                break;
            case 'last_month':
                start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                end = new Date(now.getFullYear(), now.getMonth(), 0);
                break;
            case 'this_year':
                start = new Date(now.getFullYear(), 0, 1);
                end = new Date(now.getFullYear(), 11, 31);
                break;
        }

        $('#filter-start-date').datepicker('update', formatDate(start));
        $('#filter-end-date').datepicker('update', formatDate(end));
        $('#filter-form').trigger('submit');
    });

    // AJAX Form Submission & Metric Refresh
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();

        // Reload DataTables
        gmvTable.ajax.reload();

        // Fetch new summary metrics, charts & ranking via AJAX
        $.ajax({
            url: "{{ route('admin.reports.gmv.index') }}",
            type: "GET",
            data: formData,
            dataType: "json",
            beforeSend: function() {
                $('#btn-apply-filter').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyegarkan...');
            },
            success: function(res) {
                $('#btn-apply-filter').prop('disabled', false).html('<i class="fa fa-filter"></i> Terapkan Filter');
                if (res.success) {
                    var m = res.metrics;

                    // Update Top Cards
                    $('#kpi-gross-gmv').text(formatRupiah(m.gross_gmv));
                    $('#kpi-nmv').text(formatRupiah(m.net_merchandise_value));
                    $('#kpi-discount').text(formatRupiah(m.effective_discount));
                    $('#kpi-realized').text(formatRupiah(m.realized_revenue));
                    $('#kpi-paid-orders').text(m.paid_orders_count.toLocaleString('id-ID'));
                    $('#kpi-total-orders').text(m.total_orders.toLocaleString('id-ID'));
                    $('#kpi-aov').text(formatRupiah(m.aov));
                    $('#kpi-units').text(m.total_units_sold.toLocaleString('id-ID'));

                    // Update Growth Badge
                    var growthHtml = m.growth_percentage >= 0
                        ? '<span class="kpi-badge badge-up"><i class="fa fa-arrow-up"></i> +' + m.growth_percentage + '%</span>'
                        : '<span class="kpi-badge badge-down"><i class="fa fa-arrow-down"></i> ' + m.growth_percentage + '%</span>';
                    $('#kpi-growth').replaceWith(growthHtml);

                    // Update Waterfall
                    $('#label-period').text(m.period.label);
                    $('#waterfall-gross').text(formatRupiah(m.gross_gmv));
                    $('#waterfall-discount').text(formatRupiah(m.effective_discount));
                    $('#waterfall-nmv').text(formatRupiah(m.net_merchandise_value));
                    $('#waterfall-shipping-ppn').text(formatRupiah(m.total_shipping + m.estimated_ppn));
                    $('#waterfall-total').text(formatRupiah(m.total_order_amount));
                    $('#waterfall-realized').text(formatRupiah(m.realized_revenue));

                    // Update Charts
                    initTrendChart(res.trend);
                    initWarehousePieChart(res.warehouse_breakdown);

                    // Update Top Products Table
                    var prodHtml = '';
                    if (res.top_products.length > 0) {
                        res.top_products.forEach(function(p, idx) {
                            var badgeClass = idx === 0 ? 'bg-yellow' : 'bg-gray';
                            prodHtml += '<tr>' +
                                '<td><span class="badge ' + badgeClass + '">' + (idx + 1) + '</span></td>' +
                                '<td><strong>' + p.name + '</strong><br><small class="text-muted">Kode: ' + (p.code || '-') + '</small></td>' +
                                '<td class="text-center"><span class="label label-info">' + p.total_qty.toLocaleString('id-ID') + ' pcs</span></td>' +
                                '<td class="text-right text-maroon"><strong>' + p.gmv_formatted + '</strong></td>' +
                            '</tr>';
                        });
                    } else {
                        prodHtml = '<tr><td colspan="4" class="text-center text-muted" style="padding: 20px;">Tidak ada data produk pada periode ini.</td></tr>';
                    }
                    $('#table-top-products tbody').html(prodHtml);

                    // Update Top Sales Table
                    var salesHtml = '';
                    if (res.top_sales.length > 0) {
                        res.top_sales.forEach(function(s, idx) {
                            var badgeClass = idx === 0 ? 'bg-yellow' : 'bg-gray';
                            salesHtml += '<tr>' +
                                '<td><span class="badge ' + badgeClass + '">' + (idx + 1) + '</span></td>' +
                                '<td><strong>' + s.salesperson_name + '</strong><br><small class="text-muted">Kode: <span class="label label-default">' + s.sales_code + '</span></small></td>' +
                                '<td class="text-center"><span class="label label-primary">' + s.orders_count.toLocaleString('id-ID') + ' order</span></td>' +
                                '<td class="text-right text-maroon"><strong>' + s.gmv_formatted + '</strong></td>' +
                            '</tr>';
                        });
                    } else {
                        salesHtml = '<tr><td colspan="4" class="text-center text-muted" style="padding: 20px;">Tidak ada data transaksi sales pada periode ini.</td></tr>';
                    }
                    $('#table-top-sales tbody').html(salesHtml);
                }
            },
            error: function() {
                $('#btn-apply-filter').prop('disabled', false).html('<i class="fa fa-filter"></i> Terapkan Filter');
            }
        });
    });

    // Export Excel Button
    $('#btn-export-excel').on('click', function(e) {
        e.preventDefault();
        var params = $('#filter-form').serialize();
        var exportUrl = "{{ route('admin.reports.gmv.export') }}?" + params;
        window.location.href = exportUrl;
    });
});
</script>
@endpush
