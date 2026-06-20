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
                            <h3>Produk Promo</h3>
                            <p>Semua produk yang sedang dalam promo aktif.</p>
                        </div>

                        @if($products->isEmpty())
                            <div class="col-12 text-center py-5">
                                <div class="mb-20">
                                    <i class="fi-rs-label" style="font-size: 64px; color: #ddd;"></i>
                                </div>
                                <h4>Belum ada produk promo aktif saat ini.</h4>
                                <p>Silakan kembali lagi nanti untuk melihat penawaran terbaru kami.</p>
                                <a href="{{ route('home') }}" class="btn btn-xs mt-20">Kembali ke Beranda <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        @else
                            <div class="row product-grid-4">
                                @foreach($products as $product)
                                    @include('themes.nest.partials.product-card', [
                                        'product' => $product,
                                        'showPromoPeriod' => true,
                                    ])
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
