@extends('layouts.warehouse')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Hub')
@section('page-description', 'Ringkasan informasi hub Anda')

@section('breadcrumb')
    <li class="active">Dashboard</li>
@endsection

@section('content')
    <!-- Info boxes -->
    <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Jenis Produk</span>
                    <span class="info-box-number">{{ number_format($totalProducts) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-archive"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Unit Stock</span>
                    <span class="info-box-number">{{ number_format($totalStock) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 col-xs-12">
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
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-building"></i> Info Hub</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed">
                        <tr>
                            <th>Nama</th>
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
        </div>

        <!-- Recent Stock Updates -->
        <div class="col-md-8">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-clock-o"></i> Update Stock Terbaru</h3>
                    <div class="box-tools">
                        <a href="{{ route('warehouse.stock.index') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-cubes"></i> Lihat Semua Stock
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
                                    <td>{{ $stock->product->name }}</td>
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
                <a href="{{ route('warehouse.stock.index', ['filter' => 'low']) }}">Lihat daftar produk</a></p>
            </div>
            @endif
        </div>
    </div>
@endsection
