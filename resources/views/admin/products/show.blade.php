@extends('layouts.admin')

@section('title', 'Detail Produk')
@section('page-title', 'Detail Produk')
@section('page-description', 'Detail master data produk')

@section('breadcrumb')
    <li><a href="{{ route('admin.products.index') }}">Produk</a></li>
    <li class="active">Detail</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Informasi Produk</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-responsive" style="max-width: 100%; border-radius: 5px;">
                            @else
                                <div class="text-center" style="padding: 40px; background: #f5f5f5; border-radius: 5px;">
                                    <i class="fa fa-image fa-3x text-muted"></i>
                                    <p class="text-muted">Tidak ada gambar</p>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <table class="table table-bordered">
                                @if($product->code)
                                <tr>
                                    <th width="35%">Kode Produk</th>
                                    <td><span class="label label-info">{{ $product->code }}</span></td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Nama Produk</th>
                                    <td><strong>{{ $product->name }}</strong></td>
                                </tr>
                                @if($product->commercial_name)
                                <tr>
                                    <th>Nama Komersial</th>
                                    <td>{{ $product->commercial_name }}</td>
                                </tr>
                                @endif
                                @if($product->technical_description)
                                <tr>
                                    <th>Deskripsi Teknis</th>
                                    <td>{{ $product->technical_description }}</td>
                                </tr>
                                @endif
                                @if($product->description)
                                <tr>
                                    <th>Deskripsi Lengkap</th>
                                    <td>{{ $product->description }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Produk -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-tags"></i> Detail Produk</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                @if($product->brand)
                                <tr>
                                    <th width="40%">Brand</th>
                                    <td>
                                        <a href="{{ route('admin.brands.edit', $product->brand) }}">
                                            <span class="label label-primary">{{ $product->brand->name }}</span>
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                @if($product->category)
                                <tr>
                                    <th>Kategori</th>
                                    <td>
                                        <a href="{{ route('admin.categories.edit', $product->category) }}">
                                            <span class="label label-default">{{ $product->category->name }}</span>
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                @if($product->size)
                                <tr>
                                    <th>Ukuran (Size)</th>
                                    <td>{{ $product->size }}</td>
                                </tr>
                                @endif
                                @if($product->unit)
                                <tr>
                                    <th>Satuan (UM)</th>
                                    <td>{{ $product->unit }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Harga</th>
                                    <td><strong class="text-green" style="font-size: 1.2em;">Rp {{ number_format($product->price, 0, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Berat</th>
                                    <td>{{ $product->formatted_weight }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="label {{ $product->status === 'active' ? 'label-success' : 'label-danger' }}">
                                            {{ $product->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-clock-o"></i> Info Sistem</h3>
                </div>
                <div class="box-body">
                    <dl>
                        <dt>ID Produk</dt>
                        <dd><small class="text-muted">{{ $product->id }}</small></dd>
                        <dt>Dibuat Oleh</dt>
                        <dd>{{ $product->creator->name ?? 'N/A' }}</dd>
                        <dt>Tanggal Dibuat</dt>
                        <dd>{{ $product->created_at->format('d M Y, H:i') }}</dd>
                        <dt>Terakhir Diupdate</dt>
                        <dd>{{ $product->updated_at->format('d M Y, H:i') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cubes"></i> Stock</h3>
                </div>
                <div class="box-body">
                    <p>Stock produk ini dikelola per Hub.</p>
                    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-success btn-block">
                        <i class="fa fa-building"></i> Lihat Hub
                    </a>
                </div>
            </div>

            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-trash"></i> Hapus Produk</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">Hapus produk ini secara permanen.</p>
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fa fa-trash"></i> Hapus Produk
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
