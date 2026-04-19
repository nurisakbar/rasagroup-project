<div class="row">
    <div class="col-md-6 col-sm-12 col-xs-12 mb-md-0 mb-sm-5">
        <div class="detail-gallery">
            <span class="zoom-icon"><i class="fi-rs-search"></i></span>
            <!-- MAIN SLIDES -->
            <div class="product-image-slider">
                <figure class="border-radius-10">
                    <img src="{{ $product->image_url ?? asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="{{ $product->display_name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}';" />
                </figure>
                @if($product->images)
                    @foreach(json_decode($product->images) as $img)
                        <figure class="border-radius-10">
                            <img src="{{ asset('storage/' . $img) }}" alt="{{ $product->display_name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}';" />
                        </figure>
                    @endforeach
                @endif
            </div>
            <!-- THUMBNAILS -->
            <div class="slider-nav-thumbnails">
                <div><img src="{{ $product->image_url ?? asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="{{ $product->display_name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}';" /></div>
                @if($product->images)
                    @foreach(json_decode($product->images) as $img)
                        <div><img src="{{ asset('storage/' . $img) }}" alt="{{ $product->display_name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}';" /></div>
                    @endforeach
                @endif
            </div>
        </div>
        <!-- End Gallery -->
    </div>
    <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="detail-info pr-30 pl-30">
            @if($product->price < ($product->compare_price ?? 0))
                <span class="stock-status out-stock"> Diskon </span>
            @endif
            <h3 class="title-detail"><a href="{{ route('products.show', $product->slug) }}" class="text-heading">{{ $product->display_name }}</a></h3>
            <div class="product-detail-rating">
                <div class="product-rate-cover text-end">
                    <div class="product-rate d-inline-block">
                        <div class="product-rating" style="width: {{ ($product->rating ?? 0) * 20 }}%"></div>
                    </div>
                    <span class="font-small ml-5 text-muted"> ({{ number_format($product->rating ?? 0, 1) }})</span>
                </div>
            </div>
            <div class="clearfix product-price-cover">
                <div class="product-price primary-color float-left">
                    <span class="current-price text-brand">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                    @if(isset($product->compare_price) && $product->compare_price > $product->price)
                        <span>
                             <span class="save-price font-md color3 ml-15">Hemat {{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}%</span>
                            <span class="old-price font-md ml-15">Rp{{ number_format($product->compare_price, 0, ',', '.') }}</span>
                        </span>
                    @endif
                </div>
            </div>
            <div class="detail-extralink mb-30">
                <form class="add-to-cart-form" action="{{ route('cart.store', $product->slug) }}" method="POST">
                    @csrf
                    <div class="detail-qty border radius">
                        <a href="#" class="qty-down"><i class="fi-rs-angle-small-down"></i></a>
                        <input type="number" name="quantity" class="qty-val" value="1" min="1" style="width: 50px; border: none; text-align: center;">
                        <a href="#" class="qty-up"><i class="fi-rs-angle-small-up"></i></a>
                    </div>
                    <input type="hidden" name="warehouse_id" value="{{ $selectedHubId }}">
                    <div class="product-extra-link2 mt-15">
                        <button type="submit" class="add" style="border: none; cursor: pointer; background-color: #801D1D; color: #ffffff; border-radius: 4px; font-weight: bold; opacity: 1; transition: background-color 0.3s; padding: 10px 20px;">
                            <i class="fi-rs-shopping-cart mr-5"></i>Add To Cart
                        </button>
                    </div>
                </form>
            </div>
            <div class="font-xs">
                <ul>
                    <li class="mb-5">Brand: <span class="text-brand">{{ $product->brand->name ?? '-' }}</span></li>
                    <li class="mb-5">Kategori: <span class="text-brand">{{ $product->category->name ?? '-' }}</span></li>
                    <li class="mb-5">Berat: <span class="text-brand">{{ $product->formatted_weight }}</span></li>
                </ul>
            </div>
        </div>
        <!-- Detail Info -->
    </div>
</div>
