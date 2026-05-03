@extends('themes.nest.layouts.app')

@section('title', $warehouse->name . ' - ' . config('app.name'))

@section('content')
<div class="page-header mt-30 mb-50 rg-archive-header-maroon">
    <div class="container">
        <div class="archive-header">
            <div class="row align-items-start">
                <div class="col-xl-8">
                    <p class="font-sm mb-10 text-uppercase" style="color: rgba(255,255,255,0.85); letter-spacing: 0.06em;">Hub &amp; Distributor</p>
                    <h1 class="mb-15">{{ $warehouse->name }}</h1>
                    <p class="font-md mb-15" style="color: rgba(255,255,255,0.92);">{{ $warehouse->full_location }}</p>
                    <p class="font-sm mb-20" style="color: rgba(255,255,255,0.88); max-width: 720px;">{{ Str::limit($warehouse->description ?? 'Hub resmi kami yang menyediakan berbagai produk berkualitas untuk kebutuhan Anda di wilayah ini.', 220) }}</p>
                    <div class="breadcrumb">
                        <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
                        <span></span> <a href="{{ route('hubs.index') }}">Hub &amp; Distributor</a>
                        <span></span> {{ $warehouse->name }}
                    </div>
                </div>
                <div class="col-xl-4 text-xl-end mt-30 mt-xl-0">
                    <ul class="list-unstyled font-sm text-start text-xl-end mb-20" style="color: rgba(255,255,255,0.92);">
                        <li class="mb-15">
                            <strong>Alamat</strong><br>
                            <span class="font-xs">{{ $warehouse->address ?? $warehouse->full_location }}</span>
                        </li>
                        @if($warehouse->phone)
                        <li>
                            <strong>Telepon</strong><br>
                            {{ $warehouse->phone }}
                        </li>
                        @endif
                    </ul>
                    <form action="{{ route('hubs.select') }}" method="POST" class="d-inline-block w-100 text-xl-end">
                        @csrf
                        <input type="hidden" name="warehouse_id" value="{{ $warehouse->id }}">
                        <button type="submit" class="btn btn-standar-utama btn-sm">Pilih Hub Ini <i class="fi-rs-check"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container mb-30">
    <div class="row flex-row-reverse">
        <div class="col-lg-4-5">
            <div class="shop-product-fillter">
                <div class="totall-product">
                    <p>Kami menemukan <strong class="text-brand">{{ $productsWithStock->count() }}</strong> produk tersedia!</p>
                </div>
            </div>
            <div class="product-list mb-50">
                @forelse($productsWithStock as $stock)
                    @php 
                        $product = $stock->product ?? null;
                    @endphp
                    @if($product && isset($product->display_name) && isset($product->price))
                    <div class="product-cart-wrap">
                        <div class="product-img-action-wrap">
                            <div class="product-img product-img-zoom">
                                <div class="product-img-inner">
                                    <a href="{{ route('products.show', ['product' => $product, 'warehouse_id' => $warehouse->slug]) }}">
                                        @if(!empty($product->image_url))
                                            <img class="default-img" src="{{ asset($product->image_url) }}" alt="{{ $product->display_name }}" />
                                            <img class="hover-img" src="{{ asset($product->image_url) }}" alt="{{ $product->display_name }}" />
                                        @else
                                            <img class="default-img" src="{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="" />
                                            <img class="hover-img" src="{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-2.jpg') }}" alt="" />
                                        @endif
                                    </a>
                                </div>
                            </div>
                            <div class="product-action-1">
                                <a aria-label="Lihat Detail" class="action-btn" href="{{ route('products.show', ['product' => $product, 'warehouse_id' => $warehouse->id]) }}"><i class="fi-rs-eye"></i></a>
                            </div>
                            <div class="product-badges product-badges-position product-badges-mrg">
                                <span class="hot">Tersedia</span>
                            </div>
                        </div>
                        <div class="product-content-wrap">
                            <div class="product-category">
                                <a href="#">{{ $product->brand->name ?? 'Produk Unggulan' }}</a>
                            </div>
                            <h2><a href="{{ route('products.show', ['product' => $product, 'warehouse_id' => $warehouse->id]) }}">{{ $product->display_name }}</a></h2>
                            <div class="product-rate-cover">
                                <div class="product-rate d-inline-block">
                                    <div class="product-rating" style="width: 90%"></div>
                                </div>
                                <span class="font-small ml-5 text-muted"> (4.0)</span>
                                <span class="ml-30 text-brand">{{ $stock->stock }} items tersedia</span>
                            </div>
                            <p class="mt-15 mb-15">{{ Str::limit($product->description ?? 'Nikmati produk berkualitas tinggi yang tersedia langsung di hub kami. Produk ini telah melalui seleksi ketat untuk menjamin kepuasan Anda.', 180) }}</p>
                            <div class="product-price">
                                <span>Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="mt-30 d-flex align-items-center">
                                <a aria-label="Lihat Detail" class="btn" href="{{ route('products.show', ['product' => $product, 'warehouse_id' => $warehouse->id]) }}"><i class="fi-rs-shopping-cart mr-5"></i>Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="text-center mt-50 mb-50">
                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-location.svg') }}" alt="Empty" style="width: 80px; opacity: 0.3;">
                        <h4 class="mt-20 text-muted">Belum ada produk tersedia</h4>
                        <p class="text-muted">Hub ini belum memiliki stok produk saat ini.</p>
                        <a href="{{ route('hubs.index') }}" class="btn btn-sm mt-20"><i class="fi-rs-arrow-left mr-5"></i> Kembali ke Daftar Hub</a>
                    </div>
                @endforelse
            </div>
            
            @if($productsWithStock->count() > 0)
            <div class="pagination-area mt-20 mb-20">
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-start">
                        <li class="page-item"><a class="page-link active" href="#">1</a></li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
        <div class="col-lg-1-5 primary-sidebar sticky-sidebar">
            <div class="sidebar-widget widget-category-2 mb-30">
                <h5 class="section-title style-1 mb-30">Statistik Hub</h5>
                <ul>
                    <li>
                        <a href="#"> <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/category-1.svg') }}" alt="" />Total Produk</a><span class="count">{{ $productsWithStock->count() }}</span>
                    </li>
                    <li>
                        <a href="#"> <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/category-2.svg') }}" alt="" />Total Stok</a><span class="count">{{ $productsWithStock->sum('stock') }}</span>
                    </li>
                </ul>
            </div>
            
            <div class="banner-img wow fadeIn mb-lg-0 animated d-lg-block d-none">
                <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-11.png') }}" alt="" />
                <div class="banner-text">
                    <span>{{ $warehouse->province->name ?? 'Hub Lokal' }}</span>
                    <h4>
                        Belanja <br />
                        di <span class="text-brand">{{ Str::limit($warehouse->name, 15) }}</span><br />
                        Sekarang
                    </h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
