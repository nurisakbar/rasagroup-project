@extends('layouts.shop')
@section('hide_layout_alerts', true)

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

<div class="container mb-80 mt-50 checkout-page">
    <div class="row">
        <div class="col-lg-8 mb-40">
            <h3 class="heading-2 mb-10">Checkout</h3>
        </div>
    </div>

    <div id="sessionAlerts">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-30" role="alert">
                <i class="fi-rs-cross-circle mr-10"></i>{!! nl2br(e(session('error'))) !!}
                @if(str_contains(session('error'), 'Stock tidak mencukupi') || str_contains(session('error'), 'stok'))
                    <div class="mt-15">
                        <form action="{{ route('cart.remove-out-of-stock') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-outline-danger">Hapus Item Habis Stok</button>
                        </form>
                    </div>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show mb-30" role="alert">
                <i class="fi-rs-exclamation mr-10"></i>{!! nl2br(e(session('warning'))) !!}
                @if(str_contains(session('warning'), 'stok') || str_contains(session('warning'), 'Stok'))
                    <div class="mt-15">
                        <form action="{{ route('cart.remove-out-of-stock') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-outline-danger">Hapus Item Habis Stok</button>
                        </form>
                    </div>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <div id="checkoutAlertContainer"></div>

    <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm">
        @csrf
        @foreach(request('cart_ids', []) as $cartId)
            <input type="hidden" name="cart_ids[]" value="{{ $cartId }}">
        @endforeach
        <div class="row">
            <!-- Left Column - Shipping & Payment -->
            <div class="col-lg-7">
                
                <!-- Address Selection -->
                <div class="mb-25">
                    <div class="d-flex justify-content-between align-items-center mb-15">
                        <h4 class="mb-0"><i class="fi-rs-marker mr-10 text-muted"></i>Alamat Pengiriman</h4>
                        <a href="{{ route('buyer.addresses.create', ['origin' => 'checkout']) }}" class="btn btn-sm btn-standar-outline">
                            <i class="fi-rs-plus mr-5"></i>Tambah Alamat
                        </a>
                    </div>
                    
                    @if($addresses->isEmpty())
                        <div class="alert alert-warning mb-0">
                            <i class="fi-rs-info mr-10"></i> 
                            Anda belum memiliki alamat pengiriman. 
                            <a href="{{ route('buyer.addresses.create', ['origin' => 'checkout']) }}" class="alert-link">Tambah alamat</a> terlebih dahulu.
                        </div>
                    @else
                        <div class="payment_method">
                            <div class="payment_accordion" id="addressList">
                                @foreach($addresses as $address)
                                    <div class="payment-option mb-15 address-card {{ $address->id === ($defaultAddress->id ?? '') ? 'active' : '' }}" 
                                         data-address-id="{{ $address->id }}"
                                         data-recipient="{{ $address->recipient_name }}"
                                         data-full-address="{{ $address->full_address }}"
                                         onclick="selectAddress('{{ $address->id }}')">
                                        <div class="custom-radio">
                                            <input class="form-check-input" type="radio" name="address_id" 
                                                   id="address{{ $address->id }}" value="{{ $address->id }}" 
                                                   {{ $address->id === ($defaultAddress->id ?? '') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="address{{ $address->id }}">
                                                <span class="font-weight-bold">{{ $address->label }}</span> 
                                                @if($address->is_default)
                                                    <span class="badge bg-success ml-10">Utama</span>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="payment-content pl-20 mt-10" style="display: block;">
                                            <p class="font-weight-bold">{{ $address->recipient_name }} | {{ $address->phone }}</p>
                                            <p class="checkout-address-detail">{{ $address->address_detail }}</p>
                                            <p class="checkout-address-location text-small">
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
                            @php
                                $isDefaultExpedition = $defaultExpedition && $expedition->id === $defaultExpedition->id;
                            @endphp
                            <div class="col-md-4 col-6 mb-10">
                                <div class="card-radio-btn expedition-card {{ $isDefaultExpedition ? 'active' : '' }}" 
                                     data-expedition-id="{{ $expedition->id }}"
                                     onclick="selectExpedition('{{ $expedition->id }}')">
                                    <input type="radio" name="expedition_id" value="{{ $expedition->id }}" 
                                           id="exp{{ $expedition->id }}" class="d-none"
                                           {{ $isDefaultExpedition ? 'checked' : '' }}>
                                    <div class="card-body text-center p-2">
                                        @if($expedition->logo)
                                            <img src="{{ str_starts_with($expedition->logo, 'http') ? $expedition->logo : asset('storage/' . $expedition->logo) }}" alt="{{ $expedition->name }}" style="height: 30px; object-fit:contain;">
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
                                    @php
                                        $isDefaultService = ($defaultService['code'] ?? null) === $service['code'];
                                    @endphp
                                    <div class="col-md-6 mb-10">
                                        <div class="card-radio-btn service-card {{ $isDefaultService ? 'active' : '' }}"
                                             data-service-code="{{ $service['code'] }}"
                                             onclick="selectService('{{ $service['code'] }}')">
                                            <input type="radio" name="expedition_service" value="{{ $service['code'] }}" 
                                                   id="service-{{ $loop->index }}" class="d-none"
                                                   {{ $isDefaultService ? 'checked' : '' }}>
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
                            <!-- Online Payment (Xendit/Faspay) -->
                            <div class="payment-option mb-10 payment-method-card active" onclick="selectPayment('{{ config('services.active_payment_gateway') }}')" id="card-xendit">
                                <div class="custom-radio">
                                    <input class="form-check-input" type="radio" name="payment_method" value="{{ config('services.active_payment_gateway') }}" id="payXendit" checked>
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

                            @if(Auth::user()->isDistributor() && (int) (Auth::user()->term_of_payment ?? 0) > 0)
                            <div class="payment-option mb-10 payment-method-card" onclick="selectPayment('term_of_payment')" id="card-tot">
                                <div class="custom-radio">
                                    <input class="form-check-input" type="radio" name="payment_method" value="term_of_payment" id="payTot">
                                    <label class="form-check-label" for="payTot">
                                        <strong>TOT (Term of Payment)</strong>
                                    </label>
                                </div>
                                <div class="payment-content pl-20 mt-10" style="display: block;">
                                    <p class="font-sm text-muted mb-0">Pembayaran dengan tempo <strong>{{ (int) Auth::user()->term_of_payment }}</strong> hari sesuai kesepakatan distributor. Tidak melalui pembayaran online otomatis.</p>
                                </div>
                            </div>
                            @endif


                        </div>
                    </div>
                </div>
                
                <div class="divider-2 mb-30"></div>

                <!-- Notes -->
                <div class="mb-30">
                    <h4 class="mb-15"><i class="fi-rs-comment mr-10 text-muted"></i>Catatan Tambahan</h4>
                    <div class="form-group mb-30">
                        <textarea name="notes" rows="2" placeholder="Catatan untuk penjual atau kurir (Opsional)">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="col-lg-5">
                <div class="cart-totals checkout-order-summary rg-checkout-summary mb-50">
                    <div class="d-flex align-items-end justify-content-between mb-30 rg-checkout-summary-header">
                        <h4 class="mb-0">Ringkasan Pesanan</h4>
                    </div>

                    @if($affiliate)
                        <div class="alert alert-info py-3 px-3 mb-30 d-flex align-items-center" style="border-radius: 10px; background-color: #f4f9ff; border: 1px solid #d1e7ff;">
                            <i class="fi-rs-info mr-10 text-primary"></i>
                            <div class="small text-dark">
                                Pesanan ini menggunakan kode referal: <strong class="text-brand">{{ $affiliate->referral_code }}</strong><br>
                                <span class="text-muted">Direkomendasikan oleh: {{ $affiliate->name }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="divider-2 mb-30"></div>
                    
                    <div class="order-items-scroll mb-30 rg-checkout-items-scroll" style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                        <div class="table-responsive order_table checkout rg-checkout-items-wrap">
                            <table class="table no-border rg-checkout-items-table">
                                <tbody>
                                    @foreach($carts as $cart)
                                        @php
                                            $checkoutUser = Auth::user();
                                            $unitPrice = $checkoutUser->getProductPrice($cart->product);
                                            $retailUnit = (float) $cart->product->price;
                                            $showRetailStrike = $checkoutUser->isDistributor() && $checkoutUser->priceLevel && $unitPrice < $retailUnit;
                                        @endphp
                                        <tr class="rg-checkout-item">
                                            <td class="image product-thumbnail rg-checkout-item-thumb">
                                                <img src="{{ $cart->product->image_url ? $cart->product->image_url : asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" alt="{{ $cart->product->name }}">
                                            </td>
                                            <td class="rg-checkout-item-info">
                                                <a href="{{ route('products.show', $cart->product) }}" class="rg-checkout-item-name text-heading">{{ $cart->product->name }}</a>
                                                @if($cart->product->commercial_name)
                                                    <p class="rg-checkout-item-variant">{{ $cart->product->commercial_name }}</p>
                                                @endif
                                                <div class="rg-checkout-item-meta">
                                                    <span class="rg-checkout-item-qty">{{ $cart->quantity }} ×</span>
                                                    <span class="rg-checkout-item-unit">
                                                        @if($showRetailStrike)
                                                            <span class="rg-checkout-item-unit-retail">Rp {{ number_format($retailUnit, 0, ',', '.') }}</span>
                                                        @endif
                                                        <span class="rg-checkout-item-unit-price">Rp {{ number_format($unitPrice, 0, ',', '.') }}</span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="rg-checkout-item-price">
                                                <span class="rg-checkout-item-price-label">Subtotal</span>
                                                <strong class="rg-checkout-item-price-value text-brand">Rp {{ number_format($unitPrice * $cart->quantity, 0, ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="divider-2 mb-30"></div>
                    
                    <div class="table-responsive order_table checkout rg-checkout-totals-wrap">
                        <table class="table table-borderless no-border rg-checkout-totals-table" style="border: none !important;">
                            <style>
                                .rg-checkout-totals-table, .rg-checkout-totals-table th, .rg-checkout-totals-table td, .rg-checkout-totals-table tr {
                                    border: none !important;
                                    border-color: transparent !important;
                                }
                            </style>
                            <tbody>
                                <tr id="distributorRetailRow" class="rg-checkout-total-row" style="{{ !empty($showDistributorPricing) ? '' : 'display: none;' }}">
                                    <th class="cart_total_label align-middle pb-3">
                                        <h6 class="text-muted mb-0">Subtotal katalog (referensi)</h6>
                                    </th>
                                    <td class="cart_total_amount text-end align-middle pb-3">
                                        <h5 class="text-muted mb-0" id="retailSubtotalDisplay">Rp {{ number_format($retailSubtotal ?? $subtotal, 0, ',', '.') }}</h5>
                                    </td>
                                </tr>
                                <tr id="distributorDiscountRow" class="rg-checkout-total-row" style="{{ !empty($showDistributorPricing) ? '' : 'display: none;' }}">
                                    <th class="cart_total_label align-middle pb-3">
                                        <h6 class="text-muted mb-0">Potongan distributor <span id="distributorLevelLabel">@if(!empty($priceLevelName))({{ $priceLevelName }})@endif</span></h6>
                                    </th>
                                    <td class="cart_total_amount text-end align-middle pb-3">
                                        <h5 class="text-danger mb-0" id="distributorDiscountDisplay">-Rp {{ number_format($distributorPriceDiscount ?? 0, 0, ',', '.') }}</h5>
                                    </td>
                                </tr>
                                <tr class="rg-checkout-total-row">
                                    <th class="cart_total_label align-middle pb-3">
                                        <h6 class="text-muted mb-0">Subtotal</h6>
                                    </th>
                                    <td class="cart_total_amount text-end align-middle pb-3">
                                        <h5 class="text-brand mb-0" id="subtotalDisplay">Rp {{ number_format($subtotal, 0, ',', '.') }}</h5>
                                    </td>
                                </tr>
                                <tr id="discountRow" class="rg-checkout-total-row" style="{{ $discountAmount > 0 ? '' : 'display: none;' }}">
                                    <th class="cart_total_label align-middle py-3">
                                        <h6 class="text-muted mb-0">Potongan Harga (<span id="discountPercentDisplay">{{ $discountPercent }}</span>%)</h6>
                                    </th>
                                    <td class="cart_total_amount text-end align-middle py-3">
                                        <h5 class="text-danger mb-0" id="discountAmountDisplay">-Rp {{ number_format($discountAmount, 0, ',', '.') }}</h5>
                                    </td>
                                </tr>
                                <tr class="rg-checkout-total-row rg-checkout-shipping-row">
                                    <th class="cart_total_label align-middle py-3">
                                        <h6 class="text-muted mb-2">Ongkos Kirim</h6>
                                        <small class="text-muted d-block rg-checkout-expedition-info" id="expeditionInfo">{{ $defaultExpedition?->name ?? '-' }} - {{ $defaultService['name'] ?? 'Reguler' }}</small>
                                    </th>
                                    <td class="cart_total_amount text-end align-middle py-3">
                                        <h5 class="text-brand mb-2" id="shippingCostDisplay">
                                            @if($shippingCost > 0)
                                                Rp {{ number_format($shippingCost, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </h5>
                                        <small class="text-muted d-block mt-1" id="totalWeightDisplay">Berat: {{ number_format($totalWeight / 1000, 1) }} kg</small>
                                        <small class="text-muted d-block mt-1" id="estimatedDelivery">
                                             @if($defaultService)
                                                Estimasi: {{ $defaultService['estimated_days'] }}
                                            @endif
                                        </small>
                                    </td>
                                </tr>
                                <tr class="rg-checkout-total-row rg-checkout-total-final">
                                    <th class="cart_total_label align-middle pt-4 pb-2">
                                        <h4 class="text-brand mb-0">Total</h4>
                                    </th>
                                    <td class="cart_total_amount text-end align-middle pt-4 pb-2">
                                        <h4 class="text-brand mb-0" id="totalDisplay">Rp {{ number_format($total, 0, ',', '.') }}</h4>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="rg-checkout-meta-wrap">
                        <div class="payment_method mb-15 rg-checkout-meta-card">
                             <div class="payment_accordion">
                                 <div class="payment-content" style="display: block;">
                                     <p class="font-sm mb-0"><i class="fi-rs-building mr-5"></i> Dikirim dari: <strong id="sourceWarehouseName">{{ $sourceWarehouse->name ?? 'Gudang Pusat' }}</strong></p>
                                     <p class="font-xs text-muted mb-0" id="sourceWarehouseLocation">{{ $sourceWarehouse->full_location ?? '' }}</p>
                                 </div>
                             </div>
                        </div>
                        
                        <div class="payment_method mb-0 rg-checkout-meta-card">
                            <div class="payment_accordion">
                                <div class="payment-content" style="display: block;">
                                    <p class="font-sm mb-0"><i class="fi-rs-marker mr-5"></i> Dikirim ke:</p>
                                    @if($defaultAddress)
                                        <p class="font-sm font-weight-bold mb-0 text-dark" id="shippingRecipient">{{ $defaultAddress->recipient_name }}</p>
                                        <p class="font-xs text-muted mb-0" id="shippingAddress">{{ Str::limit($defaultAddress->full_address, 60) }}</p>
                                    @else
                                        <p class="font-sm text-danger mb-0" id="shippingRecipient">Pilih alamat pengiriman</p>
                                        <p class="font-xs text-muted mb-0" id="shippingAddress"></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check mt-30 mb-15">
                        <input class="form-check-input" type="checkbox" id="agreeTerms">
                        <label class="form-check-label text-muted font-xs" style="line-height: 1.5;" for="agreeTerms">
                            Saya telah memeriksa seluruh detail pesanan dan memahami bahwa pesanan yang telah dibayar tidak dapat dibatalkan, diubah, atau direfund karena perubahan keputusan maupun kesalahan pemesanan dari pihak saya. Pengecualian berlaku apabila terdapat kesalahan penjual, produk tidak tersedia, produk tidak sesuai pesanan, atau produk rusak/cacat berdasarkan kebijakan pengembalian yang berlaku.
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-fill-out w-100 rg-checkout-submit-btn" id="submitBtn" disabled>
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
        border: 1.5px solid #ECECEC;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fff;
    }
    .card-radio-btn:hover {
        border-color: #6A1B1B;
    }
    .card-radio-btn.active {
        border-color: #6A1B1B;
        background-color: rgba(106, 27, 27, 0.05);
        position: relative;
    }
    .card-radio-btn.active::after {
        content: '\f143';
        font-family: 'uicons-regular-straight';
        position: absolute;
        top: 8px;
        right: 8px;
        color: #6A1B1B;
        font-size: 14px;
    }
    
    .address-card, .payment-method-card {
        border: 1.5px solid #ECECEC;
        padding: 20px;
        border-radius: 15px;
        transition: all 0.3s;
        cursor: pointer;
        background: #fff;
    }
    .address-card:hover, .payment-method-card:hover {
        border-color: #6A1B1B;
        box-shadow: 0 5px 15px rgba(106, 27, 27, 0.1);
    }
    .address-card.active, .payment-method-card.active {
        border-color: #6A1B1B;
        background-color: rgba(106, 27, 27, 0.05);
    }

    /* Improve muted text contrast on checkout page only */
    .checkout-page .text-muted,
    .checkout-page small.text-muted,
    .checkout-page .font-xs.text-muted,
    .checkout-page .font-sm.text-muted,
    .checkout-page .font-small.text-muted {
        color: #5f6b7a !important;
        font-weight: 500;
    }

    .checkout-page .text-body {
        color: #253D4E;
    }

    /* Address text contrast on cream background */
    .checkout-address-detail {
        color: #253D4E;
        font-weight: 500;
        margin-bottom: 6px;
    }

    .checkout-address-location {
        color: #5f6b7a;
        font-weight: 500;
        margin-bottom: 0;
    }

    /* Summary panel shipping address */
    #shippingAddress {
        color: #5f6b7a !important;
        font-weight: 500;
    }
    
    .service-card {
        padding: 10px;
    }

    /* Input Overrides */
    textarea {
        background: #f8f9fa !important;
        border: 1px solid #ececec !important;
        border-radius: 12px !important;
        padding: 15px !important;
        height: 60px !important;
        min-height: 60px !important;
    }
    textarea:focus {
        border-color: #6A1B1B !important;
        background: #fff !important;
    }

    button#submitBtn {
        background-color: #6A1B1B !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 18px !important;
        font-family: 'Fira Sans', sans-serif !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
    }

    button#submitBtn:hover {
        background-color: #4D1313 !important;
        transform: translateY(-2px);
    }
    
    .btn-standar-outline {
        background-color: transparent !important;
        border: 1.5px solid #6A1B1B !important;
        color: #6A1B1B !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        padding: 8px 18px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    .btn-standar-outline:hover {
        background-color: #6A1B1B !important;
        color: #ffffff !important;
        transform: translateY(-2px);
    }

    /* Desktop: offset ringkasan ke kanan */
    .checkout-page .checkout-order-summary {
        margin-left: 30px;
        padding: 30px 40px;
    }

    .checkout-page .rg-checkout-item-thumb img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 10px;
        display: block;
        border: none !important;
        padding: 0 !important;
        margin-right: 15px !important;
    }

    .checkout-page .rg-checkout-item-name {
        display: block;
        font-size: 15px;
        font-weight: 600;
        color: #253D4E;
        text-decoration: none;
        line-height: 1.35;
        margin-bottom: 4px;
    }

    .checkout-page .rg-checkout-item-name:hover {
        color: #6A1B1B;
    }

    .checkout-page .rg-checkout-item-variant {
        font-size: 12px;
        color: #7E7E7E;
        line-height: 1.3;
        margin: 0 0 8px;
    }

    .checkout-page .rg-checkout-item-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 4px 6px;
        font-size: 13px;
    }

    .checkout-page .rg-checkout-item-qty {
        font-weight: 700;
        color: #253D4E;
    }

    .checkout-page .rg-checkout-item-unit {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 4px;
        color: #5f6b7a;
    }

    .checkout-page .rg-checkout-item-unit-retail {
        text-decoration: line-through;
        color: #9ca3af;
        font-size: 12px;
    }

    .checkout-page .rg-checkout-item-unit-price {
        font-weight: 600;
        color: #5f6b7a;
    }

    .checkout-page .rg-checkout-item-price {
        text-align: right;
        vertical-align: middle;
        white-space: nowrap;
    }

    .checkout-page .rg-checkout-item-price-label {
        display: none;
    }

    .checkout-page .rg-checkout-item-price-value {
        display: block;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.2;
    }

    /* Menghapus border Ringkasan Pesanan */
    .checkout-page .rg-checkout-items-wrap {
        border: none !important;
    }
    .checkout-page .rg-checkout-items-table,
    .checkout-page .rg-checkout-items-table th,
    .checkout-page .rg-checkout-items-table td,
    .checkout-page .rg-checkout-items-table tr {
        border: none !important;
    }

    .checkout-page .rg-checkout-items-table td {
        vertical-align: top !important;
        padding-top: 15px !important;
        padding-bottom: 15px !important;
    }

    .checkout-page .rg-checkout-items-table .rg-checkout-item-info {
        padding-right: 12px !important;
    }

    /* Mobile: ringkasan pesanan */
    @media (max-width: 991.98px) {
        .checkout-page .checkout-order-summary.rg-checkout-summary {
            margin-left: 0 !important;
            margin-right: 0 !important;
            margin-top: 8px;
            padding: 20px 16px 24px !important;
            text-align: left !important;
            background: #fff;
            border: 1px solid #edf2f7 !important;
            border-radius: 16px !important;
            box-shadow: 0 4px 18px rgba(37, 61, 78, 0.06);
        }

        .checkout-page .rg-checkout-summary-header h4 {
            font-size: 1.15rem;
        }

        .checkout-page .rg-checkout-items-scroll {
            max-height: none !important;
            overflow: visible !important;
            padding-right: 0 !important;
            margin-bottom: 20px !important;
        }

        .checkout-page .rg-checkout-items-wrap,
        .checkout-page .rg-checkout-items-table,
        .checkout-page .rg-checkout-items-table tbody {
            display: block;
            width: 100%;
        }

        .checkout-page .rg-checkout-item {
            display: grid !important;
            grid-template-columns: 60px 1fr minmax(92px, auto);
            grid-template-areas: "thumb info price";
            gap: 12px;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .checkout-page .rg-checkout-item:last-child {
            border-bottom: none;
        }

        .checkout-page .rg-checkout-item > td {
            display: block;
            border: none !important;
            padding: 0 !important;
        }

        .checkout-page .rg-checkout-item-thumb {
            grid-area: thumb;
            align-self: start;
        }

        .checkout-page .rg-checkout-item-thumb img {
            width: 60px !important;
            height: 60px;
            border-radius: 12px !important;
        }

        .checkout-page .rg-checkout-item-info {
            grid-area: info;
            min-width: 0;
            padding-right: 0 !important;
        }

        .checkout-page .rg-checkout-item-name {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 14px !important;
            line-height: 1.4;
            margin-bottom: 2px !important;
        }

        .checkout-page .rg-checkout-item-variant {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 11px !important;
            margin-bottom: 6px !important;
        }

        .checkout-page .rg-checkout-item-meta {
            margin-top: 2px;
        }

        .checkout-page .rg-checkout-item-qty {
            font-size: 13px;
        }

        .checkout-page .rg-checkout-item-unit-price {
            font-size: 13px;
        }

        .checkout-page .rg-checkout-item-price {
            grid-area: price;
            align-self: center;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
            gap: 2px;
        }

        .checkout-page .rg-checkout-item-price-label {
            display: block;
            font-size: 10px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .checkout-page .rg-checkout-item-price-value {
            font-size: 15px !important;
        }

        .checkout-page .rg-checkout-totals-wrap,
        .checkout-page .rg-checkout-totals-table,
        .checkout-page .rg-checkout-totals-table tbody {
            display: block;
            width: 100%;
        }

        .checkout-page .rg-checkout-totals-table tr {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            width: 100%;
            padding: 10px 0;
        }

        .checkout-page .rg-checkout-totals-table tr.rg-checkout-divider-row {
            display: block;
            padding: 0;
        }

        .checkout-page .rg-checkout-totals-table tr > td {
            display: block;
            border: none !important;
            padding: 0 !important;
        }

        .checkout-page .rg-checkout-totals-table .cart_total_label {
            flex: 1 1 auto;
            min-width: 0;
            text-align: left !important;
        }

        .checkout-page .rg-checkout-totals-table .cart_total_label h6,
        .checkout-page .rg-checkout-totals-table .cart_total_label h4 {
            font-size: 14px;
            line-height: 1.4;
        }

        .checkout-page .rg-checkout-totals-table .cart_total_amount {
            flex: 0 0 auto;
            text-align: right !important;
            max-width: 48%;
        }

        .checkout-page .rg-checkout-totals-table .cart_total_amount h5,
        .checkout-page .rg-checkout-totals-table .cart_total_amount h4 {
            font-size: 14px !important;
            text-align: right !important;
            margin-bottom: 0;
        }

        .checkout-page .rg-checkout-shipping-row {
            align-items: flex-start;
        }

        .checkout-page .rg-checkout-expedition-info {
            margin-top: 4px;
            line-height: 1.35;
            font-size: 11px !important;
        }

        .checkout-page .rg-checkout-shipping-amounts {
            flex-direction: column;
            align-items: flex-end !important;
            gap: 4px !important;
        }

        .checkout-page .rg-checkout-total-final {
            background: #f8fafc;
            border-radius: 12px;
            padding: 14px 12px !important;
            margin-top: 4px;
        }

        .checkout-page .rg-checkout-total-final .cart_total_label h4,
        .checkout-page .rg-checkout-total-final .cart_total_amount h4 {
            font-size: 18px !important;
            font-weight: 700;
        }

        .checkout-page .rg-checkout-meta-wrap {
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid #edf2f7;
        }

        .checkout-page .rg-checkout-meta-card {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 12px !important;
        }

        .checkout-page .rg-checkout-meta-card .payment-content {
            padding: 0 !important;
        }

        .checkout-page .rg-checkout-submit-btn {
            width: 100%;
            margin-top: 20px !important;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    window.checkoutTotalWithoutShipping = {{ (float) ($subtotal - $discountAmount) }};
    var currentAddressId = '{{ $defaultAddress?->id ?? '' }}';
    var currentExpeditionId = '{{ $defaultExpedition?->id ?? '' }}';
    var currentServiceCode = @json($defaultService['code'] ?? null);
    var servicesLoading = false;

    function syncCheckoutShippingStateFromForm() {
        var checkedExpedition = $('input[name="expedition_id"]:checked').val();
        var checkedService = $('#serviceList input[name="expedition_service"]:checked').val();

        if (checkedExpedition) {
            currentExpeditionId = String(checkedExpedition);
        }
        if (checkedService) {
            currentServiceCode = String(checkedService);
        }
    }

    function setServiceRadioChecked(serviceCode) {
        $('#serviceList input[name="expedition_service"]').prop('checked', false);
        $('#serviceList input[name="expedition_service"]').each(function() {
            if (String($(this).val()) === String(serviceCode)) {
                $(this).prop('checked', true);
            }
        });
    }

    function setSubmitEnabled(enabled) {
        var btn = $('#submitBtn');
        if (!btn.length) {
            return;
        }
        
        var termsAgreed = $('#agreeTerms').is(':checked');
        
        if (enabled && currentAddressId && currentExpeditionId && currentServiceCode && !servicesLoading && termsAgreed) {
            btn.prop('disabled', false);
        } else {
            btn.prop('disabled', true);
        }
    }
    
    // Listen for terms agreement change
    $(document).on('change', '#agreeTerms', function() {
        setSubmitEnabled(true);
    });
    
    function selectAddress(addressId) {
        currentAddressId = addressId;
        
        // Update radio button
        $('#address' + addressId).prop('checked', true);
        
        // Update styling
        $('.address-card').removeClass('active');
        var selectedCard = $('[data-address-id="' + addressId + '"]');
        selectedCard.addClass('active');
        
        // Update summary immediately for better UX
        var recipient = selectedCard.data('recipient');
        var fullAddress = selectedCard.data('full-address');
        
        if (recipient) {
            $('#shippingRecipient').text(recipient).removeClass('text-danger');
        }
        if (fullAddress) {
            $('#shippingAddress').text(fullAddress);
        }
        
        // Reload services
        if (currentExpeditionId) {
            loadExpeditionServices(currentExpeditionId);
        }
    }
    
    function selectExpedition(expeditionId) {
        if (!currentAddressId) {
            alert('Silakan pilih alamat pengiriman terlebih dahulu');
            return;
        }

        if (expeditionId === currentExpeditionId && $('#serviceList input[name="expedition_service"]').length > 0) {
            return;
        }

        currentExpeditionId = expeditionId;
        currentServiceCode = '';
        
        // Update radio button
        $('input[name="expedition_id"]').prop('checked', false);
        $('#exp' + expeditionId).prop('checked', true);
        
        // Update styling
        $('.expedition-card').removeClass('active');
        $('[data-expedition-id="' + expeditionId + '"]').addClass('active');
        
        // Load services
        loadExpeditionServices(expeditionId);
    }
    
    function loadExpeditionServices(expeditionId) {
        if (!currentAddressId) {
            alert('Silakan pilih alamat pengiriman terlebih dahulu');
            return;
        }
    
        var serviceList = $('#serviceList');
        servicesLoading = true;
        setSubmitEnabled(false);
        currentServiceCode = '';
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
                    servicesLoading = false;
                    setSubmitEnabled(false);
                    return;
                }
                
                serviceList.empty();
                
                if (data.services.length === 0) {
                    serviceList.html('<div class="col-12"><div class="alert alert-warning py-2 small"><i class="fi-rs-info"></i> Tidak ada layanan pengiriman tersedia untuk wilayah ini.</div></div>');
                    servicesLoading = false;
                    setSubmitEnabled(false);
                    return;
                }
    
                data.services.forEach(function(service, index) {
                    var isSelected = index === 0;
                    if (isSelected) {
                        currentServiceCode = String(service.code);
                    }
                    
                    var serviceHtml = `
                        <div class="col-md-6 mb-10">
                            <div class="card-radio-btn service-card ${isSelected ? 'active' : ''}"
                                 data-service-code="${service.code}"
                                 onclick='selectService(${JSON.stringify(service.code)})'>
                                <input type="radio" name="expedition_service" value="${service.code}" 
                                       class="d-none"
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
                    // Update Warehouse info if returned (keeps Geolocation info in sync)
                    if (data.warehouse) {
                        $('#sourceWarehouseName').text(data.warehouse.name);
                        $('#sourceWarehouseLocation').text(data.warehouse.location);
                    }
                    updateShipping();
                } else {
                     // Reset shipping displays if no services
                    $('#shippingCostDisplay').text('-');
                    $('#totalDisplay').text('Rp ' + Number(window.checkoutTotalWithoutShipping || 0).toLocaleString('id-ID')); // Subtotal − potongan tier (tanpa ongkir)
                    $('#estimatedDelivery').text('-');
                }

                servicesLoading = false;
                setSubmitEnabled(true);
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                serviceList.html('<div class="col-12"><div class="alert alert-danger">Gagal memuat layanan pengiriman</div></div>');
                servicesLoading = false;
                setSubmitEnabled(false);
            }
        });
    }
    
    function selectService(serviceCode) {
        currentServiceCode = String(serviceCode);
        
        setServiceRadioChecked(serviceCode);
        
        // Update styling
        $('.service-card').removeClass('active');
        $('#serviceList .service-card').filter(function() {
            return String($(this).data('service-code')) === String(serviceCode);
        }).addClass('active');
        
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
                    alert(data.error);
                    return;
                }
                
                // Update displays
                $('#shippingCostDisplay').text(data.shipping_cost_formatted);
                $('#totalDisplay').text(data.total_formatted);
                $('#subtotalDisplay').text(data.subtotal_formatted);
                if (data.total_weight_formatted) {
                    $('#totalWeightDisplay').text('Berat: ' + data.total_weight_formatted);
                }
                window.checkoutTotalWithoutShipping = parseFloat(data.subtotal) - parseFloat(data.discount_amount);

                if (data.show_distributor_pricing) {
                    $('#distributorRetailRow').show();
                    $('#distributorDiscountRow').show();
                    $('#retailSubtotalDisplay').text(data.retail_subtotal_formatted);
                    $('#distributorDiscountDisplay').text('-' + data.distributor_price_discount_formatted);
                    if (data.price_level_name) {
                        $('#distributorLevelLabel').text('(' + data.price_level_name + ')');
                    } else {
                        $('#distributorLevelLabel').text('');
                    }
                } else {
                    $('#distributorRetailRow').hide();
                    $('#distributorDiscountRow').hide();
                }
                
                // Update Address & Hub info
                if (data.address && data.address.id == currentAddressId) {
                    $('#shippingRecipient').text(data.address.recipient_name).removeClass('text-danger');
                    $('#shippingAddress').text(data.address.full_address);
                }

                if (data.warehouse) {
                    $('#sourceWarehouseName').text(data.warehouse.name);
                    $('#sourceWarehouseLocation').text(data.warehouse.location);
                }

                // Handle Hub changes and Stock warnings
                $('#checkoutAlertContainer').empty();
                if (data.hub_changed) {
                    const hubAlert = `
                        <div class="alert alert-info alert-dismissible fade show mb-30" role="alert">
                            <i class="fi-rs-info mr-10"></i> Sumber pengiriman diubah ke <strong>${data.warehouse.name}</strong> menyesuaikan alamat Anda.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`;
                    $('#checkoutAlertContainer').append(hubAlert);
                }

                if (data.stock_warnings && data.stock_warnings.length > 0) {
                    // Hide session alerts if we are showing new stock warnings from AJAX
                    $('#sessionAlerts').empty();
                    
                    const warningHtml = data.stock_warnings.join('<br>');
                    const removeBtnHtml = `
                        <div class="mt-15">
                            <form action="{{ route('cart.remove-out-of-stock') }}" method="POST">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-xs btn-outline-danger">Hapus Item Habis Stok</button>
                            </form>
                        </div>`;
                        
                    const stockAlert = `
                        <div class="alert alert-warning alert-dismissible fade show mb-30" role="alert">
                            <i class="fi-rs-exclamation mr-10"></i> <strong>Peringatan Stok:</strong><br>${warningHtml}
                            ${removeBtnHtml}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`;
                    $('#checkoutAlertContainer').append(stockAlert);
                }

                // Update discount if present in response
                if (data.discount_amount > 0) {
                    $('#discountPercentDisplay').text(data.discount_percent);
                    $('#discountAmountDisplay').text('-' + data.discount_amount_formatted);
                    $('#discountRow').show();
                } else {
                    $('#discountRow').hide();
                }

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
                alert("Gagal menghitung biaya pengiriman");
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
        else if(method === 'term_of_payment') cardId = 'card-tot';
        else if(method === 'cod') cardId = 'card-cod';
        
        if(cardId) {
             $('#' + cardId).addClass('active');
        }
    }

    $('#checkoutForm').on('submit', function(e) {
        if (servicesLoading) {
            e.preventDefault();
            alert('Mohon tunggu, layanan pengiriman sedang dimuat.');
            return false;
        }

        syncCheckoutShippingStateFromForm();

        var checkedExpedition = $('input[name="expedition_id"]:checked').val();
        var checkedService = $('#serviceList input[name="expedition_service"]:checked').val();

        if (!checkedExpedition || !checkedService) {
            e.preventDefault();
            alert('Silakan pilih ekspedisi dan layanan pengiriman.');
            return false;
        }
    });

    $(function() {
        syncCheckoutShippingStateFromForm();
        
        var checkedExpedition = $('input[name="expedition_id"]:checked').val();
        var serverExpedition = @json($defaultExpedition?->id ?? null);
        if (checkedExpedition && checkedExpedition !== serverExpedition) {
            selectExpedition(checkedExpedition);
        }
        
        setSubmitEnabled(true);
    });
</script>
@endpush
