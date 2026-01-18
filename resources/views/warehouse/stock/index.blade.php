@extends('layouts.warehouse')

@section('title', 'Kelola Stock')
@section('page-title', 'Kelola Stock')
@section('page-description', 'Kelola stock produk di hub Anda')

@section('breadcrumb')
    <li class="active">Kelola Stock</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Stock Produk - {{ $warehouse->name }}</h3>
                    <div class="box-tools">
                        <form action="{{ route('warehouse.stock.sync') }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Sync semua produk dengan stock 0?');">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fa fa-refresh"></i> Sync Produk
                            </button>
                        </form>
                    </div>
                </div>
                <!-- Filter -->
                <div class="box-body" style="border-bottom: 1px solid #f4f4f4;">
                    <form action="{{ route('warehouse.stock.index') }}" method="GET" class="form-inline">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="{{ request('search') }}">
                        </div>
                        <div class="form-group">
                            <select name="filter" class="form-control">
                                <option value="">Semua Stock</option>
                                <option value="low" {{ request('filter') == 'low' ? 'selected' : '' }}>Stock Rendah (≤10)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-default">
                            <i class="fa fa-search"></i> Filter
                        </button>
                        @if(request('search') || request('filter'))
                            <a href="{{ route('warehouse.stock.index') }}" class="btn btn-default">
                                <i class="fa fa-times"></i> Reset
                            </a>
                        @endif
                    </form>
                </div>
                <!-- Table -->
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="60">Gambar</th>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th width="120">Stock</th>
                                <th>Terakhir Update</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stocks as $stock)
                                <tr class="{{ $stock->stock <= 10 ? 'danger' : '' }}">
                                    <td>
                                        @if($stock->product->image)
                                            <img src="{{ asset($stock->product->image_url) }}" alt="{{ $stock->product->display_name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        @else
                                            <div style="width: 50px; height: 50px; background: #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fa fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $stock->product->display_name }}</strong>
                                        @if($stock->product->status !== 'active')
                                            <br><span class="label label-warning">Produk Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($stock->product->price, 0, ',', '.') }}</td>
                                    <td>
                                        @if($stock->stock <= 10)
                                            <span class="badge bg-red" style="font-size: 14px;">{{ number_format($stock->stock) }}</span>
                                            @if($stock->stock <= 10)
                                                <br><small class="text-red"><i class="fa fa-warning"></i> Stock rendah!</small>
                                            @endif
                                        @elseif($stock->stock <= 50)
                                            <span class="badge bg-yellow" style="font-size: 14px;">{{ number_format($stock->stock) }}</span>
                                        @else
                                            <span class="badge bg-green" style="font-size: 14px;">{{ number_format($stock->stock) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $stock->updated_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#updateStockModal{{ $stock->id }}">
                                            <i class="fa fa-edit"></i> Update Stock
                                        </button>
                                    </td>
                                </tr>

                                <!-- Update Stock Modal -->
                                <div class="modal fade" id="updateStockModal{{ $stock->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('warehouse.stock.update', $stock) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title">Update Stock - {{ $stock->product->display_name }}</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            @if($stock->product->image)
                                                                <img src="{{ $stock->product->image_url }}" alt="{{ $stock->product->display_name }}" class="img-responsive" style="border-radius: 5px;">
                                                            @endif
                                                        </div>
                                                        <div class="col-md-8">
                                                            <p><strong>{{ $stock->product->display_name }}</strong></p>
                                                            <p class="text-muted">Harga: Rp {{ number_format($stock->product->price, 0, ',', '.') }}</p>
                                                            <hr>
                                                            <div class="form-group">
                                                                <label for="stock{{ $stock->id }}">Jumlah Stock Baru</label>
                                                                <input type="number" class="form-control input-lg" id="stock{{ $stock->id }}" name="stock" value="{{ $stock->stock }}" min="0" required autofocus>
                                                                <p class="help-block">Stock saat ini: <strong>{{ number_format($stock->stock) }}</strong> unit</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa fa-save"></i> Simpan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <p class="text-muted" style="padding: 40px 0;">
                                            <i class="fa fa-inbox fa-3x"></i><br><br>
                                            @if(request('search') || request('filter'))
                                                Tidak ada produk yang sesuai dengan filter.
                                            @else
                                                Belum ada produk di hub ini.<br>
                                                Klik tombol "Sync Produk" untuk menambahkan semua produk.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($stocks->hasPages())
                <div class="box-footer clearfix">
                    <div class="pull-left">
                        <p class="text-muted">Menampilkan {{ $stocks->firstItem() }} - {{ $stocks->lastItem() }} dari {{ $stocks->total() }} produk</p>
                    </div>
                    <div class="pull-right">
                        {{ $stocks->appends(request()->query())->links('pagination::simple-bootstrap-3') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
