@extends('themes.nest.layouts.app')

@section('content')
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
                                        <a href="{{ $slider->link }}" class="btn">Shop Now</a>
                                    </div>
                                @else
                                    <form class="form-subcriber d-flex">
                                        <input type="email" placeholder="Your emaill address" />
                                        <button class="btn" type="submit">Subscribe</button>
                                    </form>
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
                                <form class="form-subcriber d-flex">
                                    <input type="email" placeholder="Your emaill address" />
                                    <button class="btn" type="submit">Subscribe</button>
                                </form>
                            </div>
                        </div>
                        <div class="single-hero-slider single-animation-wrap" style="background-image: url({{ asset('themes/nest-frontend/assets/imgs/slider/slider-2.png') }})">
                            <div class="slider-content">
                                <h1 class="display-2 mb-40">
                                    Fresh Vegetables<br />
                                    Big discount
                                </h1>
                                <p class="mb-65">Save up to 50% off on your first order</p>
                                <form class="form-subcriber d-flex">
                                    <input type="email" placeholder="Your emaill address" />
                                    <button class="btn" type="submit">Subscribe</button>
                                </form>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    <div class="slider-arrow hero-slider-1-arrow"></div>
                </div>
            </div>
        </section>
        <!--End hero slider-->
        <section class="popular-categories section-padding">
            <div class="container wow animate__animated animate__fadeIn">
                <div class="section-title">
                    <div class="title">
                        <h3>Featured Categories</h3>
                        <ul class="list-inline nav nav-tabs links">
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
                                <h6><a href="{{ route('products.index', ['category' => $category->slug]) }}">{{ $category->name }}</a></h6>
                                <span>{{ $category->products_count }} items</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
        <!--End category slider-->
        <section class="banners mb-25">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="banner-img wow animate__animated animate__fadeInUp" data-wow-delay="0">
                            <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-1.png') }}" alt="" />
                            <div class="banner-text">
                                <h4>
                                    Everyday Fresh & <br />Clean with Our<br />
                                    Products
                                </h4>
                                <a href="shop-grid-right.html" class="btn btn-xs">Shop Now <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="banner-img wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                            <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-2.png') }}" alt="" />
                            <div class="banner-text">
                                <h4>
                                    Make your Breakfast<br />
                                    Healthy and Easy
                                </h4>
                                <a href="shop-grid-right.html" class="btn btn-xs">Shop Now <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 d-md-none d-lg-flex">
                        <div class="banner-img mb-sm-0 wow animate__animated animate__fadeInUp" data-wow-delay=".4s">
                            <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-3.png') }}" alt="" />
                            <div class="banner-text">
                                <h4>The best Organic <br />Products Online</h4>
                                <a href="shop-grid-right.html" class="btn btn-xs">Shop Now <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--End banners-->
        <section class="product-tabs section-padding position-relative">
            <div class="container">
                <div class="section-title style-2 wow animate__animated animate__fadeIn">
                    <h3>Popular Products</h3>
                    <ul class="nav nav-tabs links" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="nav-tab-one" data-bs-toggle="tab" data-bs-target="#tab-one" type="button" role="tab" aria-controls="tab-one" aria-selected="true">All</button>
                        </li>
                        @foreach($categories as $index => $category)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="nav-tab-{{ $category->id }}" data-bs-toggle="tab" data-bs-target="#tab-cat-{{ $category->id }}" type="button" role="tab" aria-controls="tab-cat-{{ $category->id }}" aria-selected="false">{{ $category->name }}</button>
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
            </div>
        </section>
        <!--Products Tabs-->
        <section class="section-padding pb-5">
            <div class="container">
                <div class="section-title wow animate__animated animate__fadeIn">
                    <h3 class="">Daily Best Sells</h3>
                    <ul class="nav nav-tabs links" id="myTab-2" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="nav-tab-one-1" data-bs-toggle="tab" data-bs-target="#tab-one-1" type="button" role="tab" aria-controls="tab-one" aria-selected="true">Featured</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="nav-tab-two-1" data-bs-toggle="tab" data-bs-target="#tab-two-1" type="button" role="tab" aria-controls="tab-two" aria-selected="false">Popular</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="nav-tab-three-1" data-bs-toggle="tab" data-bs-target="#tab-three-1" type="button" role="tab" aria-controls="tab-three" aria-selected="false">New added</button>
                        </li>
                    </ul>
                </div>
                <div class="row">
                    <div class="col-lg-3 d-none d-lg-flex wow animate__animated animate__fadeIn">
                        <div class="banner-img style-2">
                            <div class="banner-text">
                                <h2 class="mb-100">Bring nature into your home</h2>
                                <a href="shop-grid-right.html" class="btn btn-xs">Shop Now <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-12 wow animate__animated animate__fadeIn" data-wow-delay=".4s">
                        <div class="tab-content" id="myTabContent-1">
                        <!--End tab-content-->
                    </div>
                    <!--End Col-lg-9-->
                </div>
            </div>
        </section>
        <!--End Best Sales-->
        <section class="section-padding pb-5">
            <div class="container">
                <div class="section-title wow animate__animated animate__fadeIn" data-wow-delay="0">
                    <h3 class="">Deals Of The Day</h3>
                    <a class="show-all" href="shop-grid-right.html">
                        All Deals
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
                                            <a class="add" href="{{ route('cart.index') }}"><i class="fi-rs-shopping-cart mr-5"></i>Add </a>
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
                                            <a class="add" href="{{ route('cart.index') }}"><i class="fi-rs-shopping-cart mr-5"></i>Add </a>
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
                                            <a class="add" href="{{ route('cart.index') }}"><i class="fi-rs-shopping-cart mr-5"></i>Add </a>
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
                                            <a class="add" href="{{ route('cart.index') }}"><i class="fi-rs-shopping-cart mr-5"></i>Add </a>
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
        <section class="section-padding mb-30">
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-sm-5 mb-md-0 wow animate__animated animate__fadeInUp" data-wow-delay="0">
                        <h4 class="section-title style-1 mb-30 animated animated">Top Selling</h4>
                        <div class="product-list-small animated animated">
                            @foreach($topSelling as $product)
                                @include('themes.nest.partials.product-list-small', compact('product'))
                            @endforeach
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-md-0 wow animate__animated animate__fadeInUp" data-wow-delay=".1s">
                        <h4 class="section-title style-1 mb-30 animated animated">Trending Products</h4>
                        <div class="product-list-small animated animated">
                            @foreach($trendingProducts as $product)
                                @include('themes.nest.partials.product-list-small', compact('product'))
                            @endforeach
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-sm-5 mb-md-0 d-none d-lg-block wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                        <h4 class="section-title style-1 mb-30 animated animated">Recently Added</h4>
                        <div class="product-list-small animated animated">
                            @foreach($recentlyAdded as $product)
                                @include('themes.nest.partials.product-list-small', compact('product'))
                            @endforeach
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-sm-5 mb-md-0 d-none d-xl-block wow animate__animated animate__fadeInUp" data-wow-delay=".3s">
                        <h4 class="section-title style-1 mb-30 animated animated">Top Rated</h4>
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
