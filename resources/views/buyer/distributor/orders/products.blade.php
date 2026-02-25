@extends('layouts.shop')

@section('title', 'Pesan Produk ke Pusat')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Pesan ke Pusat
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
                                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0">Pesan Produk ke Pusat</h3>
                                        <p class="text-muted font-sm">Pilih produk untuk menambah stok warehouse Anda.</p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('distributor.orders.cart') }}" class="btn btn-sm btn-warning rounded-pill px-4 text-dark">
                                            <i class="fi-rs-shopping-cart mr-5"></i> Keranjang
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <!-- Search -->
                                    <form action="{{ route('distributor.orders.products') }}" method="GET" class="mb-4">
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="search" class="form-control font-sm" placeholder="Cari produk..." value="{{ request('search') }}">
                                            <button class="btn btn-brand btn-sm" type="submit">Cari</button>
                                        </div>
                                    </form>

                                    <!-- Products Grid -->
                                    <div class="row g-3">
                                        @forelse($products as $product)
                                            <div class="col-md-4 col-sm-6">
                                                <div class="product-cart-wrap mb-10 pb-10 shadow-none border">
                                                    <div class="product-img-action-wrap">
                                                        <div class="product-img product-img-zoom p-2">
                                                            <a href="#">
                                                                @if($product->image_url)
                                                                    <img class="default-img border-radius-10" src="{{ asset($product->image_url) }}" alt="{{ $product->display_name }}" style="height: 150px; object-fit: cover;">
                                                                @else
                                                                    <div class="bg-light border-radius-10 d-flex align-items-center justify-content-center" style="height: 150px;">
                                                                        <i class="fi-rs-shopping-bag text-muted fs-1"></i>
                                                                    </div>
                                                                @endif
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="product-content-wrap p-3">
                                                        <div class="product-category">
                                                            <a href="#">{{ $product->category->name ?? 'General' }}</a>
                                                        </div>
                                                        <h2 class="fs-6 mt-1 mb-2"><a href="#">{{ Str::limit($product->display_name, 35) }}</a></h2>
                                                        <div class="product-card-bottom">
                                                            <div class="product-price">
                                                                <span class="fs-6">Rp {{ number_format(Auth::user()->getProductPrice($product), 0, ',', '.') }}</span>
                                                            </div>
                                                        </div>
                                                        <form action="{{ route('distributor.orders.add-to-cart') }}" method="POST" class="mt-3">
                                                            @csrf
                                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                            <div class="d-flex gap-2 align-items-center">
                                                                <input type="number" name="quantity" class="form-control form-control-sm font-sm" value="1" min="1" style="width: 60px;">
                                                                <button type="submit" class="btn btn-sm btn-brand rounded-pill w-100 font-xs">
                                                                    <i class="fi-rs-cart-plus mr-5"></i> Tambah
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12 text-center py-5">
                                                <p class="text-muted">Tidak ada produk ditemukan.</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    <!-- Pagination -->
                                    <div class="pagination-area mt-30 mb-50">
                                        {{ $products->appends(request()->query())->links() }}
                                    </div>
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

@push('styles')
<style>
    .product-cart-wrap { border-radius: 15px; overflow: hidden; height: 100%; }
    .product-cart-wrap:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important; border-color: #3BB77E !important; }
    .product-img-zoom img { transition: all 1.5s cubic-bezier(0, 0, 0.05, 1); }
    .product-cart-wrap:hover .product-img-zoom img { transform: scale(1.05); }
    .product-content-wrap h2 { height: 40px; }
</style>
@endpush
