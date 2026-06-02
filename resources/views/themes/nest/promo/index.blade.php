@extends('themes.nest.layouts.app')

@section('content')
    <main class="main">
        <div class="page-header mt-30 mb-75 rg-archive-header-maroon">
            <div class="container">
                <div class="archive-header">
                    <div class="row align-items-center">
                        <div class="col-xl-12">
                            <h1 class="mb-15">Promo</h1>
                            <div class="breadcrumb">
                                <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Halaman Utama</a>
                                <span></span> Promo
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <div class="page-content pt-50 pb-50">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title style-2 wow animate__animated animate__fadeIn">
                        <h3>Promo Terbaru</h3>
                        <p>Dapatkan penawaran terbaik denga promo-promo menarik dari kami.</p>
                    </div>
                    <div class="row">
                        @forelse($promos as $promo)
                            @php
                                $delay = $loop->first ? '0' : '.' . min($loop->index % 5, 4) . 's';
                            @endphp
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-50">
                                <div class="product-cart-wrap style-2 rg-menu-hari-ini-card position-relative wow animate__animated animate__fadeInUp" data-wow-delay="{{ $delay }}">
                                    <div class="product-img-action-wrap">
                                        <div class="product-img rg-menu-hari-ini-card__media">
                                            <a href="{{ route('promo.show', $promo->slug) }}" class="position-relative z-2 rg-menu-hari-ini-card__media-link">
                                                <img src="{{ $promo->image_url ?: asset('themes/nest-frontend/assets/imgs/banner/banner-5.png') }}" alt="{{ $promo->judul_promo }}" loading="lazy" />
                                            </a>
                                        </div>
                                    </div>
                                    <div class="product-content-wrap">
                                        @if($promo->akhir)
                                            <div class="deals-countdown-wrap">
                                                <div class="deals-countdown" data-countdown="{{ $promo->akhir->format('Y/m/d H:i:s') }}"></div>
                                            </div>
                                        @endif
                                        <div class="deals-content">
                                            <h2><a href="{{ route('promo.show', $promo->slug) }}" class="position-relative z-2">{{ $promo->judul_promo }}</a></h2>
                                            
                                            <div class="product-rate-cover">
                                                <span class="font-small text-muted">Kode Promo: <strong class="text-brand">{{ $promo->kode_promo }}</strong></span>
                                            </div>
                                            
                                            <div class="product-card-bottom">
                                                <div class="product-price">
                                                    <span>Rp {{ number_format($promo->harga, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="add-cart">
                                                    <a class="add position-relative z-2" href="{{ route('promo.show', $promo->slug) }}" style="background-color: #3BB77E; color: #ffffff;"><i class="fi-rs-shopping-cart mr-5"></i>Detail</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ route('promo.show', $promo->slug) }}" class="stretched-link" aria-label="Lihat detail {{ $promo->judul_promo }}"><span class="visually-hidden">{{ $promo->judul_promo }}</span></a>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <div class="mb-20">
                                    <i class="fi-rs-label" style="font-size: 64px; color: #ddd;"></i>
                                </div>
                                <h4>Belum ada promo aktif saat ini.</h4>
                                <p>Silakan kembali lagi nanti untuk melihat promo-promo terbaru kami.</p>
                                <a href="{{ route('home') }}" class="btn btn-xs mt-20">Kembali ke Beranda <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>
@endsection
