<article class="row align-items-center hover-up">
    <figure class="col-md-4 mb-0">
        <a href="{{ route('products.show', $product->slug) }}">
            <img src="{{ $product->image_url ?? asset('themes/nest-frontend/assets/imgs/shop/thumbnail-1.jpg') }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/thumbnail-1.jpg') }}';" />
        </a>
    </figure>
    <div class="col-md-8 mb-0">
        <h6>
            <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
        </h6>
        <div class="product-rate-cover">
            <div class="product-rate d-inline-block">
                <div class="product-rating" style="width: {{ ($product->rating ?? 0) * 20 }}%"></div>
            </div>
            <span class="font-small ml-5 text-muted"> ({{ number_format($product->rating ?? 0, 1) }})</span>
        </div>
        <div class="product-price">
            <span>Rp{{ number_format($product->price, 0, ',', '.') }}</span>
            @if(isset($product->compare_price) && $product->compare_price > $product->price)
                <span class="old-price">Rp{{ number_format($product->compare_price, 0, ',', '.') }}</span>
            @endif
        </div>
    </div>
</article>
