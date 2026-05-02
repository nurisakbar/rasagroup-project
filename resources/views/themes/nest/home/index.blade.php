@extends('themes.nest.layouts.app')

@section('title', 'Marketplace Produk Makanan & Minuman Berkualitas')
@section('meta_description', 'Selamat datang di Multi Citra Rasa Marketplace. Temukan berbagai pilihan produk sirup premium, bahan baku makanan, dan kebutuhan horeca terbaik untuk bisnis Anda.')

@section('content')
        <style>
            /* Tema Nest: pastikan label tab home uppercase (override nav-link / media query) */
            #myTab.nav-tabs.links .nav-link {
                text-transform: uppercase !important;
            }
        </style>
        <section class="popular-categories section-padding" style="display: none;">
            <div class="container wow animate__animated animate__fadeIn">
                <div class="section-title">
                    <div class="title">
                        <h3>Featured Categories</h3>
                        <ul class="list-inline nav nav-tabs links text-uppercase">
                            @foreach($categories->take(4) as $category)
                            <li class="list-inline-item nav-item"><a class="nav-link" href="{{ route('products.index', ['category' => $category->slug]) }}">{{ $category->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="slider-arrow slider-arrow-2 flex-right carausel-10-columns-arrow" id="carausel-10-columns-arrows"></div>
                </div>
                <div class="carausel-10-columns-cover position-relative">
                    <div class="carausel-10-columns" id="carausel-10-columns">
                        @php
                            $bgColors = ['bg-9', 'bg-10', 'bg-11', 'bg-12', 'bg-13', 'bg-14', 'bg-15'];
                        @endphp
                        @foreach($categories as $category)
                            @php
                                $bgColor = $bgColors[$loop->index % count($bgColors)];
                                $image = $category->image ? asset('storage/' . $category->image) : asset('themes/nest-frontend/assets/imgs/shop/cat-13.png');
                            @endphp
                            <div class="card-2 {{ $bgColor }} wow animate__animated animate__fadeInUp" data-wow-delay="{{ .1 * ($loop->index + 1) }}s">
                                <figure class="img-hover-scale overflow-hidden">
                                    <a href="{{ route('products.index', ['category' => $category->slug]) }}"><img src="{{ $image }}" alt="{{ $category->name }}" style="height: 80px; width: 80px; object-fit: cover;" /></a>
                                </figure>
                                <h6 class="text-uppercase"><a href="{{ route('products.index', ['category' => $category->slug]) }}">{{ $category->name }}</a></h6>
                                <span>{{ $category->products_count }} items</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
        <!--End category slider-->
        <section class="product-tabs section-padding position-relative">
            <div class="container">
                <div class="section-title style-2 wow animate__animated animate__fadeIn">
                    <h3>Produk Populer</h3>
                    <ul class="nav nav-tabs links text-uppercase" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active text-uppercase" id="nav-tab-one" data-bs-toggle="tab" data-bs-target="#tab-one" type="button" role="tab" aria-controls="tab-one" aria-selected="true">{{ mb_strtoupper('Semua', 'UTF-8') }}</button>
                        </li>
                        @foreach($categories as $index => $category)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-uppercase" id="nav-tab-{{ $category->id }}" data-bs-toggle="tab" data-bs-target="#tab-cat-{{ $category->id }}" type="button" role="tab" aria-controls="tab-cat-{{ $category->id }}" aria-selected="false">{{ mb_strtoupper($category->name, 'UTF-8') }}</button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <!--End nav-tabs-->
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="tab-one" role="tabpanel" aria-labelledby="tab-one">
                        <div class="row product-grid-4">
                            @foreach($popularProducts as $product)
                                @include('themes.nest.partials.product-card', compact('product'))
                            @endforeach
                        </div>
                        <!--End product-grid-4-->
                    </div>
                    <!--En tab one-->
                    @foreach($categories as $category)
                        <div class="tab-pane fade" id="tab-cat-{{ $category->id }}" role="tabpanel" aria-labelledby="tab-cat-{{ $category->id }}">
                            <div class="row product-grid-4">
                                @if(isset($categoryProducts[$category->id]))
                                    @foreach($categoryProducts[$category->id] as $product)
                                        @include('themes.nest.partials.product-card', compact('product'))
                                    @endforeach
                                @endif
                            </div>
                            <!--End product-grid-4-->
                        </div>
                        <!--En tab category-->
                    @endforeach
                </div>
                <!--End tab-content-->
                <div class="text-center mt-30">
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-default">
                        Lihat lebih banyak <i class="fi-rs-arrow-right ml-10"></i>
                    </a>
                </div>
            </div>
        </section>
        <section class="section-padding pb-5">
            <div class="container">
                <div class="section-title wow animate__animated animate__fadeIn" data-wow-delay="0">
                    <h3 class="">Penawaran Hari Ini</h3>
                    <a class="show-all" href="shop-grid-right.html">
                        Semua Penawaran
                        <i class="fi-rs-angle-right"></i>
                    </a>
                </div>
                <div class="row">
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="product-cart-wrap style-2 wow animate__animated animate__fadeInUp" data-wow-delay="0">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-5.png') }}" alt="" />
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2025/12/25 00:00:00"></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Seeds of Change Organic Quinoa, Brown, & Red Rice</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 90%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (4.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">NestFood</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>Rp 32.850</span>
                                            <span class="old-price">Rp 33.800</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="{{ route('cart.index') }}" style="background-color: #3BB77E; color: #ffffff;"><i class="fi-rs-shopping-cart mr-5"></i>Tambah </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="product-cart-wrap style-2 wow animate__animated animate__fadeInUp" data-wow-delay=".1s">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-6.png') }}" alt="" />
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2026/04/25 00:00:00"></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Perdue Simply Smart Organics Gluten Free</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 90%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (4.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">Old El Paso</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>Rp 24.850</span>
                                            <span class="old-price">Rp 26.800</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="{{ route('cart.index') }}" style="background-color: #3BB77E; color: #ffffff;"><i class="fi-rs-shopping-cart mr-5"></i>Tambah </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 d-none d-lg-block">
                        <div class="product-cart-wrap style-2 wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-7.png') }}" alt="" />
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2027/03/25 00:00:00"></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Signature Wood-Fired Mushroom and Caramelized</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 80%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (3.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">Progresso</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>Rp 12.850</span>
                                            <span class="old-price">Rp 13.800</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="{{ route('cart.index') }}" style="background-color: #3BB77E; color: #ffffff;"><i class="fi-rs-shopping-cart mr-5"></i>Tambah </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 d-none d-xl-block">
                        <div class="product-cart-wrap style-2 wow animate__animated animate__fadeInUp" data-wow-delay=".3s">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-8.png') }}" alt="" />
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2025/02/25 00:00:00"></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Simply Lemonade with Raspberry Juice</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 80%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (3.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">Yoplait</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>Rp 15.850</span>
                                            <span class="old-price">Rp 16.800</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="{{ route('cart.index') }}" style="background-color: #3BB77E; color: #ffffff;"><i class="fi-rs-shopping-cart mr-5"></i>Tambah </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--End Deals-->

        <section class="section-padding pb-5">
            <div class="container">
                <div class="section-title wow animate__animated animate__fadeIn" data-wow-delay="0">
                    <h3 class="">Menu Hari Ini</h3>
                    <a class="show-all" href="shop-grid-right.html">
                        Semua Menu
                        <i class="fi-rs-angle-right"></i>
                    </a>
                </div>
                <div class="row">
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="product-cart-wrap style-2 wow animate__animated animate__fadeInUp" data-wow-delay="0">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-5.png') }}" alt="" />
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2025/12/25 00:00:00"></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Seeds of Change Organic Quinoa, Brown, & Red Rice</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 90%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (4.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">NestFood</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>Rp 32.850</span>
                                            <span class="old-price">Rp 33.800</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="{{ route('cart.index') }}" style="background-color: #3BB77E; color: #ffffff;"><i class="fi-rs-shopping-cart mr-5"></i>Tambah </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="product-cart-wrap style-2 wow animate__animated animate__fadeInUp" data-wow-delay=".1s">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-6.png') }}" alt="" />
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2026/04/25 00:00:00"></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Perdue Simply Smart Organics Gluten Free</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 90%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (4.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">Old El Paso</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>Rp 24.850</span>
                                            <span class="old-price">Rp 26.800</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="{{ route('cart.index') }}" style="background-color: #3BB77E; color: #ffffff;"><i class="fi-rs-shopping-cart mr-5"></i>Tambah </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 d-none d-lg-block">
                        <div class="product-cart-wrap style-2 wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-7.png') }}" alt="" />
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2027/03/25 00:00:00"></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Signature Wood-Fired Mushroom and Caramelized</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 80%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (3.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">Progresso</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>Rp 12.850</span>
                                            <span class="old-price">Rp 13.800</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="{{ route('cart.index') }}" style="background-color: #3BB77E; color: #ffffff;"><i class="fi-rs-shopping-cart mr-5"></i>Tambah </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 d-none d-xl-block">
                        <div class="product-cart-wrap style-2 wow animate__animated animate__fadeInUp" data-wow-delay=".3s">
                            <div class="product-img-action-wrap">
                                <div class="product-img">
                                    <a href="shop-product-right.html">
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-8.png') }}" alt="" />
                                    </a>
                                </div>
                            </div>
                            <div class="product-content-wrap">
                                <div class="deals-countdown-wrap">
                                    <div class="deals-countdown" data-countdown="2025/02/25 00:00:00"></div>
                                </div>
                                <div class="deals-content">
                                    <h2><a href="shop-product-right.html">Simply Lemonade with Raspberry Juice</a></h2>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 80%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (3.0)</span>
                                    </div>
                                    <div>
                                        <span class="font-small text-muted">By <a href="vendor-details-1.html">Yoplait</a></span>
                                    </div>
                                    <div class="product-card-bottom">
                                        <div class="product-price">
                                            <span>Rp 15.850</span>
                                            <span class="old-price">Rp 16.800</span>
                                        </div>
                                        <div class="add-cart">
                                            <a class="add" href="{{ route('cart.index') }}" style="background-color: #3BB77E; color: #ffffff;"><i class="fi-rs-shopping-cart mr-5"></i>Tambah </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--End Menu Hari Ini-->

        <section class="home-slider position-relative mb-30">
            <div class="container">
                <div class="home-slide-cover mt-30">
                    <div class="hero-slider-1 style-4 dot-style-1 dot-style-1-position-1">
                        @forelse($sliders as $slider)
                        <div class="single-hero-slider single-animation-wrap" style="background-image: url({{ asset('storage/' . $slider->image) }})">
                            <div class="slider-content">
                                <h1 class="display-2 mb-40">
                                    {!! $slider->title !!}
                                </h1>
                                <p class="mb-65">{{ $slider->description }}</p>
                                @if($slider->link)
                                    <div class="form-subcriber d-flex">
                                        <a href="{{ $slider->link }}" class="btn">Belanja Sekarang</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="single-hero-slider single-animation-wrap" style="background-image: url({{ asset('themes/nest-frontend/assets/imgs/slider/slider-1.png') }})">
                            <div class="slider-content">
                                <h1 class="display-2 mb-40">
                                    Don’t miss amazing<br />
                                    grocery deals
                                </h1>
                                <p class="mb-65">Sign up for the daily newsletter</p>
                            </div>
                        </div>
                        <div class="single-hero-slider single-animation-wrap" style="background-image: url({{ asset('themes/nest-frontend/assets/imgs/slider/slider-2.png') }})">
                            <div class="slider-content">
                                <h1 class="display-2 mb-40">
                                    Fresh Vegetables<br />
                                    Big discount
                                </h1>
                                <p class="mb-65">Save up to 50% off on your first order</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    <div class="slider-arrow hero-slider-1-arrow"></div>
                </div>
            </div>
        </section>
        <!--End hero slider-->

        <section class="section-padding mb-30" style="display: none;">
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-sm-5 mb-md-0 wow animate__animated animate__fadeInUp" data-wow-delay="0">
                        <h4 class="section-title style-1 mb-30 animated animated">Paling Laris</h4>
                        <div class="product-list-small animated animated">
                            @foreach($topSelling as $product)
                                @include('themes.nest.partials.product-list-small', compact('product'))
                            @endforeach
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-md-0 wow animate__animated animate__fadeInUp" data-wow-delay=".1s">
                        <h4 class="section-title style-1 mb-30 animated animated">Produk Tren</h4>
                        <div class="product-list-small animated animated">
                            @foreach($trendingProducts as $product)
                                @include('themes.nest.partials.product-list-small', compact('product'))
                            @endforeach
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-sm-5 mb-md-0 d-none d-lg-block wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                        <h4 class="section-title style-1 mb-30 animated animated">Terbaru</h4>
                        <div class="product-list-small animated animated">
                            @foreach($recentlyAdded as $product)
                                @include('themes.nest.partials.product-list-small', compact('product'))
                            @endforeach
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-sm-5 mb-md-0 d-none d-xl-block wow animate__animated animate__fadeInUp" data-wow-delay=".3s">
                        <h4 class="section-title style-1 mb-30 animated animated">Rating Tertinggi</h4>
                        <div class="product-list-small animated animated">
                            @foreach($topRated as $product)
                                @include('themes.nest.partials.product-list-small', compact('product'))
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--End 4 columns-->
    </main>

@endsection
