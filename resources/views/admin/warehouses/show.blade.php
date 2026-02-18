@extends('layouts.admin')

@section('title', 'Detail Hub')
@section('page-title', 'Detail Hub')
@section('page-description', 'Detail informasi dan stock hub')

@section('breadcrumb')
    <li><a href="{{ route('admin.warehouses.index') }}">Hub</a></li>
    <li class="active">Detail</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <!-- Hub Info -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Informasi Hub</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-warning btn-xs">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-condensed">
                        <tr>
                            <th>Nama</th>
                            <td><strong>{{ $warehouse->name }}</strong></td>
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

            <!-- Statistics -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bar-chart"></i> Statistik Stock</h3>
                </div>
                <div class="box-body">
                    <div class="info-box bg-aqua">
                        <span class="info-box-icon"><i class="fa fa-cubes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Jenis Produk</span>
                            <span class="info-box-number">{{ $stocks->total() }}</span>
                        </div>
                    </div>
                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-archive"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Unit Stock</span>
                            <span class="info-box-number">{{ number_format($warehouse->total_stock) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sync Products -->
            @if($availableProducts->count() > 0)
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-refresh"></i> Sync Produk</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">Tambahkan semua produk aktif ke hub ini dengan stock awal 0.</p>
                    <p><strong>{{ $availableProducts->count() }}</strong> produk belum ada di hub ini.</p>
                </div>
                <div class="box-footer">
                    <form action="{{ route('admin.warehouses.sync-products', $warehouse) }}" method="POST" onsubmit="return confirm('Tambahkan semua produk dengan stock 0?');">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fa fa-refresh"></i> Sync Semua Produk
                        </button>
                    </form>
                </div>
            </div>

            <!-- Add Product Form -->
            <div class="box box-info collapsed-box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-plus"></i> Tambah Produk Manual</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
                <form action="{{ route('admin.warehouses.add-stock', $warehouse) }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label for="product_id">Pilih Produk</label>
                            <select class="form-control" id="product_id" name="product_id" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($availableProducts as $product)
                                    <option value="{{ $product->id }}">{{ $product->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="stock">Jumlah Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" value="0" min="0" required>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-info btn-block">
                            <i class="fa fa-plus"></i> Tambah ke Hub
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-check"></i> Semua Produk Tersinkron</h3>
                </div>
                <div class="box-body">
                    <p class="text-success"><i class="fa fa-check-circle"></i> Semua produk aktif sudah ada di hub ini.</p>
                </div>
            </div>
            @endif

            <!-- Hub Users -->
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-users"></i> Staff Hub</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    @if($warehouse->users->count() > 0)
                        <ul class="list-group">
                            @foreach($warehouse->users as $user)
                                <li class="list-group-item">
                                    <div class="pull-right">
                                        <form action="{{ route('admin.warehouses.remove-user', [$warehouse, $user]) }}" method="POST" style="display: inline;" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <i class="fa fa-user text-muted"></i> 
                                    <strong>{{ $user->name }}</strong><br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center">Belum ada staff di hub ini.</p>
                    @endif
                </div>
                <div class="box-footer">
                    <button type="button" class="btn btn-success btn-sm btn-block" data-toggle="modal" data-target="#addUserModal">
                        <i class="fa fa-plus"></i> Tambah Staff
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Stock List -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cubes"></i> Daftar Stock Produk</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('admin.warehouses.show', $warehouse) }}" class="form-inline" style="margin-bottom: 15px;">
                        <div class="input-group" style="width: 100%;">
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Cari produk berdasarkan nama..." 
                                   value="{{ request('search') }}"
                                   style="border-radius: 4px 0 0 4px;">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="submit" style="border-radius: 0 4px 4px 0;">
                                    <i class="fa fa-search"></i> Cari
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="btn btn-default" title="Reset">
                                        <i class="fa fa-times"></i>
                                    </a>
                                @endif
                            </span>
                        </div>
                    </form>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="60">Gambar</th>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th width="100">Stock</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stocks as $stock)
                                <tr>
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
                                            <br><span class="label label-warning">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($stock->product->price, 0, ',', '.') }}</td>
                                    <td>
                                        @if($stock->stock <= 10)
                                            <span class="badge bg-red">{{ number_format($stock->stock) }}</span>
                                        @elseif($stock->stock <= 50)
                                            <span class="badge bg-yellow">{{ number_format($stock->stock) }}</span>
                                        @else
                                            <span class="badge bg-green">{{ number_format($stock->stock) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#updateStockModal{{ $stock->id }}" title="Update Stock">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.warehouses.remove-stock', [$warehouse, $stock]) }}" method="POST" style="display: inline-block;" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Update Stock Modal -->
                                <div class="modal fade" id="updateStockModal{{ $stock->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.warehouses.update-stock', [$warehouse, $stock]) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title">Update Stock - {{ $stock->product->display_name }}</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="stock{{ $stock->id }}">Jumlah Stock</label>
                                                        <input type="number" class="form-control" id="stock{{ $stock->id }}" name="stock" value="{{ $stock->stock }}" min="0" required>
                                                        <p class="help-block">Stock saat ini: <strong>{{ number_format($stock->stock) }}</strong></p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Update Stock</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <p class="text-muted" style="padding: 20px 0;">
                                            <i class="fa fa-inbox fa-3x"></i><br><br>
                                            Belum ada produk di hub ini.<br>
                                            Tambahkan produk menggunakan form di sebelah kiri.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($stocks->hasPages() || request('search'))
                <div class="box-footer clearfix">
                    <div class="pull-left">
                        @if(request('search'))
                            <p class="text-muted">
                                Menampilkan {{ $stocks->total() }} hasil untuk "<strong>{{ request('search') }}</strong>"
                                @if($stocks->hasPages())
                                    ({{ $stocks->firstItem() }} - {{ $stocks->lastItem() }})
                                @endif
                            </p>
                        @else
                            <p class="text-muted">Menampilkan {{ $stocks->firstItem() }} sampai {{ $stocks->lastItem() }} dari {{ $stocks->total() }} produk</p>
                        @endif
                    </div>
                    <div class="pull-right">
                        {{ $stocks->appends(request()->query())->links('pagination::simple-bootstrap-3') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.warehouses.add-user', $warehouse) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-user-plus"></i> Tambah Staff Hub</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <p class="help-block">Email digunakan untuk login ke sistem hub.</p>
                        </div>
                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                            <p class="help-block">Minimal 8 karakter.</p>
                        </div>
                        <div class="form-group">
                            <label for="phone">No. Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
