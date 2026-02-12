<div class="product-cart-wrap small hover-up">
    <div class="product-img-action-wrap">
        <div class="product-img product-img-zoom">
            <a href="{{ route('products.show', $product->slug) }}">
                <img class="default-img" src="{{ $product->image_url ?? asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}';" />
                <img class="hover-img" src="{{ $product->image_url ?? asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}';" />
            </a>
        </div>
        <div class="product-action-1">
            <a aria-label="Quick view" class="action-btn small hover-up" data-bs-toggle="modal" data-bs-target="#quickViewModal"> <i class="fi-rs-eye"></i></a>
            <a aria-label="Add To Wishlist" class="action-btn small hover-up" href="shop-wishlist.html"><i class="fi-rs-heart"></i></a>
            <a aria-label="Compare" class="action-btn small hover-up" href="shop-compare.html"><i class="fi-rs-shuffle"></i></a>
        </div>
        <div class="product-badges product-badges-position product-badges-mrg">
             @if($product->created_at->diffInDays(now()) < 7)
                <span class="new">New</span>
            @elseif($product->price < ($product->compare_price ?? 0))
                <span class="hot">Save</span>
            @endif
        </div>
    </div>
    <div class="product-content-wrap">
        <div class="product-category">
            <a href="#">{{ $product->category->name ?? 'Uncategorized' }}</a>
        </div>
        <h2><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h2>
        <div class="product-rate d-inline-block">
             <div class="product-rating" style="width: {{ ($product->rating ?? 0) * 20 }}%"></div>
        </div>
        <div class="product-price mt-10">
            <span>Rp{{ number_format($product->price, 0, ',', '.') }}</span>
            @if(isset($product->compare_price) && $product->compare_price > $product->price)
                <span class="old-price">Rp{{ number_format($product->compare_price, 0, ',', '.') }}</span>
            @endif
        </div>
        <div class="sold mt-15 mb-15">
            <!-- Sold progress bar hidden until real data available -->
        </div>
        <a href="#" class="btn w-100 hover-up"><i class="fi-rs-shopping-cart mr-5"></i>Add To Cart</a>
    </div>
</div>
