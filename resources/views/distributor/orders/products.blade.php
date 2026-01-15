@extends('layouts.distributor')

@section('title', 'Order Produk')
@section('page-title', 'Order Produk')
@section('page-description', 'Pilih produk untuk dipesan')

@section('breadcrumb')
    <li class="active">Order Produk</li>
@endsection

@section('content')
    <!-- Search -->
    <div class="box box-default">
        <div class="box-body">
            <form action="{{ route('distributor.orders.products') }}" method="GET" class="form-inline">
                <div class="form-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="{{ request('search') }}" style="width: 300px;">
                </div>
                <button type="submit" class="btn btn-default">
                    <i class="fa fa-search"></i> Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('distributor.orders.products') }}" class="btn btn-default">
                        <i class="fa fa-times"></i> Reset
                    </a>
                @endif
                <a href="{{ route('distributor.orders.cart') }}" class="btn btn-warning pull-right">
                    <i class="fa fa-shopping-cart"></i> Keranjang
                </a>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row">
        @forelse($products as $product)
            <div class="col-md-3 col-sm-4 col-xs-6">
                <div class="box box-widget widget-user-2" style="margin-bottom: 15px;">
                    <div class="widget-user-header bg-white" style="padding: 10px;">
                        @if($product->image)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-responsive" style="width: 100%; height: 150px; object-fit: cover;">
                        @else
                            <div style="width: 100%; height: 150px; background: #f4f4f4; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="box-footer" style="padding: 10px;">
                        <h5 style="margin: 5px 0; font-size: 14px; height: 36px; overflow: hidden;">{{ $product->name }}</h5>
                        <p style="margin: 5px 0; color: #f39c12; font-weight: bold;">
                            Rp {{ number_format(Auth::user()->getProductPrice($product), 0, ',', '.') }}
                            @if(Auth::user()->priceLevel && Auth::user()->getProductPrice($product) != $product->price)
                                <br><small style="text-decoration: line-through; color: #999; font-weight: normal;">Rp {{ number_format($product->price, 0, ',', '.') }}</small>
                            @endif
                        </p>
                        <form action="{{ route('distributor.orders.add-to-cart') }}" method="POST" class="form-inline" style="margin-top: 10px;">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <div class="input-group input-group-sm" style="width: 100%;">
                                <input type="number" name="quantity" class="form-control" value="1" min="1" style="width: 60px;">
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-warning btn-flat">
                                        <i class="fa fa-cart-plus"></i>
                                    </button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-xs-12">
                <div class="box box-body">
                    <p class="text-center text-muted" style="padding: 50px 0;">
                        <i class="fa fa-inbox fa-3x"></i><br><br>
                        Tidak ada produk ditemukan.
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="text-center">
        {{ $products->appends(request()->query())->links() }}
    </div>
@endsection

