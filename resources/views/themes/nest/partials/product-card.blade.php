<div class="{{ $columnClass ?? 'col-lg-1-5 col-md-4 col-12 col-sm-6' }} rg-product-col">
    <div class="product-cart-wrap mb-30 wow animate__animated animate__fadeIn rg-product-card" data-wow-delay=".1s">
        <div class="product-img-action-wrap rg-product-media">
            <div class="product-img product-img-zoom">
                <a href="{{ route('products.show', $product->slug) }}">
                    <img class="default-img rg-product-img" src="{{ $product->image_url ?? asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}';" />
                    <img class="hover-img rg-product-img" src="{{ $product->image_url ?? asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}';" />
                </a>
            </div>
            <div class="product-badges product-badges-position product-badges-mrg">
                @if($product->created_at->diffInDays(now()) < 7)
                    <span class="new">Baru</span>
                @elseif($product->price < ($product->compare_price ?? 0))
                    <span class="hot">Populer</span>
                @endif
            </div>
        </div>
        <div class="product-content-wrap rg-product-body">
            <div class="product-category">
                <a href="#">{{ $product->category->name ?? 'Tanpa Kategori' }}</a>
            </div>
            <h2 class="rg-product-title"><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h2>
            <div class="product-rate-cover">
                <div class="product-rate d-inline-block">
                    <div class="product-rating" style="width: {{ ($product->rating ?? 0) * 20 }}%"></div>
                </div>
                <span class="font-small ml-5 text-muted"> ({{ number_format($product->rating ?? 0, 1) }})</span>
            </div>
            <div>
                <span class="font-small text-muted">Oleh
                    @if($product->brand?->slug)
                        <a href="{{ route('products.index', ['brand' => $product->brand->slug]) }}">{{ $product->brand->name }}</a>
                    @else
                        {{ $product->brand->name ?? 'Tanpa Brand' }}
                    @endif
                </span>
                @if(\App\Support\ShopFulfillment::showStockOnStorefront() && session('selected_hub_id'))
                    <span class="font-small ml-10 text-success">Stok: {{ $product->current_stock }}</span>
                @endif
            </div>
            @if(!empty($showPromoPeriod) && $product->relationLoaded('promos') && $product->promos->isNotEmpty())
                @php
                    $promoAwal = $product->promos->min('awal');
                    $promoAkhir = $product->promos->max('akhir');
                @endphp
                <div class="font-small text-brand mt-5">
                    <i class="fi-rs-calendar mr-5"></i>
                    Berlaku: {{ $promoAwal->format('d M Y H:i') }} – {{ $promoAkhir->format('d M Y H:i') }}
                </div>
            @endif
            <div class="product-card-bottom rg-product-footer">
                <div class="product-price">
                    <span>Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                    @if(isset($product->compare_price) && $product->compare_price > $product->price)
                        <span class="old-price">Rp{{ number_format($product->compare_price, 0, ',', '.') }}</span>
                    @endif
                </div>
                <div class="add-cart rg-add-cart-row">
                    @php
                        $inCartQty = isset($cartItemMap) ? ($cartItemMap[$product->id] ?? 0) : 0;
                        $displayQty = $inCartQty > 0 ? $inCartQty : 1;
                    @endphp

                    <div class="product-qty-selector" id="qty-selector-{{ $product->slug }}">
                        <div class="d-flex align-items-center justify-content-between rg-grid-qty-wrap">
                            <a href="javascript:void(0)" class="qty-down-grid" data-slug="{{ $product->slug }}">
                                <i class="fi-rs-minus" style="font-size: 14px; color: #253D4E; border: 1px solid #253D4E; border-radius: 50%; padding: 4px;"></i>
                            </a>
                            <input type="text"
                                class="qty-val-grid fw-bold"
                                id="qty-val-{{ $product->slug }}"
                                value="{{ $displayQty }}"
                                min="1"
                                inputmode="numeric"
                                autocomplete="off"
                                aria-label="Jumlah"
                                title="Jumlah">
                            <a href="javascript:void(0)" class="qty-up-grid" data-slug="{{ $product->slug }}">
                                <i class="fi-rs-plus" style="font-size: 14px; color: #253D4E; border: 1px solid #253D4E; border-radius: 50%; padding: 4px;"></i>
                            </a>
                        </div>
                    </div>

                    <form class="add-to-cart-form {{ $inCartQty > 0 ? 'd-none' : '' }}" id="add-form-{{ $product->slug }}" action="{{ route('cart.store', $product->slug) }}" method="POST"
                        @if($product->hasDualUnitOrdering())
                            data-dual-uom="1"
                            data-product-name="{{ $product->display_name }}"
                            data-unit-label="{{ $product->unit }}"
                            data-large-unit-label="{{ $product->large_unit }}"
                            data-units-per-large="{{ (int) $product->units_per_large }}"
                        @endif
                    >
                        @csrf
                        <input type="hidden" name="quantity" value="{{ $displayQty }}" class="js-qty-input">
                        <input type="hidden" name="warehouse_id" value="{{ session('selected_hub_id') }}">
                        @if($product->hasDualUnitOrdering())
                            <input type="hidden" name="uom" value="{{ (auth()->check() && auth()->user()->isDistributor()) ? 'large' : 'base' }}" class="js-cart-uom-field">
                        @endif
                        <button type="submit" class="add">
                            Beli
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
