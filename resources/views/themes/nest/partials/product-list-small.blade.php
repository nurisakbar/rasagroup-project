<article class="row align-items-center hover-up">
    <figure class="col-md-4 mb-0">
        <a href="{{ route('products.show', $product->slug) }}">
            <img src="{{ $product->image_url ?? asset('logo/Rasa Connect - Logo 2_Maroon 1.png') }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('logo/Rasa Connect - Logo 2_Maroon 1.png') }}';" @if(!$product->image) style="object-fit: contain; padding: 1rem;" @endif />
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
        @php
            $isDistributor = auth()->check() && auth()->user()->isDistributor();
            $multiplier = ($isDistributor && $product->hasDualUnitOrdering()) ? $product->unitsPerLargeEffective() : 1;
            $unitLabel = ($isDistributor && $product->hasDualUnitOrdering()) ? $product->large_unit : $product->unit;
        @endphp
        <div class="product-price">
            <span>{{ number_format($product->final_price * $multiplier, 0, ',', '.') }}</span>
            @if($unitLabel)
                <span class="text-muted" style="font-size: 0.8em; margin-left: 2px;">/ {{ $unitLabel }}</span>
            @endif
            @if(\App\Support\ShopFulfillment::showStockOnStorefront() && session('selected_hub_id'))
                <span class="font-small ml-10 text-success" style="font-size: 11px;">Stok: {{ $product->current_stock }}</span>
            @endif
            <br>
            @if($product->hasActiveDiscount() && $product->discount_price < $product->price)
                <span class="old-price">{{ number_format($product->price * $multiplier, 0, ',', '.') }}</span>
            @elseif(isset($product->compare_price) && $product->compare_price > $product->price)
                <span class="old-price">{{ number_format($product->compare_price * $multiplier, 0, ',', '.') }}</span>
            @endif
        </div>
    </div>
</article>
