@extends('layouts.distributor')

@section('title', 'Keranjang')
@section('page-title', 'Keranjang Belanja')
@section('page-description', 'Review pesanan Anda')

@section('breadcrumb')
    <li><a href="{{ route('distributor.orders.products') }}">Order Produk</a></li>
    <li class="active">Keranjang</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Keranjang Belanja</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('distributor.orders.products') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-plus"></i> Tambah Produk
                        </a>
                    </div>
                </div>
                <div class="box-body table-responsive no-padding">
                    @if($carts->count() > 0)
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Gambar</th>
                                    <th>Produk</th>
                                    <th style="width: 120px;">Harga</th>
                                    <th style="width: 120px;">Jumlah</th>
                                    <th style="width: 120px;">Subtotal</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($carts as $cart)
                                    <tr>
                                        <td>
                                            @if($cart->product->image)
                                                <img src="{{ asset('storage/' . $cart->product->image) }}" alt="{{ $cart->product->name }}" style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div style="width: 60px; height: 60px; background: #f4f4f4; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fa fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $cart->product->name }}</strong><br>
                                            <small class="text-muted">
                                                <i class="fa fa-star text-yellow"></i> +{{ number_format($cart->quantity * 5000, 0, ',', '.') }} poin
                                            </small>
                                        </td>
                                        <td>
                                            Rp {{ number_format($cart->display_price ?? Auth::user()->getProductPrice($cart->product), 0, ',', '.') }}
                                            @if(isset($cart->display_price) && $cart->display_price != $cart->product->price)
                                                <br><small style="text-decoration: line-through; color: #999;">Rp {{ number_format($cart->product->price, 0, ',', '.') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('distributor.orders.update-cart', $cart) }}" method="POST" class="form-inline">
                                                @csrf
                                                @method('PUT')
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="quantity" class="form-control" value="{{ $cart->quantity }}" min="1" style="width: 60px;">
                                                    <span class="input-group-btn">
                                                        <button type="submit" class="btn btn-default btn-flat">
                                                            <i class="fa fa-refresh"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </form>
                                        </td>
                                        <td><strong>Rp {{ number_format($cart->display_subtotal ?? (Auth::user()->getProductPrice($cart->product) * $cart->quantity), 0, ',', '.') }}</strong></td>
                                        <td>
                                            <form action="{{ route('distributor.orders.remove-from-cart', $cart) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center" style="padding: 50px;">
                            <i class="fa fa-shopping-cart fa-3x text-muted"></i>
                            <p class="text-muted" style="margin-top: 15px;">Keranjang kosong</p>
                            <a href="{{ route('distributor.orders.products') }}" class="btn btn-warning">
                                <i class="fa fa-plus"></i> Mulai Belanja
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Order Summary -->
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-file-text-o"></i> Ringkasan</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed">
                        <tr>
                            <td>Total Item</td>
                            <td class="text-right"><strong>{{ $totalItems }} item</strong></td>
                        </tr>
                        <tr>
                            <td>Subtotal</td>
                            <td class="text-right"><strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr class="bg-yellow">
                            <td><i class="fa fa-star"></i> Potensi Poin</td>
                            <td class="text-right"><strong>+{{ number_format($potentialPoints, 0, ',', '.') }} poin</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="box-footer">
                    @if($carts->count() > 0)
                        <a href="{{ route('distributor.orders.checkout') }}" class="btn btn-warning btn-lg btn-block">
                            <i class="fa fa-credit-card"></i> Checkout
                        </a>
                    @else
                        <button class="btn btn-default btn-lg btn-block" disabled>
                            <i class="fa fa-credit-card"></i> Checkout
                        </button>
                    @endif
                </div>
            </div>

            <!-- Points Info -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Info Poin</h3>
                </div>
                <div class="box-body">
                    <p><i class="fa fa-star text-yellow"></i> <strong>5.000 poin</strong> per item yang dipesan</p>
                    <p><i class="fa fa-check text-green"></i> Poin akan dikreditkan setelah pesanan selesai</p>
                    <p><i class="fa fa-gift text-purple"></i> Poin dapat ditukarkan dengan berbagai hadiah</p>
                </div>
            </div>
        </div>
    </div>
@endsection

