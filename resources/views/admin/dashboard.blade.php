@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Control panel')

@section('breadcrumb')
    <li class="active">Dashboard</li>
@endsection

@section('content')
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ $totalProducts }}</h3>
                    <p>Total Produk</p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
                <a href="{{ route('admin.products.index') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $totalOrders }}</h3>
                    <p>Total Pesanan</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $pendingOrders }}</h3>
                    <p>Pesanan Pending</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $totalBuyers }}</h3>
                    <p>Total Pembeli</p>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <!-- /.row -->

    <!-- This Month Statistics -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box" style="background-color: #3c8dbc !important;">
                <div class="inner">
                    <h3 style="color: white;">Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}</h3>
                    <p style="color: white;">Pendapatan Bulan Ini</p>
                    <small style="color: rgba(255,255,255,0.8);">
                        <i class="fa fa-calendar"></i> {{ now()->format('F Y') }}
                    </small>
                </div>
                <div class="icon" style="color: rgba(255,255,255,0.3);">
                    <i class="ion ion-cash"></i>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer" style="background: rgba(0,0,0,0.2); color: #fff !important;">Lihat Detail <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box" style="background-color: #605ca8 !important;">
                <div class="inner">
                    <h3 style="color: white;">{{ number_format($thisMonthOrders, 0, ',', '.') }}</h3>
                    <p style="color: white;">Order Bulan Ini</p>
                    <small style="color: rgba(255,255,255,0.8);">
                        <i class="fa fa-calendar"></i> {{ now()->format('F Y') }}
                    </small>
                </div>
                <div class="icon" style="color: rgba(255,255,255,0.3);">
                    <i class="ion ion-document-text"></i>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer" style="background: rgba(0,0,0,0.2); color: #fff !important;">Lihat Detail <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- Online Sales -->
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>Rp {{ number_format($thisMonthOnlineRevenue, 0, ',', '.') }}</h3>
                    <p>Penjualan Online</p>
                    <small style="color: rgba(255,255,255,0.8);">
                        <i class="fa fa-globe"></i> {{ number_format($thisMonthOnlineOrders, 0, ',', '.') }} order
                    </small>
                </div>
                <div class="icon">
                    <i class="fa fa-globe"></i>
                </div>
                <a href="{{ route('admin.orders.index', ['order_type' => 'regular']) }}" class="small-box-footer">Lihat Detail <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- Offline Sales -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>Rp {{ number_format($thisMonthOfflineRevenue, 0, ',', '.') }}</h3>
                    <p>Penjualan Offline</p>
                    <small style="color: rgba(255,255,255,0.8);">
                        <i class="fa fa-cash-register"></i> {{ number_format($thisMonthOfflineOrders, 0, ',', '.') }} order
                    </small>
                </div>
                <div class="icon">
                    <i class="fa fa-cash-register"></i>
                </div>
                <a href="{{ route('admin.orders.index', ['order_type' => 'pos']) }}" class="small-box-footer">Lihat Detail <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <!-- /.row -->

    <!-- Order Chart - Full Width -->
    <div class="row">
        <div class="col-lg-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-line-chart"></i> Grafik Jumlah Pemesanan (30 Hari Terakhir)</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <canvas id="orderChart" style="height: 350px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->

    <!-- Orders by Warehouse Chart -->
    <div class="row">
        <div class="col-lg-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bar-chart"></i> Jumlah Pemesanan Berdasarkan Warehouse</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    @if(count($warehouseLabels) > 0)
                        <canvas id="warehouseChart" style="height: 400px;"></canvas>
                    @else
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Belum ada data pemesanan berdasarkan warehouse.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->

    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-8 connectedSortable">
            <!-- Recent Orders -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Pesanan Terbaru</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Pembeli</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td>
                                            {{ $order->order_number }}
                                            @if($order->order_type === 'pos')
                                                <br><span class="label label-info" style="font-size: 9px;"><i class="fa fa-cash-register"></i> POS</span>
                                            @elseif($order->order_type === 'distributor')
                                                <br><span class="label label-warning" style="font-size: 9px;">DIST</span>
                                            @else
                                                <br><span class="label label-primary" style="font-size: 9px;"><i class="fa fa-globe"></i> ONLINE</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->user->name }}</td>
                                        <td>{{ $order->created_at->format('d M Y') }}</td>
                                        <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="label {{ $order->order_status === 'delivered' ? 'label-success' : ($order->order_status === 'cancelled' ? 'label-danger' : 'label-warning') }}">
                                                {{ ucfirst($order->order_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-xs btn-info">Detail</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada pesanan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.Left col -->
        
        <!-- right col -->
        <section class="col-lg-4 connectedSortable">
            <!-- User Statistics -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Statistik User</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-green"><i class="fa fa-users"></i></span>
                                <h5 class="description-header">{{ $totalBuyers }}</h5>
                                <span class="description-text">PEMBELI</span>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="description-block">
                                <span class="description-percentage text-yellow"><i class="fa fa-user"></i></span>
                                <h5 class="description-header">{{ $totalResellers }}</h5>
                                <span class="description-text">RESELLER</span>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 15px;">
                        <div class="col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-blue"><i class="fa fa-building"></i></span>
                                <h5 class="description-header">{{ $totalDistributors }}</h5>
                                <span class="description-text">DISTRIBUTOR</span>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="description-block">
                                <span class="description-percentage text-purple"><i class="fa fa-star"></i></span>
                                <h5 class="description-header">{{ $totalDriippreneurs }}</h5>
                                <span class="description-text">DRIIPPRENEUR</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- right col -->
    </div>
    <!-- /.row (main row) -->

    <!-- Welcome Box -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Selamat Datang, {{ Auth::user()->name }}!</h3>
                </div>
                <div class="box-body">
                    <p>Anda berhasil login sebagai <strong>Admin/Agen</strong>.</p>
                    <p>Gunakan menu di sidebar untuk mengelola produk dan pesanan.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
$(document).ready(function() {
    // Order Chart
    var ctx = document.getElementById('orderChart').getContext('2d');
    var orderChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Jumlah Pemesanan',
                data: @json($chartData),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgb(75, 192, 192)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        stepSize: 1,
                        precision: 0
                    },
                    gridLines: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }],
                xAxes: [{
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }]
            },
            legend: {
                display: true,
                position: 'top'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(tooltipItem, data) {
                        return 'Jumlah: ' + tooltipItem.yLabel + ' pesanan';
                    }
                }
            }
        }
    });

    // Warehouse Orders Chart
    @if(count($warehouseLabels) > 0)
    var ctxWarehouse = document.getElementById('warehouseChart').getContext('2d');
    var warehouseChart = new Chart(ctxWarehouse, {
        type: 'bar',
        data: {
            labels: @json($warehouseLabels),
            datasets: [{
                label: 'Jumlah Pemesanan',
                data: @json($warehouseData),
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(199, 199, 199, 0.8)',
                    'rgba(83, 102, 255, 0.8)',
                    'rgba(255, 99, 255, 0.8)',
                    'rgba(99, 255, 132, 0.8)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(199, 199, 199, 1)',
                    'rgba(83, 102, 255, 1)',
                    'rgba(255, 99, 255, 1)',
                    'rgba(99, 255, 132, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        stepSize: 1,
                        precision: 0
                    },
                    gridLines: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }],
                xAxes: [{
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }]
            },
            legend: {
                display: true,
                position: 'top'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(tooltipItem, data) {
                        return 'Jumlah: ' + tooltipItem.yLabel + ' pesanan';
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endpush

