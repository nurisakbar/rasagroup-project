@extends('layouts.shop')

@section('title', 'Checkout Distributor')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('distributor.orders.products') }}">Pesan ke Pusat</a>
            <span></span> Checkout
        </div>
    </div>
</div>

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4">
                    @include('buyer.partials.sidebar')
                </div>
                <div class="col-lg-8">
                    <form action="{{ route('distributor.orders.store') }}" method="POST" id="checkout-form">
                        @csrf
                        <div class="tab-content account dashboard-content pl-50">
                            <div class="tab-pane fade show active" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-7">
                                        <div class="card border-0 shadow-sm border-radius-10 mb-4">
                                            <div class="card-header bg-white border-bottom p-4">
                                                <h4 class="mb-0">Alamat Pengiriman</h4>
                                                <p class="text-muted font-sm">Pilih alamat tujuan pengiriman stok.</p>
                                            </div>
                                            <div class="card-body p-4">
                                                <div class="row g-3">
                                                    @foreach($addresses as $address)
                                                        <div class="col-12">
                                                            <div class="address-item p-3 border border-radius-10 {{ $address->is_default ? 'border-brand' : '' }}">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="address_id" id="address_{{ $address->id }}" value="{{ $address->id }}" {{ $address->id === $defaultAddress->id ? 'checked' : '' }}>
                                                                    <label class="form-check-label w-100" for="address_{{ $address->id }}">
                                                                        <div class="d-flex justify-content-between">
                                                                            <h6 class="mb-1">{{ $address->recipient_name }} @if($address->is_default) <span class="badge bg-brand fs-tiny ml-10">Default</span> @endif</h6>
                                                                            <span class="text-muted font-sm">{{ $address->phone }}</span>
                                                                        </div>
                                                                        <p class="font-sm text-muted mb-0">
                                                                            {{ $address->address_detail }}<br>
                                                                            {{ $address->district->name ?? '' }}, {{ $address->regency->name ?? '' }}, {{ $address->province->name ?? '' }} {{ $address->postal_code }}
                                                                        </p>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card border-0 shadow-sm border-radius-10 mb-4">
                                            <div class="card-header bg-white border-bottom p-4">
                                                <h4 class="mb-0">Pilih Pengirim (Hub)</h4>
                                                <p class="text-muted font-sm">Pilih gudang pusat/hub asal pengiriman.</p>
                                            </div>
                                            <div class="card-body p-4">
                                                <select name="source_warehouse_id" class="form-select font-sm border-radius-10">
                                                    @foreach($warehouses as $wh)
                                                        <option value="{{ $wh->id }}">{{ $wh->name }} - {{ $wh->regency->name ?? '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="card border-0 shadow-sm border-radius-10 mb-4">
                                            <div class="card-header bg-white border-bottom p-4">
                                                <h4 class="mb-0">Metode Pengiriman</h4>
                                            </div>
                                            <div class="card-body p-4">
                                                <div class="mb-4">
                                                    <label class="form-label font-sm fw-bold">Pilih Kurir</label>
                                                    <select name="expedition_id" id="expedition_id" class="form-select font-sm mb-3">
                                                        @foreach($expeditions as $exp)
                                                            <option value="{{ $exp->id }}">{{ $exp->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label font-sm fw-bold">Pilih Layanan</label>
                                                    <div id="services-container" class="row g-2">
                                                        <!-- Services populated by AJAX -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card border-0 shadow-sm border-radius-10 mb-4">
                                            <div class="card-header bg-white border-bottom p-4">
                                                <h4 class="mb-0">Metode Pembayaran</h4>
                                            </div>
                                            <div class="card-body p-4">
                                                <div class="payment-option p-3 border border-radius-10 mb-3 border-brand">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="payment_method" id="pay_transfer" value="transfer" checked>
                                                        <label class="form-check-label w-100" for="pay_transfer">
                                                            <h6 class="mb-1">Transfer Bank</h6>
                                                            <p class="font-sm text-muted mb-0">Pembayaran melalui transfer bank manual atau virtual account.</p>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-5">
                                        <div class="card border-0 shadow-sm border-radius-10 position-sticky" style="top: 20px;">
                                            <div class="card-header bg-white border-bottom p-4">
                                                <h4 class="mb-0">Ringkasan Pesanan</h4>
                                            </div>
                                            <div class="card-body p-4">
                                                <div class="order-items mb-4">
                                                    @foreach($carts as $cart)
                                                        <div class="d-flex align-items-center mb-3">
                                                            <img src="{{ asset($cart->product->image_url) }}" class="border-radius-5 me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0 fs-tiny">{{ Str::limit($cart->product->display_name, 30) }}</h6>
                                                                <p class="font-xs text-muted">{{ $cart->quantity }} x Rp {{ number_format($cart->display_price, 0, ',', '.') }}</p>
                                                            </div>
                                                            <div class="text-end">
                                                                <p class="font-sm fw-bold">Rp {{ number_format($cart->display_subtotal, 0, ',', '.') }}</p>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="divider-2 mb-4"></div>

                                                <div class="order-totals">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted font-sm">Subtotal</span>
                                                        <span class="font-sm fw-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted font-sm">Ongkos Kirim</span>
                                                        <span class="font-sm fw-bold" id="shipping-cost-display">Rp {{ number_format($shippingCost, 0, ',', '.') }}</span>
                                                    </div>
                                                    <div class="divider-2 mt-2 mb-3"></div>
                                                    <div class="d-flex justify-content-between mb-4">
                                                        <h5 class="mb-0">Total</h5>
                                                        <h4 class="mb-0 text-brand" id="total-amount-display">Rp {{ number_format($total, 0, ',', '.') }}</h4>
                                                    </div>
                                                </div>

                                                <div class="mb-4">
                                                    <label class="form-label font-sm fw-bold">Catatan Pesanan (Opsional)</label>
                                                    <textarea name="notes" class="form-control font-sm" rows="3" placeholder="Contoh: Titip di satpam..."></textarea>
                                                </div>

                                                <button type="submit" class="btn btn-brand rounded-pill w-100 py-3 mt-2" id="btn-place-order">Buat Pesanan Sekarang <i class="fi-rs-check ml-10"></i></button>
                                                <p class="font-xs text-muted text-center mt-10"><i class="fi-rs-lock mr-5"></i> Pembayaran Anda aman dan terenkripsi.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .address-item, .payment-option { cursor: pointer; transition: all 0.3s; }
    .address-item:hover, .payment-option:hover { border-color: #3BB77E !important; background-color: #f7fef9; }
    .service-item { cursor: pointer; transition: all 0.3s; }
    .service-item input:checked + label .card { border-color: #3BB77E !important; background-color: #f7fef9; }
    .badge.bg-brand { background-color: #3BB77E !important; }
    .fs-tiny { font-size: 11px; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    function loadServices() {
        var addressId = $('input[name="address_id"]:checked').val();
        var expeditionId = $('#expedition_id').val();
        
        if (!addressId || !expeditionId) return;

        $('#services-container').html('<div class="col-12 text-center py-3"><div class="spinner-border spinner-border-sm text-brand" role="status"></div></div>');

        $.ajax({
            url: "{{ route('distributor.orders.shipping-services') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                address_id: addressId,
                expedition_id: expeditionId
            },
            success: function(response) {
                var html = '';
                response.services.forEach(function(service, index) {
                    var checked = index === 0 ? 'checked' : '';
                    html += `
                        <div class="col-md-6 service-item">
                            <input class="form-check-input d-none" type="radio" name="expedition_service" id="svc_${service.code}" value="${service.code}" ${checked}>
                            <label class="w-100" for="svc_${service.code}">
                                <div class="card border border-radius-10 shadow-none p-3 h-100">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="mb-0 font-sm">${service.name}</h6>
                                        <span class="text-brand font-sm fw-bold">${service.cost_formatted}</span>
                                    </div>
                                    <p class="font-xs text-muted mb-0">Estimasi ${service.estimated_days}</p>
                                </div>
                            </label>
                        </div>
                    `;
                });
                $('#services-container').html(html);
                updateTotals();
            },
            error: function() {
                $('#services-container').html('<div class="col-12 text-danger font-sm">Gagal memuat layanan.</div>');
            }
        });
    }

    function updateTotals() {
        var addressId = $('input[name="address_id"]:checked').val();
        var expeditionId = $('#expedition_id').val();
        var serviceCode = $('input[name="expedition_service"]:checked').val();

        if (!addressId || !expeditionId || !serviceCode) return;

        $.ajax({
            url: "{{ route('distributor.orders.calculate-shipping') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                address_id: addressId,
                expedition_id: expeditionId,
                service_code: serviceCode
            },
            success: function(response) {
                $('#shipping-cost-display').text(response.shipping_cost_formatted);
                $('#total-amount-display').text(response.total_formatted);
            }
        });
    }

    // Initial load
    loadServices();

    // Event handlers
    $('input[name="address_id"]').change(loadServices);
    $('#expedition_id').change(loadServices);
    $(document).on('change', 'input[name="expedition_service"]', updateTotals);

    $('#checkout-form').on('submit', function() {
        $('#btn-place-order').prop('disabled', true).html('<div class="spinner-border spinner-border-sm text-white" role="status"></div> Sedang memproses...');
    });
});
</script>
@endpush
