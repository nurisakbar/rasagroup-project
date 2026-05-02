@extends('themes.nest.layouts.app')

@section('title', $product->display_name)
@section('meta_description', Str::limit(strip_tags($product->description), 160))
@section('og_image', $product->image_url)

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
            <span></span> <a href="{{ route('products.index') }}">Products</a> <span></span> {{ $product->display_name }}
        </div>
    </div>
</div>
<div class="container mb-30">
    <div class="row">
        <div class="col-xl-11 col-lg-12 m-auto">
            <div class="row">
                <div class="col-xl-9">
                    <div class="product-detail accordion-detail">
                        <div class="row mb-50 mt-30">
                            <!-- Product Images -->
                            <div class="col-md-6 col-sm-12 col-xs-12 mb-md-0 mb-sm-5">
                                <div class="detail-gallery">
                                    <span class="zoom-icon"><i class="fi-rs-search"></i></span>
                                    <!-- MAIN SLIDES -->
                                    <div class="product-image-slider">
                                        <figure class="border-radius-10">
                                            <img src="{{ $product->image_url ? asset($product->image_url) : asset('themes/nest-frontend/assets/imgs/shop/product-16-2.jpg') }}" alt="{{ $product->display_name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-16-2.jpg') }}';" />
                                        </figure>
                                        <!-- Duplicate figures if you want slider effect with same image -->
                                        <figure class="border-radius-10">
                                            <img src="{{ $product->image_url ? asset($product->image_url) : asset('themes/nest-frontend/assets/imgs/shop/product-16-2.jpg') }}" alt="{{ $product->display_name }}" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-16-2.jpg') }}';" />
                                        </figure>
                                    </div>
                                    <!-- THUMBNAILS -->
                                    <div class="slider-nav-thumbnails">
                                        <div><img src="{{ $product->image_url ? asset($product->image_url) : asset('themes/nest-frontend/assets/imgs/shop/thumbnail-3.jpg') }}" alt="product image" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/thumbnail-3.jpg') }}';" /></div>
                                        <div><img src="{{ $product->image_url ? asset($product->image_url) : asset('themes/nest-frontend/assets/imgs/shop/thumbnail-3.jpg') }}" alt="product image" onerror="this.onerror=null;this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/thumbnail-3.jpg') }}';" /></div>
                                    </div>
                                </div>
                                <!-- End Gallery -->
                            </div>
                            
                            <!-- Product Details -->
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="detail-info pr-30 pl-30">
                                    @if(isset($product->compare_price) && $product->compare_price > $product->price)
                                        <span class="stock-status out-stock"> Sale Off </span>
                                    @endif
                                    <h2 class="title-detail product-detail-title">{{ $product->display_name }}</h2>
                                    
                                    <!-- Rating -->
                                    <div class="product-detail-rating">
                                        <div class="product-rate-cover text-end">
                                            <div class="product-rate d-inline-block">
                                                <div class="product-rating" style="width: {{ ($product->rating ?? 0) * 20 }}%"></div>
                                            </div>
                                            <span class="font-small ml-5 text-muted"> ({{ $product->reviews_count ?? 0 }} reviews)</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Price -->
                                    <div class="product-detail-price-block mb-3">
                                        <div class="product-price primary-color">
                                            <span class="current-price text-brand product-detail-price-amount">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                            @if(isset($product->compare_price) && $product->compare_price > $product->price)
                                                <div class="product-detail-price-promo mt-2">
                                                    <span class="save-price font-md color3">{{ round((($product->compare_price - $product->price)/$product->compare_price)*100) }}% Off</span>
                                                    <span class="old-price font-md ms-2">Rp {{ number_format($product->compare_price, 0, ',', '.') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="product-detail-stock-below-price mb-3">
                                        <span class="product-detail-stock-below-label">Stok</span>
                                        <span class="product-detail-stock-below-value text-brand">{{ $product->current_stock }}</span>
                                        @if($product->unit)
                                            <span class="product-detail-stock-below-unit text-muted">{{ $product->unit }}</span>
                                        @endif
                                    </div>

                                    <div class="product-detail-brand-category mb-30">
                                        <div class="product-detail-meta-card">
                                            <span class="product-detail-meta-label">Brand</span>
                                            <span class="product-detail-meta-value">{{ $product->brand->name ?? '—' }}</span>
                                        </div>
                                        <div class="product-detail-meta-card">
                                            <span class="product-detail-meta-label">Kategori</span>
                                            <span class="product-detail-meta-value">{{ $product->category->name ?? '—' }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="short-desc mb-30">
                                        <p class="font-lg">{{ Str::limit(strip_tags($product->description), 150) }}</p>
                                    </div>
                                    
                                    <!-- HUB SELECTION -->
                                    <div class="mb-4">
                                        @if(!$selectedWarehouseId)
                                            <!-- Hub Selection (only show if warehouse_id not in URL) -->
                                            <div class="hub-selection p-4" style="background: #f8f9fa; border-radius: 15px; border: 2px solid #e9ecef;">
                                                <h6 class="fw-bold mb-3"><i class="fi-rs-marker me-2 text-primary"></i>Pilih Hub Pengirim</h6>
                                                <div id="hub-loading" class="text-center py-3">
                                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <span class="ms-2 text-muted">Memuat ketersediaan stock...</span>
                                                </div>
                                                <div id="hub-list" class="d-none">
                                                    <!-- Hubs will be loaded here -->
                                                </div>
                                                <div id="hub-empty" class="d-none text-center py-3">
                                                    <i class="fi-rs-sad text-warning" style="font-size: 2rem;"></i>
                                                    <p class="text-muted mt-2 mb-0">Produk ini belum tersedia di hub manapun.</p>
                                                </div>
                                            </div>
                                        @else
                                            <!-- Hub Info (when warehouse_id is preselected) -->
                                            <div class="hub-selection p-4" style="background: #e3f2fd; border-radius: 15px; border: 2px solid #2196f3;">
                                                <h6 class="fw-bold mb-3"><i class="fi-rs-marker me-2 text-primary"></i>Hub Pengirim</h6>
                                                <div id="hub-loading" class="text-center py-3">
                                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <span class="ms-2 text-muted">Memuat informasi hub...</span>
                                                </div>
                                                <div id="hub-info" class="d-none">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong id="hub-info-name"></strong>
                                                            <br>
                                                            <small class="text-muted" id="hub-info-location"></small>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge bg-success" id="hub-info-stock">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="hub-error" class="d-none text-center py-3">
                                                    <i class="fi-rs-sad text-danger" style="font-size: 2rem;"></i>
                                                    <p class="text-danger mt-2 mb-0">Hub tidak ditemukan atau produk tidak tersedia di hub ini.</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- ADD TO CART FORM -->
                                    <form action="{{ route('cart.store', $product) }}" method="POST" id="add-to-cart-form">
                                        @csrf
                                        <input type="hidden" name="warehouse_id" id="selected-warehouse-id" value="{{ $selectedWarehouseId ?? '' }}">
                                        <input type="hidden" name="uom" id="uom-field" value="base">

                                        @if($product->hasDualUnitOrdering())
                                            <div class="product-uom-picker-wrap mb-3">
                                                <div class="product-uom-picker" role="radiogroup" aria-labelledby="product-uom-heading">
                                                    <span id="product-uom-heading" class="product-uom-picker-title">Satuan pembelian</span>
                                                    <div class="product-uom-cards">
                                                        <label class="product-uom-card" data-uom-card="base" for="product-uom-base" aria-label="Beli per {{ $product->unit }}">
                                                            <input class="product-uom-card-input" type="radio" name="uom_pick" id="product-uom-base" value="base" checked autocomplete="off">
                                                            <span class="product-uom-card-face">
                                                                <span class="product-uom-card-radio" aria-hidden="true"></span>
                                                                <span class="product-uom-card-row product-uom-card-row--solo-chip">
                                                                    <span class="product-uom-card-chip">{{ $product->unit }}</span>
                                                                </span>
                                                            </span>
                                                        </label>
                                                        <label class="product-uom-card" data-uom-card="large" for="product-uom-large" aria-label="Beli per {{ $product->large_unit }}">
                                                            <input class="product-uom-card-input" type="radio" name="uom_pick" id="product-uom-large" value="large" autocomplete="off">
                                                            <span class="product-uom-card-face">
                                                                <span class="product-uom-card-radio" aria-hidden="true"></span>
                                                                <span class="product-uom-card-row product-uom-card-row--solo-chip">
                                                                    <span class="product-uom-card-chip product-uom-card-chip--outline">{{ $product->large_unit }}</span>
                                                                </span>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="detail-extralink mb-50 product-detail-cart-row product-detail-qty-cta">
                                            <div class="detail-qty border radius">
                                                <a href="#" class="qty-down" onclick="decreaseQty(); return false;"><i class="fi-rs-angle-small-down"></i></a>
                                                <input type="text" name="quantity" id="quantity" class="qty-val" value="1" min="1" inputmode="numeric" title="Jumlah menurut satuan yang dipilih">
                                                <a href="#" class="qty-up" onclick="increaseQty(); return false;"><i class="fi-rs-angle-small-up"></i></a>
                                            </div>
                                            <div class="product-extra-link2 product-detail-cta-wrap">
                                                <button type="submit" class="btn button-add-to-cart product-add-cart-btn" id="add-to-cart-btn" {{ !$selectedWarehouseId ? 'disabled' : '' }}>
                                                    <i class="fi-rs-shopping-cart"></i> {{ $selectedWarehouseId ? 'Tambah ke Keranjang' : 'Pilih Hub Terlebih Dahulu' }}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-group mt-3 product-detail-subtotal-row">
                                            <span class="product-detail-subtotal-label">Subtotal</span>
                                            <span id="subtotal" class="text-brand product-detail-subtotal-amount">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                        </div>
                                    </form>
                                </div>
                                <!-- Detail Info -->
                            </div>
                        </div>
                        
                        <!-- Tabs -->
                        <div class="product-info">
                            <div class="tab-style3">
                                <ul class="nav nav-tabs text-uppercase">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="Description-tab" data-bs-toggle="tab" href="#Description">Deskripsi</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="Reviews-tab" data-bs-toggle="tab" href="#Reviews">Ulasan ({{ count($dummyReviews) }})</a>
                                    </li>
                                </ul>
                                <div class="tab-content shop_info_tab entry-main-content">
                                    <div class="tab-pane fade show active" id="Description">
                                        <div class="">
                                            {!! $product->description !!}
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="Reviews">
                                        <div class="comments-area">
                                            <div class="row">
                                                <div class="col-lg-8">
                                                    <h4 class="mb-30">Ulasan Pelanggan</h4>
                                                    <div class="comment-list">
                                                        @foreach($dummyReviews as $review)
                                                            <div class="single-comment justify-content-between d-flex mb-30">
                                                                <div class="user justify-content-between d-flex">
                                                                    <div class="thumb text-center">
                                                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/blog/author-1.png') }}" alt="" />
                                                                        <a href="#" class="font-heading text-brand">{{ $review['user'] }}</a>
                                                                    </div>
                                                                    <div class="desc">
                                                                        <div class="d-flex justify-content-between mb-10">
                                                                            <div class="d-flex align-items-center">
                                                                                <span class="font-xs text-muted">{{ $review['date'] }} </span>
                                                                            </div>
                                                                            <div class="product-rate d-inline-block">
                                                                                <div class="product-rating" style="width: {{ $review['rating'] * 20 }}%"></div>
                                                                            </div>
                                                                        </div>
                                                                        <p class="mb-10">{{ $review['comment'] }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Related Products -->
                        <div class="row mt-60">
                            <div class="col-12">
                                <h2 class="section-title style-1 mb-30">Produk Terkait</h2>
                            </div>
                            <div class="col-12">
                                <div class="row related-products">
                                     @forelse($relatedProducts as $related)
                                        @include('themes.nest.partials.product-card', ['product' => $related, 'columnClass' => 'col-lg-4 col-md-6 col-12'])
                                     @empty
                                        <div class="col-12">
                                            <p class="text-muted">Tidak ada produk terkait ditemukan.</p>
                                        </div>
                                     @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-xl-3 primary-sidebar sticky-sidebar mt-30">
                    <div class="sidebar-widget widget-category-2 mb-30">
                        <h5 class="section-title style-1 mb-30">Category</h5>
                         <ul>
                            @foreach($globalCategories as $cat)
                                <li>
                                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}">
                                        @if($cat->image)
                                            <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}" />
                                        @else
                                            <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/category-1.svg') }}" alt="" />
                                        @endif
                                        {{ $cat->name }}
                                    </a>
                                    <span class="count">{{ $cat->products_count }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <!-- Fillter By Price (Hidden) -->
                    {{-- 
                    <div class="sidebar-widget price_range range mb-30">
                        <h5 class="section-title style-1 mb-30">Fill by price</h5>
                        <div class="price-filter">
                            <div class="price-filter-inner">
                                <div id="slider-range" class="mb-20"></div>
                                <div class="d-flex justify-content-between">
                                    <div class="caption">From: <strong id="slider-range-value1" class="text-brand"></strong></div>
                                    <div class="caption">To: <strong id="slider-range-value2" class="text-brand"></strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    --}}
                    <!-- Product sidebar Widget -->
                    <div class="sidebar-widget product-sidebar mb-30 p-30 bg-grey border-radius-10">
                        <h5 class="section-title style-1 mb-30">New products</h5>
                        <!-- Static Content for now -->
                        <div class="single-post clearfix">
                            <div class="image">
                                <img src="{{ asset('themes/nest-frontend/assets/imgs/shop/thumbnail-3.jpg') }}" alt="#" />
                            </div>
                            <div class="content pt-10">
                                <h5><a href="#">Chen Cardigan</a></h5>
                                <p class="price mb-0 mt-5">Rp 99.500</p>
                                <div class="product-rate">
                                    <div class="product-rating" style="width: 90%"></div>
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

@push('scripts')
<script>
    const price = {{ $product->price }};
    let selectedStock = 0;
    let selectedWarehouseId = null;
    const hasDualUom = @json($product->hasDualUnitOrdering());
    const unitsPerLarge = {{ (int) ($product->units_per_large ?? 0) }};
    let uomMode = 'base';
    const baseUnitLabel = @json($product->unit ?: 'unit');
    const largeUnitLabel = @json($product->large_unit ?: '');

    function getMaxOrderQty() {
        if (selectedStock <= 0) {
            return 0;
        }
        if (!hasDualUom || uomMode === 'base') {
            return selectedStock;
        }
        return Math.floor(selectedStock / unitsPerLarge);
    }

    function getBaseQty() {
        const qty = parseInt(document.getElementById('quantity').value, 10) || 1;
        if (hasDualUom && uomMode === 'large') {
            return qty * unitsPerLarge;
        }
        return qty;
    }

    function updateQtyUomHint() {
        const el = document.getElementById('qty-uom-hint');
        if (!el) {
            return;
        }
        if (!hasDualUom) {
            el.textContent = '';
            return;
        }
        const q = parseInt(document.getElementById('quantity').value, 10) || 1;
        if (uomMode === 'large') {
            const base = q * unitsPerLarge;
            el.textContent = `Setara ${base} ${baseUnitLabel} di keranjang (stok dihitung per ${baseUnitLabel}).`;
        } else {
            el.textContent = `Jumlah dalam ${baseUnitLabel}.`;
        }
    }

    function refreshUomUi() {
        if (!hasDualUom) {
            return;
        }
        const largeRadio = document.getElementById('product-uom-large');
        const maxLarge = selectedStock > 0 ? Math.floor(selectedStock / unitsPerLarge) : 0;
        if (largeRadio) {
            largeRadio.disabled = maxLarge < 1;
            const largeCard = document.querySelector('[data-uom-card="large"]');
            if (largeCard) {
                largeCard.classList.toggle('product-uom-card--disabled', maxLarge < 1);
            }
            if (maxLarge < 1 && uomMode === 'large') {
                const baseRadio = document.getElementById('product-uom-base');
                if (baseRadio) {
                    baseRadio.checked = true;
                }
                uomMode = 'base';
                const uomField = document.getElementById('uom-field');
                if (uomField) {
                    uomField.value = 'base';
                }
            }
        }
        updateQtyUomHint();
    }

    function updateSubtotal() {
        const subtotal = price * getBaseQty();
        document.getElementById('subtotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
        updateQtyUomHint();
    }

    function increaseQty() {
        const input = document.getElementById('quantity');
        const maxOrder = getMaxOrderQty();
        if (maxOrder < 1) {
            return;
        }
        let newVal = parseInt(input.value, 10) + 1;
        if (newVal > maxOrder) {
            newVal = maxOrder;
        }
        input.value = Math.max(1, newVal);
        updateSubtotal();
    }

    function decreaseQty() {
        const input = document.getElementById('quantity');
        if (parseInt(input.value, 10) > 1) {
            input.value = parseInt(input.value, 10) - 1;
            updateSubtotal();
        }
    }

    document.getElementById('quantity').addEventListener('change', function() {
        let v = parseInt(this.value, 10) || 1;
        if (v < 1) {
            v = 1;
        }
        const maxOrder = getMaxOrderQty();
        if (maxOrder > 0 && v > maxOrder) {
            v = maxOrder;
        }
        this.value = Math.max(1, v);
        updateSubtotal();
    });

    function selectHub(warehouseId, warehouseName, stock) {
        selectedWarehouseId = warehouseId;
        selectedStock = stock;

        const warehouseInput = document.getElementById('selected-warehouse-id');
        if (warehouseInput) {
            warehouseInput.value = warehouseId;
        }

        const hubOptions = document.querySelectorAll('.hub-option');
        if (hubOptions.length > 0) {
            hubOptions.forEach(el => {
                el.classList.remove('selected');
            });
            const selectedOption = document.querySelector(`[data-warehouse-id="${warehouseId}"]`);
            if (selectedOption) {
                selectedOption.classList.add('selected');
            }
        }

        const btn = document.getElementById('add-to-cart-btn');
        if (btn) {
            if (stock < 1) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fi-rs-cross-circle"></i> Stok tidak tersedia';
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fi-rs-shopping-cart"></i> Tambah ke Keranjang';
            }
        }

        const maxStockHint = document.getElementById('max-stock-hint');
        if (maxStockHint) {
            let hint = stock < 1
                ? `Stok habis (0 ${baseUnitLabel})`
                : `Stok tersedia: ${stock} ${baseUnitLabel}`;
            if (hasDualUom && unitsPerLarge > 1) {
                const maxLarge = Math.floor(stock / unitsPerLarge);
                hint += ` (maks. ${maxLarge} ${largeUnitLabel} jika order per ${largeUnitLabel})`;
            }
            maxStockHint.textContent = hint;
        }

        const qtyInput = document.getElementById('quantity');
        if (qtyInput) {
            const maxOrder = getMaxOrderQty();
            if (maxOrder > 0 && parseInt(qtyInput.value, 10) > maxOrder) {
                qtyInput.value = Math.max(1, maxOrder);
            }
            if (maxOrder < 1) {
                qtyInput.value = 1;
            }
            updateSubtotal();
        }
        refreshUomUi();
    }

    function loadHubStock() {
        // Get warehouse_id from URL parameter or blade variable
        const urlParams = new URLSearchParams(window.location.search);
        const preselectedWarehouseId = urlParams.get('warehouse_id') || '{{ $selectedWarehouseId ?? '' }}';
        
        @if($selectedWarehouseId)
        // If warehouse_id is preselected, load only that warehouse's stock
        loadPreselectedHub('{{ $selectedWarehouseId }}');
        @else
        // Otherwise, load all hubs with stock
        loadAllHubs(preselectedWarehouseId);
        @endif
    }
    
    function loadPreselectedHub(warehouseId) {
        const loadingEl = document.getElementById('hub-loading');
        const infoEl = document.getElementById('hub-info');
        const errorEl = document.getElementById('hub-error');
        
        loadingEl.classList.remove('d-none');
        infoEl.classList.add('d-none');
        errorEl.classList.add('d-none');
        
        // Fetch stock for specific warehouse
        fetch('{{ route("cart.product-stock", $product) }}?warehouse_id=' + warehouseId)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Failed to fetch stock');
                    }).catch(() => {
                        throw new Error('Failed to fetch stock');
                    });
                }
                return response.json();
            })
            .then(data => {
                const targetStock = data.stocks?.[0]; // Should only have one result
                
                if (!targetStock) {
                    loadingEl.classList.add('d-none');
                    errorEl.classList.remove('d-none');
                    return;
                }
                
                try {
                    loadingEl.classList.add('d-none');
                    
                    // Display hub info
                    const hubInfoName = document.getElementById('hub-info-name');
                    const hubInfoLocation = document.getElementById('hub-info-location');
                    const stockBadge = document.getElementById('hub-info-stock');
                    
                    if (hubInfoName && hubInfoLocation && stockBadge) {
                        hubInfoName.textContent = targetStock.warehouse_name;
                        hubInfoLocation.innerHTML = `<i class="fi-rs-marker me-1"></i>${targetStock.warehouse_location}`;
                        
                        if (targetStock.stock > 0) {
                            stockBadge.className = 'badge bg-success';
                            stockBadge.textContent = `${targetStock.stock} ${baseUnitLabel}`;
                            infoEl.classList.remove('d-none');
                            selectHub(targetStock.warehouse_id, targetStock.warehouse_name, targetStock.stock);
                        } else {
                            stockBadge.className = 'badge bg-danger';
                            stockBadge.textContent = 'Stock Habis';
                            infoEl.classList.remove('d-none');
                            
                            const btn = document.getElementById('add-to-cart-btn');
                            if (btn) {
                                btn.disabled = true;
                                btn.innerHTML = '<i class="fi-rs-cross-circle me-2"></i> Stock Tidak Tersedia';
                            }
                        }
                    }
                } catch (error) {
                    loadingEl.classList.add('d-none');
                    errorEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                loadingEl.classList.add('d-none');
                errorEl.classList.remove('d-none');
            });
    }
    
    function loadAllHubs(preselectedWarehouseId) {
        const loadingEl = document.getElementById('hub-loading');
        const listEl = document.getElementById('hub-list');
        const emptyEl = document.getElementById('hub-empty');
        
        loadingEl.classList.remove('d-none');
        listEl.classList.add('d-none');
        emptyEl.classList.add('d-none');
        
        fetch('{{ route("cart.product-stock", $product) }}')
            .then(response => response.json())
            .then(data => {
                loadingEl.classList.add('d-none');
                
                if (data.stocks && data.stocks.length > 0) {
                    let html = '<div class="row g-2">';
                    data.stocks.forEach(stock => {
                        const badgeClass = Number(stock.stock) > 0 ? 'bg-success' : 'bg-secondary';
                        html += `
                            <div class="col-12">
                                <div class="hub-option p-3" data-warehouse-id="${stock.warehouse_id}" onclick="selectHub('${stock.warehouse_id}', '${stock.warehouse_name}', ${stock.stock})" style="cursor: pointer; border: 2px solid #e9ecef; border-radius: 10px; transition: all 0.2s;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>${stock.warehouse_name}</strong>
                                            <br>
                                            <small class="text-muted"><i class="fi-rs-marker me-1"></i>${stock.warehouse_location}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge ${badgeClass}">${stock.stock} ${baseUnitLabel}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    listEl.innerHTML = html;
                    listEl.classList.remove('d-none');
                    
                    if (preselectedWarehouseId) {
                        const targetStock = data.stocks.find(s => s.warehouse_id === preselectedWarehouseId);
                        if (targetStock) {
                            setTimeout(() => {
                                selectHub(targetStock.warehouse_id, targetStock.warehouse_name, targetStock.stock);
                            }, 100);
                        }
                    }
                } else {
                    emptyEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                loadingEl.classList.add('d-none');
                emptyEl.innerHTML = '<i class="fi-rs-sad text-danger" style="font-size: 2rem;"></i><p class="text-danger mt-2 mb-0">Gagal memuat data.</p>';
                emptyEl.classList.remove('d-none');
            });
    }

    document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!selectedWarehouseId) {
            showShopToast('Silakan pilih hub pengirim terlebih dahulu.', 'warning');
            return false;
        }

        const form = $(this);
        const url = form.attr('action');
        const data = form.serialize();

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                if (response.success) {
                    showShopToast(response.message, 'success');
                    
                    $('.pro-count.blue').text(response.cart_count);
                    $('.pro-count.white').text(response.cart_count);
                    if (response.mini_cart_html) {
                        $('.cart-dropdown-wrap').html(response.mini_cart_html);
                    }
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    if (confirm("Silakan masuk terlebih dahulu untuk belanja. Masuk sekarang?")) {
                        window.location.href = '{{ route("login") }}';
                    }
                    return;
                }

                let errorMsg = "Terjadi kesalahan saat menambahkan ke keranjang.";
                const body = xhr.responseJSON || {};
                if (xhr.status === 422) {
                    if (body.errors) {
                        errorMsg = Object.values(body.errors).flat()[0];
                    } else if (body.error) {
                        errorMsg = body.error;
                    } else if (body.message) {
                        errorMsg = body.message;
                    }
                } else if (body.error) {
                    errorMsg = body.error;
                }
                
                showShopToast(errorMsg, 'error');
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[name="uom_pick"]').forEach(function(r) {
            r.addEventListener('change', function() {
                uomMode = this.value;
                const uomField = document.getElementById('uom-field');
                if (uomField) {
                    uomField.value = uomMode;
                }
                const maxOrder = getMaxOrderQty();
                const qtyInput = document.getElementById('quantity');
                if (qtyInput) {
                    if (maxOrder > 0 && parseInt(qtyInput.value, 10) > maxOrder) {
                        qtyInput.value = Math.max(1, maxOrder);
                    }
                    if (maxOrder < 1) {
                        qtyInput.value = 1;
                    }
                }
                updateSubtotal();
            });
        });
        loadHubStock();
    });
</script>
@endpush

@push('styles')
<style>
    .product-detail-cart-row {
        align-items: center !important;
    }
    .detail-info .product-detail-title.title-detail {
        font-family: 'Fira Sans', sans-serif;
        font-size: 35px !important;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.02em;
        color: #253D4E;
    }
    .product-detail-price-block .product-detail-price-amount {
        font-family: 'Fira Sans', sans-serif;
        font-size: 30px !important;
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.02em;
        color: #6A1B1B !important;
    }
    .product-detail-stock-below-price {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 0.35rem 0.5rem;
        font-family: 'Fira Sans', sans-serif;
    }
    .product-detail-stock-below-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #8B7355;
    }
    .product-detail-stock-below-value {
        font-size: 1.125rem;
        font-weight: 700;
    }
    .product-detail-stock-below-unit {
        font-size: 0.9375rem;
    }
    .product-detail-price-block .float-left,
    .product-detail-price-block .clearfix {
        float: none !important;
    }
    .product-detail-brand-category {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1rem;
    }
    .product-detail-meta-card {
        flex: 1 1 140px;
        min-width: min(100%, 160px);
        padding: 0.65rem 0.9rem;
        background: #fff;
        border: 1px solid #E8DDD4;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(24, 24, 24, 0.04);
    }
    .product-detail-meta-label {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #8B7355;
        margin-bottom: 0.2rem;
        font-family: 'Fira Sans', sans-serif;
    }
    .product-detail-meta-value {
        display: block;
        font-size: 1.05rem;
        font-weight: 700;
        color: #253D4E;
        font-family: 'Fira Sans', sans-serif;
        line-height: 1.25;
    }
    .product-detail-qty-cta {
        width: 100%;
    }
    .product-detail-qty-cta .product-detail-cta-wrap {
        flex: 1 1 200px;
        min-width: min(100%, 220px);
    }
    .product-detail-qty-cta .product-add-cart-btn {
        width: 100%;
        min-width: 0 !important;
    }
    .product-detail-subtotal-row {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }
    .product-detail-subtotal-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-family: 'Fira Sans', sans-serif;
    }
    .product-detail-subtotal-amount {
        font-family: 'Fira Sans', sans-serif;
        font-size: 1.5rem;
        font-weight: 800;
        color: #6A1B1B !important;
        letter-spacing: -0.02em;
    }
    .product-uom-picker {
        background: #faf8f6;
        border-radius: 12px;
        border: 1px solid #E8DDD4;
        padding: 0.75rem 0.75rem 0.85rem;
    }
    .product-uom-picker-title {
        font-family: 'Fira Sans', sans-serif;
        font-size: 0.9375rem;
        font-weight: 700;
        color: #253D4E;
        display: block;
        margin-bottom: 0.65rem;
        letter-spacing: -0.01em;
    }
    .product-uom-cards {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }
    .product-uom-card {
        margin: 0;
        cursor: pointer;
        display: block;
    }
    .product-uom-card-input {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
        pointer-events: none;
    }
    .product-uom-card-face {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.7rem 0.85rem;
        border-radius: 10px;
        border: 2px solid #E5E0D8;
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        min-height: 3rem;
    }
    .product-uom-card:hover .product-uom-card-face {
        border-color: #C4A8A4;
        box-shadow: 0 4px 14px rgba(106, 27, 27, 0.08);
    }
    .product-uom-card-input:focus-visible + .product-uom-card-face {
        outline: 2px solid #6A1B1B;
        outline-offset: 2px;
    }
    .product-uom-card-input:not(:checked) + .product-uom-card-face {
        background: #f5f3f0;
    }
    .product-uom-card-input:checked + .product-uom-card-face {
        border-color: #6A1B1B;
        background: #fff;
        box-shadow: 0 2px 12px rgba(106, 27, 27, 0.1);
    }
    .product-uom-card-input:checked + .product-uom-card-face .product-uom-card-radio {
        border-color: #6A1B1B;
        background: #6A1B1B;
        box-shadow: inset 0 0 0 3px #fff;
    }
    .product-uom-card-radio {
        flex-shrink: 0;
        width: 1.125rem;
        height: 1.125rem;
        border-radius: 50%;
        border: 2px solid #B8B2A8;
        transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
    }
    .product-uom-card-row {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.35rem 0.5rem;
        font-family: 'Fira Sans', sans-serif;
    }
    .product-uom-card-row--solo-chip .product-uom-card-chip {
        font-size: 0.75rem;
        padding: 0.22rem 0.5rem;
    }
    .product-uom-card-name {
        font-weight: 700;
        font-size: 0.9375rem;
        color: #253D4E;
    }
    .product-uom-card-chip {
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 0.18rem 0.42rem;
        border-radius: 6px;
        background: rgba(106, 27, 27, 0.12);
        color: #6A1B1B;
    }
    .product-uom-card-chip--outline {
        background: #fff;
        border: 1px solid #D0B3AD;
        color: #5c4a48;
    }
    .product-uom-card--disabled {
        pointer-events: none;
    }
    .product-uom-card--disabled .product-uom-card-face {
        opacity: 0.55;
        cursor: not-allowed;
        background: #f5f3f0;
    }
    .product-uom-card--disabled:hover .product-uom-card-face {
        border-color: #E5E0D8;
        box-shadow: none;
    }
    @media (min-width: 520px) {
        .product-uom-cards {
            flex-direction: row;
            align-items: stretch;
        }
        .product-uom-card {
            flex: 1;
            min-width: 0;
        }
    }
    .product-add-cart-btn {
        width: 100%;
    }
    @media (min-width: 576px) {
        .product-add-cart-btn {
            width: auto;
            min-width: 220px;
        }
        .product-detail-qty-cta .product-add-cart-btn {
            width: 100%;
            min-width: 0 !important;
        }
    }
    .hub-option:hover {
        border-color: var(--primary-color) !important;
        background: #fff5f5;
    }
    .hub-option.selected {
        border-color: var(--primary-color) !important;
        background: linear-gradient(135deg, #fff5f5 0%, #fef5e7 100%);
    }
    .hub-option.selected::after {
        content: '✓';
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary-color);
        font-weight: bold;
        font-size: 1.2rem;
    }
    .hub-option {
        position: relative;
    }
</style>
@endpush
