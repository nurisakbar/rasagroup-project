@extends('layouts.shop')

@section('title', $product->name)

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" style="color: var(--primary-color);">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}" style="color: var(--primary-color);">Produk</a></li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-5">
        <!-- Product Image -->
        <div class="col-lg-6">
            <div class="product-image-wrapper position-relative">
                @if($product->image_url)
                    <img src="{{ asset($product->image_url) }}" class="img-fluid rounded-4 shadow" alt="{{ $product->name }}" style="width: 100%; height: 450px; object-fit: cover;">
                @else
                    <img src="https://via.placeholder.com/500x450/e74c3c/fff?text={{ urlencode($product->name) }}" class="img-fluid rounded-4 shadow" alt="{{ $product->name }}" style="width: 100%; height: 450px; object-fit: cover;">
                @endif
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="product-details">
                @if($product->category)
                <span class="badge mb-3" style="background: var(--primary-color); font-size: 0.85rem; padding: 8px 15px; border-radius: 20px;">
                    <i class="bi bi-tag me-1"></i> {{ $product->category->name }}
                </span>
                @endif
                
                <h1 class="product-title mb-3" style="font-weight: 700; color: var(--dark-color);">{{ $product->name }}</h1>
                
                @if($product->brand)
                <p class="mb-2"><i class="bi bi-award me-1"></i> <strong>Brand:</strong> {{ $product->brand->name }}</p>
                @endif
                
                <p class="product-description text-muted mb-4" style="font-size: 1.1rem; line-height: 1.8;">
                    {{ $product->description }}
                </p>

                <div class="price-box p-4 mb-4" style="background: linear-gradient(135deg, #fff5f5 0%, #fef5e7 100%); border-radius: 15px; border-left: 4px solid var(--primary-color);">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <small class="text-muted d-block mb-1">Harga</small>
                            <span class="price" style="font-size: 2.5rem; font-weight: 700; color: var(--primary-color);">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block mb-1">Berat</small>
                            <span style="font-size: 1.2rem; font-weight: 600; color: var(--dark-color);">
                                <i class="bi bi-box-seam me-1"></i>{{ $product->formatted_weight }}
                            </span>
                        </div>
                    </div>
                </div>

                @if(!$selectedWarehouseId)
                <!-- Hub Selection (only show if warehouse_id not in URL) -->
                <div class="hub-selection mb-4 p-4" style="background: #f8f9fa; border-radius: 15px; border: 2px solid #e9ecef;">
                    <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2 text-primary"></i>Pilih Hub Pengirim</h6>
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
                        <i class="bi bi-exclamation-circle text-warning" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">Produk ini belum tersedia di hub manapun.</p>
                    </div>
                </div>
                @else
                <!-- Hub Info (when warehouse_id is preselected) -->
                <div class="hub-selection mb-4 p-4" style="background: #e3f2fd; border-radius: 15px; border: 2px solid #2196f3;">
                    <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2 text-primary"></i>Hub Pengirim</h6>
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
                        <i class="bi bi-exclamation-circle text-danger" style="font-size: 2rem;"></i>
                        <p class="text-danger mt-2 mb-0">Hub tidak ditemukan atau produk tidak tersedia di hub ini.</p>
                    </div>
                </div>
                @endif
                
                <form action="{{ route('cart.store', $product) }}" method="POST" id="add-to-cart-form">
                    @csrf
                    <input type="hidden" name="warehouse_id" id="selected-warehouse-id" value="{{ $selectedWarehouseId ?? '' }}">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-4">
                            <label for="quantity" class="form-label fw-semibold">Jumlah</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="decreaseQty()">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" name="quantity" id="quantity" class="form-control text-center" value="1" min="1" required style="font-weight: 600; font-size: 1.1rem;">
                                <button type="button" class="btn btn-outline-secondary" onclick="increaseQty()">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="max-stock-hint"></small>
                        </div>
                        <div class="col-8">
                            <label class="form-label fw-semibold">Subtotal</label>
                            <div class="form-control-plaintext" style="font-size: 1.3rem; font-weight: 600; color: var(--primary-color);">
                                Rp <span id="subtotal">{{ number_format($product->price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-lg" id="add-to-cart-btn" style="background: var(--gradient-primary); color: white; border-radius: 10px; padding: 15px;" {{ !$selectedWarehouseId ? 'disabled' : '' }}>
                            <i class="bi bi-cart-plus me-2"></i> {{ $selectedWarehouseId ? 'Tambah ke Keranjang' : 'Pilih Hub Terlebih Dahulu' }}
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg" style="border-radius: 10px;">
                            <i class="bi bi-arrow-left me-2"></i> Kembali ke Katalog
                        </a>
                    </div>
                </form>

                <!-- Features -->
                <div class="row g-3 mt-4">
                    <div class="col-6">
                        <div class="d-flex align-items-center p-3" style="background: #f8f9fa; border-radius: 10px;">
                            <i class="bi bi-truck text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <small class="text-muted d-block">Pengiriman</small>
                                <strong>Seluruh Indonesia</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center p-3" style="background: #f8f9fa; border-radius: 10px;">
                            <i class="bi bi-shield-check text-success me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <small class="text-muted d-block">Kualitas</small>
                                <strong>100% Original</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="container py-5 mt-5">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4" style="font-weight: 700; color: var(--dark-color);">
                    <i class="bi bi-star-fill text-warning me-2"></i>Ulasan Pembeli
                </h2>
                
                <!-- Review Summary -->
                <div class="card mb-4" style="border-radius: 15px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <div style="font-size: 3rem; font-weight: 700; color: var(--primary-color);">
                                    4.8
                                </div>
                                <div class="mb-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star-fill text-warning"></i>
                                    @endfor
                                </div>
                                <small class="text-muted">Berdasarkan 24 ulasan</small>
                            </div>
                            <div class="col-md-9">
                                <div class="mb-2">
                                    <div class="d-flex align-items-center mb-1">
                                        <small class="me-2" style="width: 30px;">5</small>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 85%"></div>
                                        </div>
                                        <small class="ms-2 text-muted">20</small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex align-items-center mb-1">
                                        <small class="me-2" style="width: 30px;">4</small>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 15%"></div>
                                        </div>
                                        <small class="ms-2 text-muted">4</small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex align-items-center mb-1">
                                        <small class="me-2" style="width: 30px;">3</small>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="ms-2 text-muted">0</small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex align-items-center mb-1">
                                        <small class="me-2" style="width: 30px;">2</small>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="ms-2 text-muted">0</small>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <small class="me-2" style="width: 30px;">1</small>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="ms-2 text-muted">0</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Review List -->
                <div class="reviews-list">
                    @php
                        $reviews = [
                            [
                                'name' => 'Budi Santoso',
                                'rating' => 5,
                                'date' => '2 hari yang lalu',
                                'comment' => 'Produk sangat bagus! Rasa sirupnya enak dan kemasan rapi. Pengiriman cepat dan aman. Sangat recommended!',
                                'verified' => true
                            ],
                            [
                                'name' => 'Siti Nurhaliza',
                                'rating' => 5,
                                'date' => '5 hari yang lalu',
                                'comment' => 'Kualitas produk sesuai dengan yang diharapkan. Rasa manisnya pas, tidak terlalu manis. Anak-anak suka sekali!',
                                'verified' => true
                            ],
                            [
                                'name' => 'Ahmad Rizki',
                                'rating' => 5,
                                'date' => '1 minggu yang lalu',
                                'comment' => 'Barang sampai dengan cepat dan dalam kondisi baik. Rasa sirupnya original dan segar. Akan order lagi nanti.',
                                'verified' => false
                            ],
                            [
                                'name' => 'Dewi Lestari',
                                'rating' => 4,
                                'date' => '2 minggu yang lalu',
                                'comment' => 'Produk bagus, harga terjangkau. Hanya saja pengiriman agak lama karena lokasi saya jauh. Overall puas!',
                                'verified' => true
                            ],
                            [
                                'name' => 'Rudi Hartono',
                                'rating' => 5,
                                'date' => '3 minggu yang lalu',
                                'comment' => 'Sangat puas dengan produk ini. Rasa sirupnya enak dan kemasan sangat aman. Seller ramah dan responsif.',
                                'verified' => true
                            ],
                            [
                                'name' => 'Maya Sari',
                                'rating' => 5,
                                'date' => '1 bulan yang lalu',
                                'comment' => 'Produk original dan berkualitas tinggi. Sudah beberapa kali order dan selalu puas. Terima kasih!',
                                'verified' => true
                            ]
                        ];
                    @endphp

                    @foreach($reviews as $review)
                    <div class="card mb-3" style="border-radius: 15px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-3" style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1.2rem;">
                                        {{ strtoupper(substr($review['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0" style="font-weight: 600;">{{ $review['name'] }}</h6>
                                        @if($review['verified'])
                                        <small class="text-success">
                                            <i class="bi bi-patch-check-fill"></i> Pembeli Terverifikasi
                                        </small>
                                        @endif
                                    </div>
                                </div>
                                <small class="text-muted">{{ $review['date'] }}</small>
                            </div>
                            
                            <div class="mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review['rating'])
                                        <i class="bi bi-star-fill text-warning"></i>
                                    @else
                                        <i class="bi bi-star text-muted"></i>
                                    @endif
                                @endfor
                            </div>
                            
                            <p class="mb-0" style="color: #555; line-height: 1.6;">{{ $review['comment'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Load More Reviews Button -->
                <div class="text-center mt-4">
                    <button class="btn btn-outline-primary" style="border-radius: 25px; padding: 10px 30px;">
                        <i class="bi bi-arrow-down-circle me-2"></i>Muat Lebih Banyak Ulasan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Products Section (Optional) -->
@if(isset($relatedProducts) && $relatedProducts->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2>Produk Lainnya</h2>
            <div class="divider mx-auto"></div>
        </div>
        <div class="row g-4">
            @foreach($relatedProducts as $related)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card product-card h-100">
                        <div class="card-img-wrapper">
                            @if($related->image_url)
                                <img src="{{ asset($related->image_url) }}" class="card-img-top" alt="{{ $related->name }}">
                            @else
                                <img src="https://via.placeholder.com/300x200/e74c3c/fff?text={{ urlencode($related->name) }}" class="card-img-top" alt="{{ $related->name }}">
                            @endif
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $related->name }}</h5>
                            <span class="price mb-3">Rp {{ number_format($related->price, 0, ',', '.') }}</span>
                            <a href="{{ route('products.show', $related) }}" class="btn btn-add-cart mt-auto">
                                <i class="bi bi-eye me-1"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

@push('scripts')
<script>
    const price = {{ $product->price }};
    let selectedStock = 0;
    let selectedWarehouseId = null;
    
    function updateSubtotal() {
        const qty = parseInt(document.getElementById('quantity').value) || 1;
        const subtotal = price * qty;
        document.getElementById('subtotal').textContent = new Intl.NumberFormat('id-ID').format(subtotal);
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
            btn.innerHTML = '<i class="bi bi-cart-plus me-2"></i> Tambah ke Keranjang';
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
                    // If HTTP error, try to get error message
                    return response.json().then(data => {
                        throw new Error(data.error || 'Failed to fetch stock');
                    }).catch(() => {
                        throw new Error('Failed to fetch stock');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Stock data received:', data); // Debug log
                
                if (data.error) {
                    console.error('Error in response:', data.error);
                    loadingEl.classList.add('d-none');
                    errorEl.classList.remove('d-none');
                    return;
                }
                
                const targetStock = data.stocks?.[0]; // Should only have one result
                console.log('Target stock:', targetStock); // Debug log
                console.log('Target stock exists?', !!targetStock); // Debug log
                
                if (!targetStock) {
                    console.error('No target stock found in data.stocks');
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
                    
                    if (!hubInfoName || !hubInfoLocation || !stockBadge) {
                        console.error('DOM elements not found!', { hubInfoName, hubInfoLocation, stockBadge });
                        errorEl.classList.remove('d-none');
                        return;
                    }
                    
                    hubInfoName.textContent = targetStock.warehouse_name;
                    hubInfoLocation.innerHTML = `<i class="bi bi-geo-alt me-1"></i>${targetStock.warehouse_location}`;
                    
                    if (targetStock.stock > 0) {
                        stockBadge.className = 'badge bg-success';
                        stockBadge.textContent = `${targetStock.stock} unit`;
                        infoEl.classList.remove('d-none');
                        
                        // Set warehouse and enable button
                        selectHub(targetStock.warehouse_id, targetStock.warehouse_name, targetStock.stock);
                    } else {
                        stockBadge.className = 'badge bg-danger';
                        stockBadge.textContent = 'Stock Habis';
                        infoEl.classList.remove('d-none');
                        
                        // Disable button and show message
                        const btn = document.getElementById('add-to-cart-btn');
                        if (btn) {
                            btn.disabled = true;
                            btn.innerHTML = '<i class="bi bi-x-circle me-2"></i> Stock Tidak Tersedia';
                        }
                        const maxStockHint = document.getElementById('max-stock-hint');
                        if (maxStockHint) {
                            maxStockHint.textContent = 'Stock habis di hub ini';
                        }
                    }
                } catch (error) {
                    console.error('Error processing stock data:', error);
                    loadingEl.classList.add('d-none');
                    errorEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error:', error);
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
                                            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>${stock.warehouse_location}</small>
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
                    
                    // Auto-select hub if preselectedWarehouseId is provided
                    if (preselectedWarehouseId) {
                        const targetStock = data.stocks.find(s => s.warehouse_id === preselectedWarehouseId);
                        if (targetStock) {
                            // Small delay to ensure DOM is ready
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
                console.error('Error:', error);
                loadingEl.classList.add('d-none');
                emptyEl.innerHTML = '<i class="bi bi-exclamation-circle text-danger" style="font-size: 2rem;"></i><p class="text-danger mt-2 mb-0">Gagal memuat data. Silakan refresh halaman.</p>';
                emptyEl.classList.remove('d-none');
            });
    }

    // Form validation
    document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
        if (!selectedWarehouseId) {
            e.preventDefault();
            alert('Silakan pilih hub pengirim terlebih dahulu.');
            return false;
        }
    });

    // Load hub stock on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadHubStock();
    });
</script>
@endpush

@push('styles')
<style>
    .divider {
        width: 80px;
        height: 4px;
        background: var(--gradient-primary);
        border-radius: 2px;
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
