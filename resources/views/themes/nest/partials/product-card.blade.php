<div class="{{ $columnClass ?? 'col-lg-1-5 col-md-4 col-12 col-sm-6' }} rg-product-col">
    <div class="product-cart-wrap mb-30 wow animate__animated animate__fadeIn rg-product-card" data-wow-delay=".1s">
        <div class="product-img-action-wrap rg-product-media">
            <div class="product-img product-img-zoom">
                <a href="{{ route('products.show', $product->slug) }}">
                    <img class="default-img rg-product-img" src="{{ $product->image_url ?? asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}';" />
                    <img class="hover-img rg-product-img" src="{{ $product->image_url ?? asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}';" />
                </a>
            </div>
            <div class="product-action-1">
                <a aria-label="Lihat cepat" class="action-btn btn-quick-view" data-bs-toggle="modal" data-bs-target="#quickViewModal" href="#" data-url="{{ route('products.quick-view', $product->slug) }}"><i class="fi-rs-eye"></i></a>
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
                <span class="font-small text-muted">Oleh <a href="#">{{ $product->brand->name ?? 'Tanpa Brand' }}</a></span>
                <span class="font-small ml-10 text-success">Stok: {{ $product->current_stock }}</span>
            </div>
            <div class="product-card-bottom rg-product-footer">
                <div class="product-price">
                    <span>Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                    @if(isset($product->compare_price) && $product->compare_price > $product->price)
                        <span class="old-price">Rp{{ number_format($product->compare_price, 0, ',', '.') }}</span>
                    @endif
                </div>
                <div class="add-cart">
                    <form class="add-to-cart-form" action="{{ route('cart.store', $product->slug) }}" method="POST"
                        @if($product->hasDualUnitOrdering())
                            data-dual-uom="1"
                            data-product-name="{{ $product->display_name }}"
                            data-unit-label="{{ $product->unit }}"
                            data-large-unit-label="{{ $product->large_unit }}"
                            data-units-per-large="{{ (int) $product->units_per_large }}"
                        @endif
                    >
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="warehouse_id" value="{{ session('selected_hub_id') }}">
                        @if($product->hasDualUnitOrdering())
                            <input type="hidden" name="uom" value="base" class="js-cart-uom-field">
                        @endif
                        <button type="submit" class="add">
                            Add <i class="fi-rs-plus ml-5"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
