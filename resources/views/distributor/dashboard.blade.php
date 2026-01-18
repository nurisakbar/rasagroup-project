@extends('layouts.distributor')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Distributor')
@section('page-description', 'Ringkasan informasi distributor Anda')

@section('breadcrumb')
    <li class="active">Dashboard</li>
@endsection

@section('content')
    <!-- Info boxes -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-star"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Poin</span>
                    <span class="info-box-number">{{ number_format($user->points ?? 0) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Jenis Produk</span>
                    <span class="info-box-number">{{ number_format($totalProducts) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-archive"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Unit Stock</span>
                    <span class="info-box-number">{{ number_format($totalStock) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Stock Rendah (≤10)</span>
                    <span class="info-box-number">{{ number_format($lowStockProducts) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Hub Info -->
        <div class="col-md-4">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-building"></i> Info Hub</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed">
                        <tr>
                            <th>Nama Hub</th>
                            <td>{{ $warehouse->name }}</td>
                        </tr>
                        <tr>
                            <th>Lokasi</th>
                            <td>{{ $warehouse->full_location }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $warehouse->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>{{ $warehouse->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($warehouse->is_active)
                                    <span class="label label-success">Aktif</span>
                                @else
                                    <span class="label label-danger">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-user"></i> Profil Anda</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed">
                        <tr>
                            <th>Nama</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>No. HP</th>
                            <td>{{ $user->phone ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Stock Updates -->
        <div class="col-md-8">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-clock-o"></i> Update Stock Terbaru</h3>
                    <div class="box-tools">
                        <a href="{{ route('distributor.stock.index') }}" class="btn btn-box-tool">
                            <i class="fa fa-external-link"></i> Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Stock</th>
                                <th>Terakhir Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentStocks as $stock)
                                <tr>
                                    <td>{{ $stock->product->display_name }}</td>
                                    <td>
                                        @if($stock->stock <= 10)
                                            <span class="badge bg-red">{{ number_format($stock->stock) }}</span>
                                        @elseif($stock->stock <= 50)
                                            <span class="badge bg-yellow">{{ number_format($stock->stock) }}</span>
                                        @else
                                            <span class="badge bg-green">{{ number_format($stock->stock) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $stock->updated_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada data stock</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($lowStockProducts > 0)
            <div class="callout callout-warning">
                <h4><i class="fa fa-warning"></i> Perhatian!</h4>
                <p>Ada <strong>{{ $lowStockProducts }}</strong> produk dengan stock rendah (≤10 unit). 
                <a href="{{ route('distributor.stock.index', ['filter' => 'low']) }}">Lihat daftar produk</a></p>
            </div>
            @endif
        </div>
    </div>

    <!-- Sales Revenue Chart -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-line-chart"></i> Grafik Omset Penjualan (30 Hari Terakhir)</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <!-- Summary Cards -->
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-4">
                            <div class="info-box bg-green">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Omset Bulan Ini</span>
                                    <span class="info-box-number">Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ now()->format('F Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-blue">
                                <span class="info-box-icon"><i class="fa fa-shopping-cart"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Order Bulan Ini</span>
                                    <span class="info-box-number">{{ number_format($thisMonthOrders, 0, ',', '.') }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Pesanan yang sudah dibayar
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-yellow">
                                <span class="info-box-icon"><i class="fa fa-calculator"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Rata-rata per Order</span>
                                    <span class="info-box-number">
                                        Rp {{ $thisMonthOrders > 0 ? number_format($thisMonthRevenue / $thisMonthOrders, 0, ',', '.') : '0' }}
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Omset dibagi jumlah order
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart -->
                    <canvas id="revenueChart" style="height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Revenue Chart
    var ctx = document.getElementById('revenueChart');
    if (ctx) {
        var revenueChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Omset Penjualan',
                    data: @json($chartData),
                    borderColor: '#00a65a',
                    backgroundColor: 'rgba(0, 166, 90, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#00a65a',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: '#008d4c',
                    pointHoverBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Omset: Rp ' + context.parsed.y.toLocaleString('id-ID');
                            },
                            title: function(context) {
                                return 'Tanggal: ' + context[0].label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toFixed(0) + 'Rb';
                                }
                                return 'Rp ' + value.toLocaleString('id-ID');
                            },
                            font: {
                                size: 11
                            },
                            padding: 10
                        },
                        title: {
                            display: true,
                            text: 'Omset (Rupiah)',
                            font: {
                                size: 13,
                                weight: 'bold'
                            },
                            padding: {
                                top: 10,
                                bottom: 10
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            font: {
                                size: 10
                            }
                        },
                        title: {
                            display: true,
                            text: 'Tanggal',
                            font: {
                                size: 13,
                                weight: 'bold'
                            },
                            padding: {
                                top: 10,
                                bottom: 5
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                elements: {
                    line: {
                        borderJoinStyle: 'round'
                    }
                }
            }
        });
    }
});
</script>
@endpush

