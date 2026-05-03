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
                            <div class="col-lg-4 col-md-6 mb-30 wow animate__animated animate__fadeInUp" data-wow-delay="{{ $loop->index * 0.1 }}s">
                                <div class="product-cart-wrap style-2">
                                    @if($promo->image_url)
                                        <div class="product-img-action-wrap">
                                            <div class="product-img">
                                                <a href="{{ route('promo.show', $promo->slug) }}">
                                                    <img src="{{ $promo->image_url }}" alt="{{ $promo->judul_promo }}" style="width: 100%; height: 200px; object-fit: cover;" />
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="product-content-wrap">
                                        <div class="deals-content">
                                            <h2 class="mb-10"><a href="{{ route('promo.show', $promo->slug) }}">{{ $promo->judul_promo }}</a></h2>
                                            <div class="product-rate-cover">
                                                <span class="font-small text-muted">Kode Promo: <strong class="text-brand">{{ $promo->kode_promo }}</strong></span>
                                            </div>
                                            <div class="mb-15 mt-10">
                                                {!! $promo->deskripsi !!}
                                            </div>
                                            <div class="product-card-bottom">
                                                <div class="product-price">
                                                    <span>Rp {{ number_format($promo->harga, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                            <div class="mt-15">
                                                <span class="font-xs text-muted">
                                                    <i class="fi-rs-calendar mr-5"></i> Berlaku: {{ $promo->awal->format('d M Y H:i') }} - {{ $promo->akhir->format('d M Y H:i') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
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
