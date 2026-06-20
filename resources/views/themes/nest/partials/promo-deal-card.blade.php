{{-- Kartu Penawaran Hari Ini (product-cart-wrap style-2). Variabel: $promo, $delay (optional) --}}
@php
    $delay = $delay ?? '0';
    $colClass = $colClass ?? 'col-xl-3 col-lg-4 col-md-6';
    $img = $promo->image_url ?? asset('themes/nest-frontend/assets/imgs/banner/banner-5.png');
    $detailUrl = route('promo.show', $promo->slug);
    $productCount = (int) ($promo->products_count ?? $promo->products?->count() ?? 0);
@endphp
<div class="{{ $colClass }}">
    <div class="product-cart-wrap style-2 position-relative wow animate__animated animate__fadeInUp" data-wow-delay="{{ $delay }}">
        <div class="product-img-action-wrap">
            <div class="product-img">
                <a href="{{ $detailUrl }}" class="position-relative z-2">
                    <img src="{{ $img }}" alt="{{ $promo->judul_promo }}" />
                </a>
            </div>
        </div>
        <div class="product-content-wrap">
            <div class="deals-countdown-wrap">
                <div class="deals-countdown" data-countdown="{{ $promo->akhir->format('Y/m/d H:i:s') }}"></div>
            </div>
            <div class="deals-content">
                <h2><a href="{{ $detailUrl }}" class="position-relative z-2">{{ $promo->judul_promo }}</a></h2>
                <div class="product-rate-cover">
                    <span class="font-small ml-5 text-muted">{{ $promo->awal->format('d M Y') }} – {{ $promo->akhir->format('d M Y') }}</span>
                </div>
                <div>
                    <span class="font-small text-muted">{{ $productCount }} produk dalam promo ini</span>
                </div>
                <div class="product-card-bottom">
                    <div class="product-price">
                        <span class="font-small text-muted">Kode: {{ $promo->kode_promo }}</span>
                    </div>
                    <div class="add-cart">
                        <a class="add position-relative z-2" href="{{ $detailUrl }}" style="background-color: #3BB77E; color: #ffffff;"><i class="fi-rs-shopping-cart mr-5"></i>Lihat</a>
                    </div>
                </div>
            </div>
        </div>
        <a href="{{ $detailUrl }}" class="stretched-link" aria-label="Lihat promo {{ $promo->judul_promo }}"><span class="visually-hidden">{{ $promo->judul_promo }}</span></a>
    </div>
</div>
