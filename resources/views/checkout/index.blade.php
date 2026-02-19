@extends('layouts.shop')

@section('title', 'Checkout')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('cart.index') }}">Keranjang</a>
            <span></span> Checkout
        </div>
    </div>
</div>

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-8 mb-40">
            <h3 class="heading-2 mb-10">Checkout</h3>
            <div class="d-flex justify-content-between">
                <h6 class="text-body">Ada <span class="text-brand">{{ $carts->count() }}</span> produk di keranjang Anda</h6>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-30" role="alert">
            <i class="fi-rs-cross-circle mr-10"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="row">
            <!-- Left Column - Shipping & Payment -->
            <div class="col-lg-7">
                
                <!-- Address Selection -->
                <div class="mb-25">
                    <div class="d-flex justify-content-between align-items-center mb-15">
                        <h4 class="mb-0"><i class="fi-rs-marker mr-10 text-muted"></i>Alamat Pengiriman</h4>
                        <a href="{{ route('buyer.addresses.create') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fi-rs-plus mr-5"></i>Tambah Alamat
                        </a>
                    </div>
                    
                    @if($addresses->isEmpty())
                        <div class="alert alert-warning mb-0">
                            <i class="fi-rs-info mr-10"></i> 
                            Anda belum memiliki alamat pengiriman. 
                            <a href="{{ route('buyer.addresses.create') }}" class="alert-link">Tambah alamat</a> terlebih dahulu.
                        </div>
                    @else
                        <div class="payment_method">
                            <div class="payment_accordion" id="addressList">
                                @foreach($addresses as $address)
                                    <div class="payment-option mb-15 address-card {{ $address->is_default ? 'active' : '' }}" 
                                         data-address-id="{{ $address->id }}"
                                         onclick="selectAddress('{{ $address->id }}')">
                                        <div class="custom-radio">
                                            <input class="form-check-input" type="radio" name="address_id" 
                                                   id="address{{ $address->id }}" value="{{ $address->id }}" 
                                                   {{ $address->is_default ? 'checked' : '' }}>
                                            <label class="form-check-label" for="address{{ $address->id }}" data-bs-toggle="collapse" data-target="#addressType{{ $address->id }}" aria-controls="addressType{{ $address->id }}">
                                                <span class="font-weight-bold">{{ $address->label }}</span> 
                                                @if($address->is_default)
                                                    <span class="badge bg-success ml-10">Utama</span>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="payment-content pl-20 mt-10" style="display: block;">
                                            <p class="font-weight-bold">{{ $address->recipient_name }} | {{ $address->phone }}</p>
                                            <p class="text-muted">{{ $address->address_detail }}</p>
                                            <p class="text-muted text-small">
                                                {{ $address->village?->name }}, Kec. {{ $address->district?->name }}<br>
                                                {{ $address->regency?->name }}, {{ $address->province?->name }} 
                                                @if($address->postal_code) {{ $address->postal_code }} @endif
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @error('address_id')
                            <div class="text-danger small mt-10">{{ $message }}</div>
                        @enderror
                    @endif
                </div>

                <div class="divider-2 mb-30"></div>

                <!-- Expedition Selection -->
                <div class="mb-25">
                    <h4 class="mb-15"><i class="fi-rs-box-alt mr-10 text-muted"></i>Pilih Ekspedisi</h4>
                    
                    <!-- Expedition Options -->
                    <div class="row mb-20" id="expeditionList">
                        @foreach($expeditions as $expedition)
                            <div class="col-md-4 col-6 mb-10">
                                <div class="card-radio-btn expedition-card {{ $loop->first ? 'active' : '' }}" 
                                     data-expedition-id="{{ $expedition->id }}"
                                     onclick="selectExpedition('{{ $expedition->id }}')">
                                    <input type="radio" name="expedition_id" value="{{ $expedition->id }}" 
                                           id="exp{{ $expedition->id }}" class="d-none"
                                           {{ $loop->first ? 'checked' : '' }}>
                                    <div class="card-body text-center p-2">
                                        @if($expedition->logo)
                                            <img src="{{ asset('storage/' . $expedition->logo) }}" alt="{{ $expedition->name }}" style="height: 30px; object-fit:contain;">
                                        @else
                                            <strong class="text-uppercase">{{ $expedition->code }}</strong>
                                        @endif
                                        <div class="small fw-bold mt-1 text-dark">{{ $expedition->name }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Service Options -->
                    <div class="mt-3">
                        <label class="font-weight-bold mb-10">Layanan Tersedia:</label>
                        <div id="serviceList" class="row">
                            @if(!empty($allShippingServices))
                                @foreach($allShippingServices as $index => $service)
                                    <div class="col-md-6 mb-10">
                                        <div class="card-radio-btn service-card {{ $index === 0 ? 'active' : '' }}"
                                             data-service-code="{{ $service['code'] }}"
                                             onclick="selectService('{{ $service['code'] }}')">
                                            <input type="radio" name="expedition_service" value="{{ $service['code'] }}" 
                                                   id="service{{ $service['code'] }}" class="d-none"
                                                   {{ $index === 0 ? 'checked' : '' }}>
                                            <div class="d-flex justify-content-between align-items-center p-3">
                                                <div>
                                                    <h6 class="mb-0 text-dark">{{ $service['name'] }}</h6>
                                                    <small class="text-muted">{{ $service['estimated_days'] }}</small>
                                                </div>
                                                <div class="fw-bold text-brand">{{ $service['cost_formatted'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                 <div class="col-12">
                                     <div class="alert alert-warning py-2 small">
                                         <i class="fi-rs-info"></i> Tidak ada layanan pengiriman tersedia untuk wilayah ini.
                                     </div>
                                 </div>
                            @endif
                        </div>
                        @error('expedition_service')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="divider-2 mb-30"></div>

                <!-- Payment Method -->
                <div class="mb-25">
                    <h4 class="mb-15"><i class="fi-rs-wallet mr-10 text-muted"></i>Metode Pembayaran</h4>
                    <div class="payment_method">
                        <div class="payment_accordion">
                            <!-- Xendit -->
                            <div class="payment-option mb-10 payment-method-card active" onclick="selectPayment('xendit')" id="card-xendit">
                                <div class="custom-radio">
                                    <input class="form-check-input" type="radio" name="payment_method" value="xendit" id="payXendit" checked>
                                    <label class="form-check-label" for="payXendit">
                                        <strong class="mr-5">Pembayaran Online (Otomatis)</strong>
                                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/payment-method.png') }}" alt="" style="height: 20px;">
                                    </label>
                                </div>
                                <div class="payment-content pl-20 mt-10" style="display: block;">
                                    <p class="font-sm text-muted">QRIS, E-Wallet (Ovo, Dana, ShopeePay), Transfer Bank Virtual Account, Kartu Kredit. Konfirmasi Otomatis.</p>
                                </div>
                            </div>
                            
                            <!-- Manual Transfer -->
                            <div class="payment-option mb-10 payment-method-card" onclick="selectPayment('manual_transfer')" id="card-transfer">
                                <div class="custom-radio">
                                    <input class="form-check-input" type="radio" name="payment_method" value="manual_transfer" id="payTransfer">
                                    <label class="form-check-label" for="payTransfer">
                                        <strong>Transfer Bank Manual</strong>
                                    </label>
                                </div>
                                <div class="payment-content pl-20 mt-10" style="display: block;">
                                    <p class="font-sm text-muted">Transfer manual ke rekening BCA, Mandiri, BNI, atau BRI kami. Memerlukan konfirmasi manual.</p>
                                </div>
                            </div>

                            <!-- COD -->
                            <div class="payment-option mb-10 payment-method-card" onclick="selectPayment('cod')" id="card-cod">
                                <div class="custom-radio">
                                    <input class="form-check-input" type="radio" name="payment_method" value="cod" id="payCOD">
                                    <label class="form-check-label" for="payCOD">
                                        <strong>COD (Bayar di Tempat)</strong>
                                    </label>
                                </div>
                                <div class="payment-content pl-20 mt-10" style="display: block;">
                                    <p class="font-sm text-muted">Bayar tunai kepada kurir saat pesanan sampai di alamat Anda.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="divider-2 mb-30"></div>

                <!-- Notes -->
                <div class="mb-30">
                    <h4 class="mb-15"><i class="fi-rs-comment mr-10 text-muted"></i>Catatan Tambahan</h4>
                    <div class="form-group mb-30">
                        <textarea name="notes" rows="4" placeholder="Catatan untuk penjual atau kurir (Opsional)">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="col-lg-5">
                <div class="border p-40 cart-totals ml-30 mb-50">
                    <div class="d-flex align-items-end justify-content-between mb-30">
                        <h4>Ringkasan Pesanan</h4>
                    </div>
                    <div class="divider-2 mb-30"></div>
                    
                    <div class="order-items-scroll mb-30" style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                        <div class="table-responsive order_table checkout">
                            <table class="table no-border">
                                <tbody>
                                    @foreach($carts as $cart)
                                        <tr>
                                            <td class="image product-thumbnail"><img src="{{ $cart->product->image_url ? $cart->product->image_url : asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="#" style="width: 50px; border-radius: 10px;"></td>
                                            <td>
                                                <h6 class="w-160 mb-5"><a href="{{ route('products.show', $cart->product) }}" class="text-heading">{{ Str::limit($cart->product->name . ($cart->product->commercial_name ? ' - ' . $cart->product->commercial_name : ''), 30) }}</a></h6>
                                                <div class="product-rate-cover">
                                                    <span class="font-small text-muted">{{ $cart->quantity }} x Rp {{ number_format($cart->product->price, 0, ',', '.') }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <h5 class="text-end text-brand">Rp {{ number_format($cart->product->price * $cart->quantity, 0, ',', '.') }}</h5>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="divider-2 mb-30"></div>
                    
                    <div class="table-responsive order_table checkout">
                        <table class="table no-border">
                            <tbody>
                                <tr>
                                    <td class="cart_total_label">
                                        <h6 class="text-muted">Subtotal</h6>
                                    </td>
                                    <td class="cart_total_amount">
                                        <h5 class="text-brand text-end" id="subtotalDisplay">Rp {{ number_format($subtotal, 0, ',', '.') }}</h5>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="cart_total_label">
                                        <h6 class="text-muted">Total Berat</h6>
                                    </td>
                                    <td class="cart_total_amount">
                                        <p class="text-muted text-end" id="totalWeightDisplay">{{ number_format($totalWeight / 1000, 1) }} kg</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="cart_total_label">
                                        <h6 class="text-muted">Ongkos Kirim</h6>
                                        <small class="text-muted d-block" id="expeditionInfo">{{ $defaultExpedition?->name ?? '-' }} - {{ $defaultService['name'] ?? 'Reguler' }}</small>
                                    </td>
                                    <td class="cart_total_amount">
                                        <h5 class="text-brand text-end" id="shippingCostDisplay">
                                            @if($shippingCost > 0)
                                                Rp {{ number_format($shippingCost, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </h5>
                                        <small class="text-muted d-block text-end" id="estimatedDelivery">
                                             @if($defaultService)
                                                Estimasi: {{ $defaultService['estimated_days'] }}
                                            @endif
                                        </small>
                                    </td>
                                </tr>
                                <tr>
                                    <td scope="col" colspan="2">
                                        <div class="divider-2 mt-10 mb-10"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="cart_total_label">
                                        <h4 class="text-brand">Total</h4>
                                    </td>
                                    <td class="cart_total_amount">
                                        <h4 class="text-brand text-end" id="totalDisplay">Rp {{ number_format($total, 0, ',', '.') }}</h4>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="bt-1 border-color-1 mt-30 mb-30"></div>
                    
                    <!-- Source Info -->
                    <div class="payment_method mb-30">
                         <div class="payment_accordion">
                             <div class="payment-content" style="display: block;">
                                 <p class="font-sm mb-0"><i class="fi-rs-building mr-5"></i> Dikirim dari: <strong>{{ $sourceWarehouse->name ?? 'Gudang Pusat' }}</strong></p>
                                 <p class="font-xs text-muted">{{ $sourceWarehouse->full_location ?? '' }}</p>
                             </div>
                         </div>
                    </div>
                    
                    <div class="payment_method mb-30">
                        <div class="payment_accordion">
                            <div class="payment-content" style="display: block;">
                                <p class="font-sm mb-0"><i class="fi-rs-marker mr-5"></i> Dikirim ke:</p>
                                @if($defaultAddress)
                                    <p class="font-sm font-weight-bold mb-0 text-dark">{{ $defaultAddress->recipient_name }}</p>
                                    <p class="font-xs text-muted">{{ Str::limit($defaultAddress->full_address, 60) }}</p>
                                @else
                                    <p class="font-sm text-danger">Pilih alamat pengiriman</p>
                                @endif
                            </div>
                        </div>
                   </div>
                    
                    <button type="submit" class="btn btn-fill-out btn-block mt-30" id="submitBtn" {{ $addresses->isEmpty() ? 'disabled' : '' }}>
                        Proses Pesanan <i class="fi-rs-sign-out ml-15"></i>
                    </button>
                    
                    <p class="text-muted text-center font-xs mt-10">
                        <i class="fi-rs-shield-check mr-5"></i> Data Anda aman dan terenkripsi.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    /* Custom styles for checkout components */
    .card-radio-btn {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fff;
    }
    .card-radio-btn:hover {
        border-color: #3BB77E;
    }
    .card-radio-btn.active {
        border-color: #3BB77E;
        background-color: #f7fbf8;
        position: relative;
    }
    .card-radio-btn.active::after {
        content: '\f26b'; /* fi-rs-check */
        font-family: 'uicons-regular-straight';
        position: absolute;
        top: 5px;
        right: 5px;
        color: #3BB77E;
        font-size: 12px;
    }
    
    .address-card {
        border: 1px solid #e9ecef;
        padding: 15px;
        border-radius: 10px;
        transition: all 0.3s;
        cursor: pointer;
    }
    .address-card:hover {
        border-color: #3BB77E;
    }
    .address-card.active {
        border-color: #3BB77E;
        background-color: #f7fbf8;
    }
    
    .service-card {
        padding: 5px;
    }
    
    .payment-method-card {
        border: 1px solid #e9ecef;
        padding: 15px;
        border-radius: 10px;
        transition: all 0.3s;
        cursor: pointer;
    }
    .payment-method-card:hover {
        border-color: #3BB77E;
    }
    .payment-method-card.active {
        border-color: #3BB77E;
        background-color: #f7fbf8;
    }
    
    /* Scrollbar for order items */
    .order-items-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .order-items-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .order-items-scroll::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 5px;
    }
</style>
@endsection

@push('scripts')
<script>
    var currentAddressId = '{{ $defaultAddress?->id ?? '' }}';
    var currentExpeditionId = '{{ $defaultExpedition?->id ?? '' }}';
    var currentServiceCode = '{{ $defaultService["code"] ?? "" }}';
    
    function selectAddress(addressId) {
        currentAddressId = addressId;
        
        // Update radio button
        $('#address' + addressId).prop('checked', true);
        
        // Update styling
        $('.address-card').removeClass('active');
        $('[data-address-id="' + addressId + '"]').addClass('active');
        
        // Reload services
        if (currentExpeditionId) {
            loadExpeditionServices(currentExpeditionId);
        }
    }
    
    function selectExpedition(expeditionId) {
        currentExpeditionId = expeditionId;
        
        // Update radio button
        $('#exp' + expeditionId).prop('checked', true);
        
        // Update styling
        $('.expedition-card').removeClass('active');
        $('[data-expedition-id="' + expeditionId + '"]').addClass('active');
        
        // Load services
        loadExpeditionServices(expeditionId);
    }
    
    function loadExpeditionServices(expeditionId) {
        if (!currentAddressId) {
            swal("Perhatian", "Silakan pilih alamat pengiriman terlebih dahulu", "warning");
            return;
        }
    
        var serviceList = $('#serviceList');
        serviceList.html('<div class="col-12"><div class="text-center py-3"><div class="spinner-border text-brand" role="status"><span class="visually-hidden">Loading...</span></div> Memuat layanan...</div></div>');
    
        $.ajax({
            url: '{{ route("checkout.expedition-services") }}',
            type: 'GET',
            data: {
                expedition_id: expeditionId,
                address_id: currentAddressId
            },
            success: function(data) {
                if (data.error) {
                    serviceList.html('<div class="col-12"><div class="alert alert-danger">' + data.error + '</div></div>');
                    return;
                }
                
                serviceList.empty();
                
                if (data.services.length === 0) {
                    serviceList.html('<div class="col-12"><div class="alert alert-warning py-2 small"><i class="fi-rs-info"></i> Tidak ada layanan pengiriman tersedia untuk wilayah ini.</div></div>');
                    return;
                }
    
                data.services.forEach(function(service, index) {
                    var isSelected = index === 0;
                    if (isSelected) {
                        currentServiceCode = service.code;
                    }
                    
                    var serviceHtml = `
                        <div class="col-md-6 mb-10">
                            <div class="card-radio-btn service-card ${isSelected ? 'active' : ''}"
                                 data-service-code="${service.code}"
                                 onclick="selectService('${service.code}')">
                                <input type="radio" name="expedition_service" value="${service.code}" 
                                       id="service${service.code}" class="d-none"
                                       ${isSelected ? 'checked' : ''}>
                                <div class="d-flex justify-content-between align-items-center p-3">
                                    <div>
                                        <h6 class="mb-0 text-dark">${service.name}</h6>
                                        <small class="text-muted">${service.estimated_days}</small>
                                    </div>
                                    <div class="fw-bold text-brand">${service.cost_formatted}</div>
                                </div>
                            </div>
                        </div>`;
                    
                    serviceList.append(serviceHtml);
                });
                
                // Update displays if services found
                if (data.services.length > 0) {
                    updateShipping();
                } else {
                     // Reset shipping displays if no services
                    $('#shippingCostDisplay').text('-');
                    $('#totalDisplay').text('Rp {{ number_format($subtotal, 0, ',', '.') }}'); // Reset to subtotal
                    $('#estimatedDelivery').text('-');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                serviceList.html('<div class="col-12"><div class="alert alert-danger">Gagal memuat layanan pengiriman</div></div>');
            }
        });
    }
    
    function selectService(serviceCode) {
        currentServiceCode = serviceCode;
        
        // Update radio button
        $('#service' + serviceCode).prop('checked', true);
        
        // Update styling
        $('.service-card').removeClass('active');
        $('[data-service-code="' + serviceCode + '"]').addClass('active');
        
        // Update shipping cost
        updateShipping();
    }
    
    function updateShipping() {
        if (!currentAddressId || !currentExpeditionId || !currentServiceCode) {
            return;
        }
        
        // Add loading indicator to totals
        $('#shippingCostDisplay').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        
        $.ajax({
            url: '{{ route("checkout.calculate-shipping") }}',
            type: 'GET',
            data: {
                address_id: currentAddressId,
                expedition_id: currentExpeditionId,
                service_code: currentServiceCode
            },
            success: function(data) {
                if (data.error) {
                    swal("Error", data.error, "error");
                    return;
                }
                
                // Update displays
                $('#shippingCostDisplay').text(data.shipping_cost_formatted);
                $('#totalDisplay').text(data.total_formatted);
                if(data.estimated_delivery) {
                    $('#estimatedDelivery').text('Estimasi: ' + data.estimated_delivery);
                } else {
                     $('#estimatedDelivery').text('');
                }
                
                // Update expedition info
                var expCard = $('.expedition-card.active');
                var expName = expCard.length ? expCard.find('.fw-bold').text() : '';
                $('#expeditionInfo').text(expName + ' - ' + data.service_name);
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                swal("Error", "Gagal menghitung biaya pengiriman", "error");
            }
        });
    }
    
    function selectPayment(method) {
        // Update radio button
        $('input[name="payment_method"][value="' + method + '"]').prop('checked', true);
        
        // Update styling
        $('.payment-method-card').removeClass('active');
        
        // Map method to card ID
        var cardId = '';
        if(method === 'xendit') cardId = 'card-xendit';
        else if(method === 'manual_transfer') cardId = 'card-transfer';
        else if(method === 'cod') cardId = 'card-cod';
        
        if(cardId) {
             $('#' + cardId).addClass('active');
        }
    }
</script>
@endpush
