@extends('themes.nest.layouts.app')

@section('title', $warehouse->name . ' - ' . config('app.name'))

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
            <span></span> <a href="{{ route('hubs.index') }}">Hub & Distributor</a>
            <span></span> {{ $warehouse->name }}
        </div>
    </div>
</div>
<div class="container mb-30">
    <div class="archive-header-3 mt-30 mb-80" style="background-image: url({{ asset('themes/nest-frontend/assets/imgs/vendor/vendor-header-bg.png') }})">
        <div class="archive-header-3-inner">
            <div class="vendor-logo mr-50">
                <img src="{{ asset('themes/nest-frontend/assets/imgs/vendor/vendor-17.png') }}" alt="{{ $warehouse->name }}" />
            </div>
            <div class="vendor-content">
                <div class="product-category">
                    <span class="text-muted">Hub & Distributor</span>
                </div>
                <h3 class="mb-5 text-white"><a href="#" class="text-white">{{ $warehouse->name }}</a></h3>
                <div class="product-rate-cover mb-15">
                    <div class="product-rate d-inline-block">
                        <div class="product-rating" style="width: 100%"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="vendor-des mb-15">
                            <p class="font-sm text-white">{{ Str::limit($warehouse->description ?? 'Hub resmi kami yang menyediakan berbagai produk berkualitas untuk kebutuhan Anda di wilayah ini.', 150) }}</p>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="vendor-info text-white mb-15">
                            <ul class="font-sm">
                                <li><img class="mr-5" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-location.svg') }}" alt="" /><strong>Alamat: </strong> <span>{{ $warehouse->address ?? $warehouse->full_location }}</span></li>
                                @if($warehouse->phone)
                                <li><img class="mr-5" src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-contact.svg') }}" alt="" /><strong>Telepon:</strong><span>{{ $warehouse->phone }}</span></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="follow-social">
                            <h6 class="mb-15 text-white">Hubungi Kami</h6>
                            <ul class="social-network">
                                <li class="hover-up">
                                    <a href="#">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/social-tw.svg') }}" alt="" />
                                    </a>
                                </li>
                                <li class="hover-up">
                                    <a href="#">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/social-fb.svg') }}" alt="" />
                                    </a>
                                </li>
                                <li class="hover-up">
                                    <a href="#">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/social-insta.svg') }}" alt="" />
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
