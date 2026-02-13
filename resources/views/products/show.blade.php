@extends('themes.nest.layouts.app')

@section('title', $product->display_name)

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
                                    <h2 class="title-detail">{{ $product->display_name }}</h2>
                                    
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
                                    <div class="clearfix product-price-cover">
                                        <div class="product-price primary-color float-left">
                                            <span class="current-price text-brand">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                            @if(isset($product->compare_price) && $product->compare_price > $product->price)
                                                <span>
                                                    <span class="save-price font-md color3 ml-15">{{ round((($product->compare_price - $product->price)/$product->compare_price)*100) }}% Off</span>
                                                    <span class="old-price font-md ml-15">Rp {{ number_format($product->compare_price, 0, ',', '.') }}</span>
                                                </span>
                                            @endif
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
                                        
                                        <div class="detail-extralink mb-50">
                                            <div class="detail-qty border radius">
                                                <a href="#" class="qty-down" onclick="decreaseQty(); return false;"><i class="fi-rs-angle-small-down"></i></a>
                                                <input type="text" name="quantity" id="quantity" class="qty-val" value="1" min="1">
                                                <a href="#" class="qty-up" onclick="increaseQty(); return false;"><i class="fi-rs-angle-small-up"></i></a>
                                            </div>
                                            <div class="product-extra-link2">
                                                <button type="submit" class="button button-add-to-cart" id="add-to-cart-btn" {{ !$selectedWarehouseId ? 'disabled' : '' }}>
                                                    <i class="fi-rs-shopping-cart"></i> {{ $selectedWarehouseId ? 'Add to cart' : 'Pilih Hub Terlebih Dahulu' }}
                                                </button>
                                                <a aria-label="Add To Wishlist" class="action-btn hover-up" href="shop-wishlist.html"><i class="fi-rs-heart"></i></a>
                                                <a aria-label="Compare" class="action-btn hover-up" href="shop-compare.html"><i class="fi-rs-shuffle"></i></a>
                                            </div>
                                        </div>
                                        <div>
                                            <small class="text-muted" id="max-stock-hint"></small>
                                        </div>
                                        <div class="form-group mt-3">
                                            <label>Subtotal: </label>
                                            <span id="subtotal" class="text-brand h4">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                        </div>
                                    </form>

                                    <div class="font-xs">
                                        <ul class="mr-50 float-start">
                                            <li class="mb-5">Brand: <span class="text-brand">{{ $product->brand->name ?? 'Unknown' }}</span></li>
                                            <li class="mb-5">Category: <span class="text-brand">{{ $product->category->name ?? 'Unknown' }}</span></li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- Detail Info -->
                            </div>
                        </div>
                        
                        <!-- Tabs -->
                        <div class="product-info">
                            <div class="tab-style3">
                                <ul class="nav nav-tabs text-uppercase">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="Description-tab" data-bs-toggle="tab" href="#Description">Description</a>
                                    </li>
                                </ul>
                                <div class="tab-content shop_info_tab entry-main-content">
                                    <div class="tab-pane fade show active" id="Description">
                                        <div class="">
                                            {!! $product->description !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Related Products (if available) -->
                        <div class="row mt-60">
                            <div class="col-12">
                                <h2 class="section-title style-1 mb-30">Related products</h2>
                            </div>
                            <div class="col-12">
                                <div class="row related-products">
                                     <!-- Ideally this should loop through related products if variable exists -->
                                     @if(isset($relatedProducts) && $relatedProducts->count() > 0)
                                        @foreach($relatedProducts as $related)
                                            <div class="col-lg-3 col-md-4 col-12 col-sm-6">
                                                @include('themes.nest.partials.product-card', ['product' => $related])
                                            </div>
                                        @endforeach
                                     @else
                                        <p>No related products found.</p>
                                     @endif
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
                    <!-- Fillter By Price -->
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
    
    function updateSubtotal() {
        const qty = parseInt(document.getElementById('quantity').value) || 1;
        const subtotal = price * qty;
        document.getElementById('subtotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
    }
    
    function increaseQty() {
        const input = document.getElementById('quantity');
        let newVal = parseInt(input.value) + 1;
        if (selectedStock > 0 && newVal > selectedStock) {
            newVal = selectedStock;
        }
        input.value = newVal;
        updateSubtotal();
    }
    
    function decreaseQty() {
        const input = document.getElementById('quantity');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
            updateSubtotal();
        }
    }
    
    document.getElementById('quantity').addEventListener('change', function() {
        if (this.value < 1) this.value = 1;
        if (selectedStock > 0 && this.value > selectedStock) {
            this.value = selectedStock;
        }
        updateSubtotal();
    });

    function selectHub(warehouseId, warehouseName, stock) {
        selectedWarehouseId = warehouseId;
        selectedStock = stock;
        
        const warehouseInput = document.getElementById('selected-warehouse-id');
        if (warehouseInput) {
            warehouseInput.value = warehouseId;
        }
        
        // Update UI (only if hub-option elements exist)
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
        
        // Enable button
        const btn = document.getElementById('add-to-cart-btn');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fi-rs-shopping-cart me-2"></i> Add to cart';
        }
        
        // Update max stock hint
        const maxStockHint = document.getElementById('max-stock-hint');
        if (maxStockHint) {
            maxStockHint.textContent = `Stock tersedia: ${stock} unit`;
        }
        
        // Validate quantity
        const qtyInput = document.getElementById('quantity');
        if (qtyInput && parseInt(qtyInput.value) > stock) {
            qtyInput.value = stock;
            updateSubtotal();
        }
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
                            stockBadge.textContent = `${targetStock.stock} unit`;
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
                                            <span class="badge bg-success">${stock.stock} unit</span>
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
        if (!selectedWarehouseId) {
            e.preventDefault();
            alert('Silakan pilih hub pengirim terlebih dahulu.');
            return false;
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        loadHubStock();
    });
</script>
@endpush

@push('styles')
<style>
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
