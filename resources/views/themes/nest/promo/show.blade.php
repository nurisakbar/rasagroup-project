@extends('themes.nest.layouts.app')

@section('content')
    <div class="page-header breadcrumb-wrap">
        <div class="container">
            <div class="breadcrumb">
                <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
                <span></span> <a href="{{ route('promo.index') }}">Promo</a>
                <span></span> {{ $promo->judul_promo }}
            </div>
        </div>
    </div>
    <div class="container mb-30 mt-50">
        <div class="row">
            <div class="col-xl-11 col-lg-12 m-auto">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="single-page pt-50">
                            <div class="single-header style-2">
                                <div class="row">
                                    <div class="col-xl-10 col-lg-12 m-auto">
                                        <h2 class="mb-10">{{ $promo->judul_promo }}</h2>
                                        <div class="single-header-meta">
                                            <div class="entry-meta meta-1 font-xxs color-grey mt-10 pb-10">
                                                <span class="post-on"><i class="fi-rs-calendar mr-5"></i> Berlaku: {{ $promo->awal->format('d M Y') }} - {{ $promo->akhir->format('d M Y') }}</span>
                                                <span class="hit-count has-dot">Kode Promo: <strong class="text-brand">{{ $promo->kode_promo }}</strong></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if($promo->image)
                                <figure class="single-thumbnail w-100 m-auto mt-30 mb-50">
                                    <img src="{{ asset('storage/' . $promo->image) }}" alt="{{ $promo->judul_promo }}" style="max-height: 500px; width: 100%; object-fit: contain; border-radius: 15px;">
                                </figure>
                            @endif

                            <div class="single-content">
                                <div class="row">
                                    <div class="col-xl-10 col-lg-12 m-auto">
                                        <div class="promo-price-tag mb-30">
                                            <h3 class="text-brand">Nilai Promo: Rp {{ number_format($promo->harga, 0, ',', '.') }}</h3>
                                        </div>
                                        
                                        <div class="promo-description">
                                            {!! $promo->deskripsi !!}
                                        </div>
                                        
                                        <div class="promo-actions mt-50 mb-50 p-30 bg-grey-9 border-radius-10">
                                            <h4>Cara Menggunakan:</h4>
                                            <p>Gunakan kode promo <strong class="text-brand">{{ $promo->kode_promo }}</strong> saat melakukan checkout untuk mendapatkan potongan harga sebesar Rp {{ number_format($promo->harga, 0, ',', '.') }}.</p>
                                            <a href="{{ route('products.index') }}" class="btn mt-20">Belanja Sekarang <i class="fi-rs-arrow-small-right"></i></a>
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
