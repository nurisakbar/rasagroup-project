@extends('layouts.shop')

@section('title', 'Keranjang Belanja Distributor')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('distributor.orders.products') }}">Pesan ke Pusat</a>
            <span></span> Keranjang
        </div>
    </div>
</div>

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4">
                    @include('buyer.partials.sidebar')
                </div>
                <div class="col-lg-8">
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom p-4">
                                    <h3 class="mb-0">Keranjang Belanja Distributor</h3>
                                    <p class="text-muted font-sm">Item yang Anda pilih untuk dipesan ke pusat.</p>
                                </div>
                                <div class="card-body p-4">
                                    @if($carts->isEmpty())
                                        <div class="text-center py-5">
                                            <i class="fi-rs-shopping-cart text-muted fs-1 mb-3 d-block"></i>
                                            <h5>Keranjang Anda kosong.</h5>
                                            <a href="{{ route('distributor.orders.products') }}" class="btn btn-brand btn-sm rounded-pill mt-3">Mulai Belanja</a>
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table font-sm">
                                                <thead>
                                                    <tr class="main-heading">
                                                        <th class="pl-20">Produk</th>
                                                        <th>Harga Unit</th>
                                                        <th>Jumlah</th>
                                                        <th>Subtotal</th>
                                                        <th class="text-end pr-20">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($carts as $cart)
                                                        <tr>
                                                            <td class="image product-thumbnail pl-20" width="80">
                                                                <img src="{{ asset($cart->product->image_url) }}" alt="{{ $cart->product->display_name }}" class="border-radius-10">
                                                            </td>
                                                            <td class="product-des product-name">
                                                                <h6 class="mb-5"><a href="#" class="text-heading">{{ $cart->product->display_name }}</a></h6>
                                                                <p class="font-xs text-muted">{{ $cart->product->code }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="font-sm fw-bold">Rp {{ number_format($cart->display_price, 0, ',', '.') }}</p>
                                                            </td>
                                                            <td>
                                                                <form action="{{ route('distributor.orders.update-cart', $cart) }}" method="POST" class="d-flex align-items-center gap-2">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="number" name="quantity" value="{{ $cart->quantity }}" min="1" class="form-control form-control-sm" style="width: 60px;">
                                                                    <button type="submit" class="btn btn-sm btn-brand rounded-pill px-2 py-1"><i class="fi-rs-refresh"></i></button>
                                                                </form>
                                                            </td>
                                                            <td>
                                                                <p class="font-sm text-brand fw-bold">Rp {{ number_format($cart->display_subtotal, 0, ',', '.') }}</p>
                                                            </td>
                                                            <td class="text-end pr-20">
                                                                <form action="{{ route('distributor.orders.remove-from-cart', $cart) }}" method="POST" onsubmit="return confirm('Hapus item ini?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-muted"><i class="fi-rs-trash"></i></button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="divider-2 mt-20 mb-20"></div>

                                        <div class="row">
                                            <div class="col-lg-6 col-md-12">
                                                <div class="p-3 bg-light border-radius-10">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="font-sm">Total Unit</span>
                                                        <span class="font-sm fw-bold">{{ number_format($totalItems) }} unit</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="font-sm">Estimasi Poin</span>
                                                        <span class="text-brand font-sm fw-bold">+{{ number_format($potentialPoints) }} Poin</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-12">
                                                <div class="p-3 bg-light border-radius-10 text-end">
                                                    <h6 class="text-muted mb-2 font-sm">Subtotal</h6>
                                                    <h3 class="text-brand mb-4">Rp {{ number_format($subtotal, 0, ',', '.') }}</h3>
                                                    <a href="{{ route('distributor.orders.checkout') }}" class="btn btn-brand rounded-pill w-100">Lanjut ke Checkout <i class="fi-rs-arrow-right ml-10"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer bg-white border-top p-4">
                                    <a href="{{ route('distributor.orders.products') }}" class="btn btn-secondary btn-sm rounded-pill px-4"><i class="fi-rs-arrow-small-left mr-5"></i> Lanjut Belanja</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
