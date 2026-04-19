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
        <div class="col-md-12">
            <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="{{ !request('tab') || request('tab') == 'info' ? 'active' : '' }}">
                        <a href="#tab_info" data-toggle="tab"><i class="fa fa-info-circle"></i> Informasi Hub</a>
                    </li>
                    <li class="{{ request('tab') == 'stock' ? 'active' : '' }}">
                        <a href="#tab_stock" data-toggle="tab"><i class="fa fa-cubes"></i> Informasi Stock</a>
                    </li>
                    <li class="{{ request('tab') == 'staff' ? 'active' : '' }}">
                        <a href="#tab_staff" data-toggle="tab"><i class="fa fa-users"></i> Staff</a>
                    </li>
                    <li class="pull-right">
                        <a href="{{ route('admin.warehouses.index') }}" class="text-muted" style="padding: 10px 15px;"><i class="fa fa-arrow-left"></i> Kembali</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <!-- TAB 1: INFORMASI HUB -->
                    <div class="tab-pane {{ !request('tab') || request('tab') == 'info' ? 'active' : '' }}" id="tab_info">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="page-header">Detail Profil Hub</h4>
                                <table class="table table-bordered table-striped">
                                    <tr>
                                        <th width="30%">Nama Hub</th>
                                        <td><strong>{{ $warehouse->name }}</strong></td>
                                    </tr>
                                    @if($warehouse->kode_hub)
                                    <tr>
                                        <th>Kode Hub QID</th>
                                        <td><span class="badge bg-purple">{{ $warehouse->kode_hub }}</span></td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>Lokasi</th>
                                        <td>{{ $warehouse->full_location }}</td>
                                    </tr>
                                    <tr>
                                        <th>Alamat Lengkap</th>
                                        <td>{{ $warehouse->address ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>No. Telepon</th>
                                        <td>{{ $warehouse->phone ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status Operasional</th>
                                        <td>
                                            @if($warehouse->is_active)
                                                <span class="label label-success">Aktif</span>
                                            @else
                                                <span class="label label-danger">Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <div class="margin-top">
                                    <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-warning">
                                        <i class="fa fa-edit"></i> Edit Informasi Hub
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h4 class="page-header">Statistik Ringkas</h4>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="small-box bg-aqua">
                                            <div class="inner">
                                                <h3>{{ $stocks->total() }}</h3>
                                                <p>Jenis Produk</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fa fa-shopping-cart"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="small-box bg-green">
                                            <div class="inner">
                                                <h3>{{ number_format($warehouse->total_stock) }}</h3>
                                                <p>Total Unit Stock</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fa fa-archive"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="callout callout-info" style="margin-top: 20px;">
                                    <h4><i class="fa fa-info"></i> Sinkronisasi QID Aktif</h4>
                                    <p>Data stok pada hub ini diperbarui secara otomatis dari sistem QID setiap kali halaman ini dibuka.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: INFORMASI STOCK -->
                    <div class="tab-pane {{ request('tab') == 'stock' ? 'active' : '' }}" id="tab_stock">
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-md-12">
                                <form method="GET" action="{{ route('admin.warehouses.show', $warehouse) }}" class="form-horizontal">
                                    <input type="hidden" name="tab" value="stock">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Cari nama produk atau kode produk..." value="{{ request('search') }}">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
                                            @if(request('search'))
                                                <a href="{{ route('admin.warehouses.show', $warehouse) }}?tab=stock" class="btn btn-default">Reset</a>
                                            @endif
                                        </span>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="bg-gray">
                                    <tr>
                                        <th width="60" class="text-center">Gambar</th>
                                        <th>Produk</th>
                                        <th>Kode Produk</th>
                                        <th>Harga</th>
                                        <th width="120" class="text-center">Stock QID</th>
                                        <th width="100" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stocks as $stock)
                                        <tr>
                                            <td class="text-center">
                                                @if($stock->product->image)
                                                    <img src="{{ asset($stock->product->image_url) }}" alt="{{ $stock->product->display_name }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                @else
                                                    <div style="width: 40px; height: 40px; background: #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fa fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $stock->product->name }}</strong>
                                                @if($stock->product->status !== 'active')
                                                    <span class="label label-warning">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td><code>{{ $stock->product->code }}</code></td>
                                            <td>Rp {{ number_format($stock->product->price, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @if($stock->stock <= 10)
                                                    <span class="badge bg-red" style="font-size: 14px; padding: 5px 10px;">{{ number_format($stock->stock) }}</span>
                                                @elseif($stock->stock <= 50)
                                                    <span class="badge bg-yellow" style="font-size: 14px; padding: 5px 10px;">{{ number_format($stock->stock) }}</span>
                                                @else
                                                    <span class="badge bg-green" style="font-size: 14px; padding: 5px 10px;">{{ number_format($stock->stock) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#updateStockModal{{ $stock->id }}" title="Detail/Update">
                                                    <i class="fa fa-pencil"></i>
                                                </button>
                                                <form action="{{ route('admin.warehouses.remove-stock', [$warehouse, $stock]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus produk ini dari daftar stok hub?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Update Stock Modal -->
                                        <div class="modal fade" id="updateStockModal{{ $stock->id }}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.warehouses.update-stock', [$warehouse, $stock]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                            <h4 class="modal-title">Koreksi Stok</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><strong>{{ $stock->product->name }}</strong></p>
                                                            <div class="form-group">
                                                                <label>Jumlah Stok</label>
                                                                <input type="number" class="form-control" name="stock" value="{{ $stock->stock }}" min="0" required>
                                                                <p class="help-block">Data QID terakhir: {{ number_format($stock->stock) }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <div style="padding: 40px;">
                                                    <i class="fa fa-search fa-3x text-muted"></i>
                                                    <p class="text-muted mt-3">Tidak ada data stok yang ditemukan.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-sm-5">
                                <div class="dataTables_info" style="padding-top: 10px;">
                                    Menampilkan {{ $stocks->firstItem() ?? 0 }} sampai {{ $stocks->lastItem() ?? 0 }} dari {{ $stocks->total() }} data
                                </div>
                            </div>
                            <div class="col-sm-7 text-right">
                                {{ $stocks->appends(request()->query())->links('pagination::simple-bootstrap-3') }}
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: STAFF -->
                    <div class="tab-pane {{ request('tab') == 'staff' ? 'active' : '' }}" id="tab_staff">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-right" style="margin-bottom: 15px;">
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addUserModal">
                                        <i class="fa fa-user-plus"></i> Tambah Staff Baru
                                    </button>
                                </div>
                                
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-gray">
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>No. Telepon</th>
                                            <th>Role</th>
                                            <th width="100" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($warehouse->users as $user)
                                            <tr>
                                                <td><strong>{{ $user->name }}</strong></td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->phone ?? '-' }}</td>
                                                <td>
                                                    @if($user->sub_role === 'admin')
                                                        <span class="label label-primary">Admin Hub</span>
                                                    @else
                                                        <span class="label label-default">Staff Hub</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <form action="{{ route('admin.warehouses.remove-user', [$warehouse, $user]) }}" method="POST" onsubmit="return confirm('Hapus staff ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-xs">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Belum ada staff yang terdaftar di hub ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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
                        </div>
                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                                <span class="input-group-btn">
                                    <button class="btn btn-default toggle-password" type="button">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sub_role">Level Akses <span class="text-danger">*</span></label>
                            <select class="form-control" id="sub_role" name="sub_role" required>
                                <option value="admin">Admin Hub</option>
                                <option value="staff">Staff Hub</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone">No. Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-save"></i> Simpan Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('.toggle-password').click(function() {
                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
                var input = $(this).closest('.input-group').find('input');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                } else {
                    input.attr('type', 'password');
                }
            });
        });
    </script>
    @endpush
@endsection
