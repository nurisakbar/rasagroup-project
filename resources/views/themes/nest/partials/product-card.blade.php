<div class="col-lg-1-5 col-md-4 col-12 col-sm-6">
    <div class="product-cart-wrap mb-30 wow animate__animated animate__fadeIn" data-wow-delay=".1s">
        <div class="product-img-action-wrap">
            <div class="product-img product-img-zoom">
                <a href="{{ route('products.show', $product->slug) }}">
                    <img class="default-img" src="{{ $product->image_url ?? asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}';" />
                    <img class="hover-img" src="{{ $product->image_url ?? asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}';" />
                </a>
            </div>
            <div class="product-action-1">
                <a aria-label="Quick view" class="action-btn btn-quick-view" data-bs-toggle="modal" data-bs-target="#quickViewModal" href="#" data-url="{{ route('products.quick-view', $product->slug) }}"><i class="fi-rs-eye"></i></a>
            </div>
            <div class="product-badges product-badges-position product-badges-mrg">
                @if($product->created_at->diffInDays(now()) < 7)
                    <span class="new">New</span>
                @elseif($product->price < ($product->compare_price ?? 0))
                    <span class="hot">Hot</span>
                @endif
            </div>
        </div>
        <div class="product-content-wrap">
            <div class="product-category">
                <a href="#">{{ $product->category->name ?? 'Uncategorized' }}</a>
            </div>
            <h2><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h2>
            <div class="product-rate-cover">
                <div class="product-rate d-inline-block">
                    <div class="product-rating" style="width: {{ ($product->rating ?? 0) * 20 }}%"></div>
                </div>
                <span class="font-small ml-5 text-muted"> ({{ number_format($product->rating ?? 0, 1) }})</span>
            </div>
            <div>
                <span class="font-small text-muted">By <a href="#">{{ $product->brand->name ?? 'Unknown' }}</a></span>
            </div>
            <div class="product-card-bottom">
                <div class="product-price">
                    <span>Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                    @if(isset($product->compare_price) && $product->compare_price > $product->price)
                        <span class="old-price">Rp{{ number_format($product->compare_price, 0, ',', '.') }}</span>
                    @endif
                </div>
                <div class="add-cart">
                    <form class="add-to-cart-form" action="{{ route('cart.store', $product->slug) }}" method="POST">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="warehouse_id" value="{{ session('selected_hub_id') }}">
                        <button type="submit" class="add" style="border: none; cursor: pointer; background-color: #3BB77E; color: #ffffff;">
                            <i class="fi-rs-shopping-cart mr-5"></i>Tambah
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
