@extends('layouts.driippreneur')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard DRiiPPreneur')
@section('page-description', 'Selamat datang di panel DRiiPPreneur')

@section('breadcrumb')
    <li class="active">Dashboard</li>
@endsection

@section('content')
    @if(!$warehouse)
    <div class="callout callout-warning">
        <h4><i class="fa fa-info-circle"></i> Akun Belum Aktif</h4>
        <p>Akun DRiiPPreneur Anda belum terhubung ke Hub manapun. Silakan hubungi admin untuk mengaktifkan akun Anda.</p>
    </div>
    @else
    <!-- Info boxes -->
    <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-purple"><i class="fa fa-cubes"></i></span>
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
            <div class="box box-solid bg-purple">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-building"></i> Hub Anda</h3>
                </div>
                <div class="box-body">
                    <h4 style="margin-top: 0;">{{ $warehouse->name }}</h4>
                    <p><i class="fa fa-map-marker"></i> {{ $warehouse->full_location }}</p>
                    @if($warehouse->phone)
                        <p><i class="fa fa-phone"></i> {{ $warehouse->phone }}</p>
                    @endif
                    <span class="label {{ $warehouse->is_active ? 'label-success' : 'label-danger' }}">
                        {{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
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
                        <tr>
                            <th>Bergabung</th>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Stock Updates -->
        <div class="col-md-8">
            <div class="box box-purple">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-clock-o"></i> Update Stock Terbaru</h3>
                    <div class="box-tools">
                        <a href="{{ route('driippreneur.stock.index') }}" class="btn btn-box-tool">
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
                <a href="{{ route('driippreneur.stock.index', ['filter' => 'low']) }}">Lihat daftar produk</a></p>
            </div>
            @endif
        </div>
    </div>
    @endif
@endsection

