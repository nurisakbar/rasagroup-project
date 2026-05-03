{{-- Sama struktur dengan kartu "Penawaran Hari Ini" di home (product-cart-wrap style-2). Variabel: $menu, $delay (optional) --}}
@php
    $delay = $delay ?? '0';
    $columnClass = $columnClass ?? 'col-xl-3 col-lg-4 col-md-6';
    $img = $menu->image_url ?: asset('themes/nest-frontend/assets/imgs/banner/banner-5.png');
    $total = $menu->bundledPrice();
    $detailUrl = route('menus.show', $menu->slug);
@endphp
<div class="{{ $columnClass }}">
    <div class="product-cart-wrap style-2 rg-menu-hari-ini-card position-relative wow animate__animated animate__fadeInUp" data-wow-delay="{{ $delay }}">
        <div class="product-img-action-wrap">
            <div class="product-img rg-menu-hari-ini-card__media">
                <a href="{{ $detailUrl }}" class="position-relative z-2 rg-menu-hari-ini-card__media-link">
                    <img src="{{ $img }}" alt="{{ $menu->nama_menu }}" loading="lazy" />
                </a>
            </div>
        </div>
        <div class="product-content-wrap">
            @if($menu->tampil_sampai)
                <div class="deals-countdown-wrap">
                    <div class="deals-countdown" data-countdown="{{ $menu->tampil_sampai->format('Y/m/d H:i:s') }}"></div>
                </div>
            @endif
            <div class="deals-content">
                <h2><a href="{{ $detailUrl }}" class="position-relative z-2">{{ $menu->nama_menu }}</a></h2>
                <div class="product-rate-cover">
                    <div class="product-rate d-inline-block">
                        <div class="product-rating" style="width: 90%"></div>
                    </div>
                    <span class="font-small ml-5 text-muted"> (4.0)</span>
                </div>
                <div>
                    <span class="font-small text-muted">By <a href="{{ route('menus.index') }}" class="position-relative z-2">Multi Citra Rasa</a></span>
                </div>
                <div class="product-card-bottom">
                    <div class="product-price">
                        <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    <div class="add-cart">
                        <a class="add position-relative z-2" href="{{ $detailUrl }}" style="background-color: #3BB77E; color: #ffffff;"><i class="fi-rs-shopping-cart mr-5"></i>Detail</a>
                    </div>
                </div>
            </div>
        </div>
        <a href="{{ $detailUrl }}" class="stretched-link" aria-label="Lihat detail {{ $menu->nama_menu }}"><span class="visually-hidden">{{ $menu->nama_menu }}</span></a>
    </div>
</div>
