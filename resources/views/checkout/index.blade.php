@extends('layouts.shop')

@section('title', 'Checkout')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Keranjang</a></li>
            <li class="breadcrumb-item active">Checkout</li>
        </ol>
    </nav>

    <h2 class="mb-4"><i class="bi bi-credit-card"></i> Checkout</h2>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="row">
            <!-- Left Column - Shipping & Payment -->
            <div class="col-lg-8">
                <!-- Address Selection -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Alamat Pengiriman</h5>
                        <a href="{{ route('buyer.addresses.create') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-plus"></i> Tambah Alamat
                        </a>
                    </div>
                    <div class="card-body">
                        @if($addresses->isEmpty())
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Anda belum memiliki alamat pengiriman. 
                                <a href="{{ route('buyer.addresses.create') }}" class="alert-link">Tambah alamat</a> terlebih dahulu.
                            </div>
                        @else
                            <div class="row" id="addressList">
                                @foreach($addresses as $address)
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100 address-card {{ $address->is_default ? 'border-primary selected' : 'border' }}" 
                                             data-address-id="{{ $address->id }}"
                                             onclick="selectAddress('{{ $address->id }}')">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <span class="badge {{ $address->is_default ? 'bg-primary' : 'bg-secondary' }}">{{ $address->label }}</span>
                                                        @if($address->is_default)
                                                            <span class="badge bg-success">Utama</span>
                                                        @endif
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input address-radio" type="radio" 
                                                               name="address_id" value="{{ $address->id }}" 
                                                               id="address{{ $address->id }}"
                                                               {{ $address->is_default ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                                <h6 class="mb-1">{{ $address->recipient_name }}</h6>
                                                <p class="text-muted mb-1 small">
                                                    <i class="bi bi-telephone"></i> {{ $address->phone }}
                                                </p>
                                                <p class="mb-1 small">{{ $address->address_detail }}</p>
                                                <p class="text-muted mb-0 small">
                                                    {{ $address->village?->name }}, Kec. {{ $address->district?->name }}<br>
                                                    {{ $address->regency?->name }}, {{ $address->province?->name }}
                                                    @if($address->postal_code) {{ $address->postal_code }} @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('address_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

                <!-- Expedition Selection -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-truck"></i> Pilih Ekspedisi</h5>
                    </div>
                    <div class="card-body">
                        <!-- Expedition Options -->
                        <div class="row mb-3" id="expeditionList">
                            @foreach($expeditions as $expedition)
                                <div class="col-md-4 col-6 mb-2">
                                    <div class="expedition-card p-3 border rounded text-center {{ $loop->first ? 'selected border-primary' : '' }}" 
                                         data-expedition-id="{{ $expedition->id }}"
                                         onclick="selectExpedition('{{ $expedition->id }}')">
                                        <input type="radio" name="expedition_id" value="{{ $expedition->id }}" 
                                               id="exp{{ $expedition->id }}" class="d-none"
                                               {{ $loop->first ? 'checked' : '' }}>
                                        @if($expedition->logo)
                                            <img src="{{ asset('storage/' . $expedition->logo) }}" alt="{{ $expedition->name }}" class="mb-2" style="height: 30px;">
                                        @else
                                            <div class="expedition-logo mb-2">
                                                <strong class="text-uppercase" style="font-size: 0.9rem;">{{ $expedition->code }}</strong>
                                            </div>
                                        @endif
                                        <div class="small fw-semibold">{{ $expedition->name }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $expedition->estimated_delivery }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Service Options -->
                        <div class="mt-3">
                            <label class="form-label fw-semibold">Pilih Layanan:</label>
                            <div id="serviceList" class="row">
                                @if($defaultExpedition)
                                    @foreach($defaultExpedition->services as $index => $service)
                                        @php
                                            $serviceCost = round(($shippingRates[$defaultAddress?->province_id ?? '31'] ?? 35000) * $defaultExpedition->base_cost * $service['multiplier']);
                                            $estMin = max(1, $defaultExpedition->est_days_min + $service['days_add']);
                                            $estMax = max(1, $defaultExpedition->est_days_max + $service['days_add']);
                                        @endphp
                                        <div class="col-md-4 mb-2">
                                            <div class="service-card p-3 border rounded {{ $index === 0 ? 'selected border-primary' : '' }}"
                                                 data-service-code="{{ $service['code'] }}"
                                                 data-cost="{{ $serviceCost }}"
                                                 onclick="selectService('{{ $service['code'] }}')">
                                                <input type="radio" name="expedition_service" value="{{ $service['code'] }}" 
                                                       id="service{{ $service['code'] }}" class="d-none"
                                                       {{ $index === 0 ? 'checked' : '' }}>
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <div class="fw-semibold">{{ $service['name'] }}</div>
                                                        <small class="text-muted">{{ $estMin === $estMax ? $estMin : $estMin.'-'.$estMax }} hari</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-primary service-cost">Rp {{ number_format($serviceCost, 0, ',', '.') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            @error('expedition_service')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-wallet2"></i> Metode Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check payment-option p-3 border rounded selected" onclick="selectPayment('xendit')">
                                    <input class="form-check-input" type="radio" name="payment_method" value="xendit" id="payXendit" checked>
                                    <label class="form-check-label w-100" for="payXendit">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-credit-card text-primary me-3" style="font-size: 1.5rem;"></i>
                                            <div>
                                                <strong>Pembayaran Online</strong>
                                                <small class="d-block text-muted">Kartu Kredit, Debit, E-Wallet, QRIS</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check payment-option p-3 border rounded" onclick="selectPayment('manual_transfer')">
                                    <input class="form-check-input" type="radio" name="payment_method" value="manual_transfer" id="payTransfer">
                                    <label class="form-check-label w-100" for="payTransfer">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-bank text-primary me-3" style="font-size: 1.5rem;"></i>
                                            <div>
                                                <strong>Transfer Bank Manual</strong>
                                                <small class="d-block text-muted">BCA, Mandiri, BNI, BRI</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check payment-option p-3 border rounded" onclick="selectPayment('cod')">
                                    <input class="form-check-input" type="radio" name="payment_method" value="cod" id="payCOD">
                                    <label class="form-check-label w-100" for="payCOD">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cash-coin text-success me-3" style="font-size: 1.5rem;"></i>
                                            <div>
                                                <strong>COD (Bayar di Tempat)</strong>
                                                <small class="d-block text-muted">Bayar saat barang sampai</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Pembayaran Online (Xendit):</strong> Mendukung Kartu Kredit/Debit, E-Wallet (OVO, DANA, LinkAja, ShopeePay), QRIS, dan Virtual Account. Pembayaran diproses secara otomatis.
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-chat-text"></i> Catatan (Opsional)</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan untuk pesanan Anda...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-receipt"></i> Ringkasan Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cart Items -->
                        <div class="order-items mb-3" style="max-height: 200px; overflow-y: auto;">
                            @foreach($carts as $cart)
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="position-relative me-3">
                                        @if($cart->product->image)
                                            <img src="{{ asset('storage/' . $cart->product->image) }}" alt="{{ $cart->product->name }}" 
                                                 class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size: 0.65rem;">
                                            {{ $cart->quantity }}
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 small">{{ Str::limit($cart->product->name, 20) }}</h6>
                                        <small class="text-muted">Rp {{ number_format($cart->product->price, 0, ',', '.') }}</small>
                                    </div>
                                    <div class="text-end">
                                        <strong class="small">Rp {{ number_format($cart->product->price * $cart->quantity, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pricing Summary -->
                        <div class="pricing-summary">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal ({{ $carts->sum('quantity') }} item)</span>
                                <span id="subtotalDisplay">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-muted small">
                                <span><i class="bi bi-box-seam"></i> Total Berat</span>
                                <span id="totalWeightDisplay">{{ number_format($totalWeight / 1000, 1) }} kg</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>
                                    Ongkos Kirim
                                    <small class="text-muted d-block" id="expeditionInfo">
                                        {{ $defaultExpedition?->name ?? '-' }} - {{ $defaultService['name'] ?? 'Reguler' }}
                                    </small>
                                </span>
                                <span id="shippingCostDisplay">
                                    @if($shippingCost > 0)
                                        Rp {{ number_format($shippingCost, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-muted small">
                                <span><i class="bi bi-clock"></i> Estimasi</span>
                                <span id="estimatedDelivery">{{ $defaultExpedition?->estimated_delivery ?? '-' }}</span>
                            </div>
                            <hr>
                            
                            <!-- Source Warehouse Info -->
                            <div class="mb-3 bg-light p-2 rounded">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-building"></i> Dikirim dari:
                                </small>
                                <span id="sourceWarehouseDisplay">
                                    @if($sourceWarehouse)
                                        <strong>{{ $sourceWarehouse->name }}</strong>
                                        <small class="d-block text-muted">{{ $sourceWarehouse->full_location }}</small>
                                    @else
                                        <span class="text-muted">Hub otomatis dipilih</span>
                                    @endif
                                </span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <strong class="fs-5">Total</strong>
                                <strong class="fs-5 text-primary" id="totalDisplay">Rp {{ number_format($total, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <!-- Selected Address Preview -->
                        <div class="selected-address-preview bg-light rounded p-3 mb-3" id="selectedAddressPreview">
                            @if($defaultAddress)
                                <small class="text-muted d-block mb-1"><i class="bi bi-geo-alt"></i> Dikirim ke:</small>
                                <strong>{{ $defaultAddress->recipient_name }}</strong>
                                <p class="mb-0 small text-muted">{{ $defaultAddress->full_address }}</p>
                            @else
                                <small class="text-muted">Pilih alamat pengiriman di atas</small>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" 
                                    {{ $addresses->isEmpty() ? 'disabled' : '' }}>
                                <i class="bi bi-check-circle"></i> Konfirmasi Pesanan
                            </button>
                        </div>

                        <p class="text-center text-muted small mt-3 mb-0">
                            <i class="bi bi-shield-check"></i> Pesanan Anda aman & terlindungi
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .address-card {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .address-card:hover {
        border-color: var(--bs-primary) !important;
        box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.1);
    }
    .address-card.selected {
        border-color: var(--bs-primary) !important;
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }
    .expedition-card {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .expedition-card:hover {
        border-color: var(--bs-primary) !important;
    }
    .expedition-card.selected {
        border-color: var(--bs-primary) !important;
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }
    .expedition-logo {
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        border-radius: 5px;
        padding: 0 10px;
    }
    .service-card {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .service-card:hover {
        border-color: var(--bs-primary) !important;
    }
    .service-card.selected {
        border-color: var(--bs-primary) !important;
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }
    .payment-option {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .payment-option:hover {
        border-color: var(--bs-primary) !important;
    }
    .payment-option.selected {
        border-color: var(--bs-primary) !important;
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }
    .order-items::-webkit-scrollbar {
        width: 5px;
    }
    .order-items::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .order-items::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }
</style>

<script>
var currentAddressId = '{{ $defaultAddress?->id ?? '' }}';
var currentExpeditionId = '{{ $defaultExpedition?->id ?? '' }}';
var currentServiceCode = '{{ $defaultService["code"] ?? "REG" }}';

function selectAddress(addressId) {
    currentAddressId = addressId;
    
    // Update radio button
    document.getElementById('address' + addressId).checked = true;
    
    // Update card styling
    document.querySelectorAll('.address-card').forEach(function(card) {
        card.classList.remove('selected', 'border-primary');
        card.classList.add('border');
    });
    var selectedCard = document.querySelector('[data-address-id="' + addressId + '"]');
    if (selectedCard) {
        selectedCard.classList.add('selected', 'border-primary');
        selectedCard.classList.remove('border');
    }
    
    // Recalculate shipping (this will also update warehouse)
    updateShipping();
}

function selectExpedition(expeditionId) {
    currentExpeditionId = expeditionId;
    
    // Update radio button
    document.getElementById('exp' + expeditionId).checked = true;
    
    // Update card styling
    document.querySelectorAll('.expedition-card').forEach(function(card) {
        card.classList.remove('selected', 'border-primary');
    });
    document.querySelector('[data-expedition-id="' + expeditionId + '"]').classList.add('selected', 'border-primary');
    
    // Load services for this expedition
    loadExpeditionServices(expeditionId);
}

function loadExpeditionServices(expeditionId) {
    fetch('{{ route("checkout.expedition-services") }}?expedition_id=' + expeditionId + '&address_id=' + currentAddressId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.error) {
                alert(data.error);
                return;
            }
            
            var serviceList = document.getElementById('serviceList');
            serviceList.innerHTML = '';
            
            data.services.forEach(function(service, index) {
                var isSelected = index === 0;
                if (isSelected) {
                    currentServiceCode = service.code;
                }
                
                serviceList.innerHTML += 
                    '<div class="col-md-4 mb-2">' +
                        '<div class="service-card p-3 border rounded ' + (isSelected ? 'selected border-primary' : '') + '"' +
                             ' data-service-code="' + service.code + '"' +
                             ' data-cost="' + service.cost + '"' +
                             ' onclick="selectService(\'' + service.code + '\')">' +
                            '<input type="radio" name="expedition_service" value="' + service.code + '"' +
                                   ' id="service' + service.code + '" class="d-none"' +
                                   (isSelected ? ' checked' : '') + '>' +
                            '<div class="d-flex justify-content-between align-items-start">' +
                                '<div>' +
                                    '<div class="fw-semibold">' + service.name + '</div>' +
                                    '<small class="text-muted">' + service.estimated_days + '</small>' +
                                '</div>' +
                                '<div class="text-end">' +
                                    '<div class="fw-bold text-primary service-cost">' + service.cost_formatted + '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
            });
            
            // Update displays with first service
            if (data.services.length > 0) {
                updateShipping();
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
        });
}

function selectService(serviceCode) {
    currentServiceCode = serviceCode;
    
    // Update radio button
    document.getElementById('service' + serviceCode).checked = true;
    
    // Update card styling
    document.querySelectorAll('.service-card').forEach(function(card) {
        card.classList.remove('selected', 'border-primary');
    });
    document.querySelector('[data-service-code="' + serviceCode + '"]').classList.add('selected', 'border-primary');
    
    // Update shipping cost
    updateShipping();
}

function updateShipping() {
    if (!currentAddressId || !currentExpeditionId || !currentServiceCode) return;
    
    fetch('{{ route("checkout.calculate-shipping") }}?address_id=' + currentAddressId + 
          '&expedition_id=' + currentExpeditionId + 
          '&service_code=' + currentServiceCode)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.error) {
                console.error(data.error);
                return;
            }
            
            // Update displays
            document.getElementById('shippingCostDisplay').textContent = data.shipping_cost_formatted;
            document.getElementById('totalDisplay').textContent = data.total_formatted;
            document.getElementById('estimatedDelivery').textContent = data.estimated_delivery;
            
            // Update expedition info
            var expCard = document.querySelector('.expedition-card.selected');
            var expName = expCard ? expCard.querySelector('.fw-semibold').textContent : '';
            document.getElementById('expeditionInfo').textContent = expName + ' - ' + data.service_name;
            
            // Update selected address preview
            document.getElementById('selectedAddressPreview').innerHTML = 
                '<small class="text-muted d-block mb-1"><i class="bi bi-geo-alt"></i> Dikirim ke:</small>' +
                '<strong>' + data.address.recipient_name + '</strong>' +
                '<p class="mb-0 small text-muted">' + data.address.full_address + '</p>';
            
            // Update source warehouse display
            if (data.warehouse) {
                document.getElementById('sourceWarehouseDisplay').innerHTML = 
                    '<strong>' + data.warehouse.name + '</strong>' +
                    '<small class="d-block text-muted">' + data.warehouse.location + '</small>';
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
        });
}

function selectPayment(method) {
    var paymentIds = {
        'xendit': 'payXendit',
        'manual_transfer': 'payTransfer',
        'cod': 'payCOD'
    };
    
    if (paymentIds[method]) {
        document.getElementById(paymentIds[method]).checked = true;
    }
    
    document.querySelectorAll('.payment-option').forEach(function(opt) {
        opt.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
}
</script>

@php
    // For service cost calculation in blade
    $shippingRates = [
        '11' => 15000, '12' => 15000, '13' => 18000, '14' => 20000, '15' => 22000,
        '16' => 25000, '17' => 25000, '18' => 28000, '19' => 30000, '21' => 32000,
        '31' => 10000, '32' => 12000, '33' => 12000, '34' => 10000, '35' => 12000,
        '36' => 15000, '51' => 20000, '52' => 25000, '53' => 30000, '61' => 35000,
        '62' => 35000, '63' => 35000, '64' => 35000, '65' => 35000, '71' => 40000,
        '72' => 40000, '73' => 40000, '74' => 40000, '75' => 40000, '76' => 40000,
        '81' => 50000, '82' => 50000, '91' => 55000, '94' => 55000,
    ];
@endphp
@endsection
