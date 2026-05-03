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
        <section id="penawaran-hari-ini" class="section-padding pb-5">
            <div class="container">
                <div class="section-title wow animate__animated animate__fadeIn" data-wow-delay="0">
                    <h3 class="">Penawaran Hari Ini</h3>
                    <a class="show-all" href="{{ route('promo.index') }}">
                        Semua Penawaran
                        <i class="fi-rs-angle-right"></i>
                    </a>
                </div>
                @if($homePromos->isEmpty())
                    <p class="text-muted font-md text-center py-40 mb-0">Belum ada penawaran aktif saat ini.</p>
                @else
                    <div class="row">
                        @foreach($homePromos as $promo)
                            @php
                                $idx = $loop->index;
                                $colClass = 'col-xl-3 col-lg-4 col-md-6';
                                if ($idx === 2) {
                                    $colClass .= ' d-none d-lg-block';
                                }
                                if ($idx === 3) {
                                    $colClass .= ' d-none d-xl-block';
                                }
                                $delay = $idx === 0 ? '0' : '.' . min($idx, 3) . 's';
                            @endphp
                            @include('themes.nest.partials.promo-deal-card', [
                                'promo' => $promo,
                                'delay' => $delay,
                                'colClass' => $colClass,
                            ])
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        <!--End Deals-->

        <section id="menu-hari-ini" class="section-padding pb-5">
            <div class="container">
                <div class="section-title wow animate__animated animate__fadeIn" data-wow-delay="0">
                    <h3 class="">Menu Hari Ini</h3>
                    <a class="show-all" href="{{ route('menus.index') }}">
                        Semua menu
                        <i class="fi-rs-angle-right"></i>
                    </a>
                </div>
                @if($todayMenus->isEmpty())
                    <p class="text-muted font-md text-center py-40 mb-0">Belum ada menu untuk ditampilkan saat ini.</p>
                @else
                    <div class="row">
                        @foreach($todayMenus as $menu)
                            @include('themes.nest.partials.menu-card', [
                                'menu' => $menu,
                                'delay' => $loop->first ? '0' : '.' . min($loop->index, 4) . 's',
                            ])
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        <!--End Menu Hari Ini-->

        {{-- Slider promosi: data sliders (Admin → Slider), urutan sort_order — tetap di bawah seperti layout asli Nest --}}
        <section class="home-slider position-relative mb-30">
            <div class="container">
                <div class="home-slide-cover mt-30">
                    <div class="hero-slider-1 style-4 dot-style-1 dot-style-1-position-1">
                        @forelse($sliders as $slider)
                            @php
                                $slideStyle = $slider->heroSlideStyle();
                                if ($slideStyle === '') {
                                    $slideStyle = 'background-image: url(' . asset('themes/nest-frontend/assets/imgs/slider/slider-1.png') . ')';
                                }
                                $slideLink = $slider->resolvedLink();
                            @endphp
                            <div class="single-hero-slider single-animation-wrap" style="{{ $slideStyle }}">
                                <div class="slider-content">
                                    @if(filled($slider->title))
                                        <h1 class="display-2 mb-40">{!! nl2br(e($slider->title)) !!}</h1>
                                    @endif
                                    @if(filled($slider->description))
                                        <p class="mb-65">{{ $slider->description }}</p>
                                    @endif
                                    @if($slideLink)
                                        <div class="form-subcriber d-flex">
                                            <a href="{{ $slideLink }}" class="btn">Belanja Sekarang</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="single-hero-slider single-animation-wrap" style="background-color: #e8f4f1; background-image: url({{ asset('themes/nest-frontend/assets/imgs/slider/slider-2.png') }});">
                                <div class="slider-content">
                                    <h1 class="display-2 mb-40">Selamat datang</h1>
                                    <p class="mb-65">Tambahkan slide promosi dari menu <strong>Admin → Slider</strong> (aktif &amp; urutan tampil).</p>
                                    <div class="form-subcriber d-flex flex-wrap align-items-center" style="gap: 12px;">
                                        <a href="{{ route('hubs.index') }}" class="btn">Pilih hub</a>
                                        <a href="{{ route('products.index') }}" class="btn btn-brush btn-xs">Lihat produk</a>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    <div class="slider-arrow hero-slider-1-arrow"></div>
                </div>
            </div>
        </section>

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
