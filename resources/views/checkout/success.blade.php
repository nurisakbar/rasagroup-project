@extends('layouts.shop')

@section('title', 'Pesanan Berhasil')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Checkout
            <span></span> Selesai
        </div>
    </div>
</div>

<div class="page-content pt-100 pb-100 checkout-success-page" style="background-color: #F2EAE1;">
    <style>
        .checkout-success-section-title {
            font-weight: 700;
            color: #253D4E;
            font-size: 1rem;
            margin-bottom: 0.85rem;
        }
        .checkout-success-name {
            color: #253D4E;
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.4;
        }
        .checkout-success-phone {
            color: #253D4E;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.5;
            margin: 0;
        }
        .checkout-success-address-line {
            color: #253D4E;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.65;
            margin: 0;
        }
    </style>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm p-40 border-radius-30" style="background-color: #ffffff;">
                    <div class="card-body text-center">
                        <div class="mb-30">
                            <i class="fi-rs-check-circle" style="font-size: 80px; color: #6A1B1B;"></i>
                        </div>
                        <h2 class="mb-20" style="font-weight: 700; color: #253D4E;">Pesanan Berhasil Dibuat!</h2>
                        <p class="text-muted mb-40">Terima kasih atas pembelian Anda. Pesanan Anda sedang kami proses.</p>
                        
                        <div class="mb-40 p-30 border-radius-20" style="background-color: #F8F9FA; border: 1.5px dashed #ECECEC;">
                            <div class="row align-items-center">
                                <div class="col-md-6 text-md-start text-center mb-md-0 mb-3">
                                    <span class="font-md d-block mb-1" style="color: #7E7E7E;">Nomor Pesanan</span>
                                    <h4 style="font-weight: 700; color: #6A1B1B; margin: 0;">{{ $order->order_number }}</h4>
                                </div>
                                <div class="col-md-6 text-md-end text-center">
                                    <span class="font-md d-block mb-1" style="color: #7E7E7E;">Total Pembayaran</span>
                                    <h4 style="font-weight: 700; color: #6A1B1B; margin: 0;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h4>
                                </div>
                            </div>
                        </div>

                        @if($order->sourceWarehouse)
                            @php $hub = $order->sourceWarehouse; @endphp
                            <div class="mb-40 p-25 border-radius-20 text-start checkout-success-address-block" style="background-color: #fffaf8; border: 1.5px solid #edd6d0;">
                                <h5 class="mb-15 checkout-success-section-title">
                                    <i class="fi-rs-shop mr-10"></i>Barang dikirim dari
                                </h5>
                                <strong class="d-block mb-10 checkout-success-name">{{ $hub->name }}</strong>
                                @if($hub->address)
                                    <p class="checkout-success-address-line mb-5">{{ $hub->address }}</p>
                                @endif
                                <p class="checkout-success-address-line mb-0">
                                    @if($hub->district)
                                        Kec. {{ $hub->district->name }},
                                    @endif
                                    {{ $hub->full_location }}
                                    @if($hub->postal_code)
                                        &nbsp;{{ $hub->postal_code }}
                                    @endif
                                </p>
                                @if($hub->phone)
                                    <p class="checkout-success-phone mt-10 mb-0">
                                        <i class="fi-rs-headset mr-5"></i>{{ $hub->phone }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <div class="row text-start mb-40 g-4">
                            <!-- Shipping Address -->
                            <div class="col-md-6">
                                <div class="p-25 h-100 border-radius-20 checkout-success-address-block" style="background-color: #ffffff; border: 1.5px solid #ECECEC;">
                                    <h5 class="mb-15 checkout-success-section-title"><i class="fi-rs-marker mr-10"></i>Alamat Penerima</h5>
                                    @if($order->address)
                                        <strong class="d-block mb-8 checkout-success-name">{{ $order->address->recipient_name }}</strong>
                                        <p class="checkout-success-phone mb-8">{{ $order->address->phone }}</p>
                                        <p class="checkout-success-address-line mb-0">
                                            {{ $order->address->address_detail }}<br>
                                            @if($order->address->village?->name)
                                                {{ $order->address->village->name }},
                                            @endif
                                            Kec. {{ $order->address->district?->name }}<br>
                                            {{ $order->address->regency?->name }}, {{ $order->address->province?->name }}
                                            @if($order->address->postal_code)
                                                <br>{{ $order->address->postal_code }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Shipping Method -->
                            <div class="col-md-6">
                                <div class="p-25 h-100 border-radius-20 checkout-success-address-block" style="background-color: #ffffff; border: 1.5px solid #ECECEC;">
                                    <h5 class="mb-15 checkout-success-section-title"><i class="fi-rs-truck-side mr-10"></i>Pengiriman</h5>
                                    @if($order->expedition)
                                        <strong class="d-block mb-8 checkout-success-name">{{ $order->expedition->name }}</strong>
                                        <p class="checkout-success-address-line mb-0">
                                            Layanan: {{ $order->expedition_service }}<br>
                                            Estimasi: {{ $order->expedition->estimated_delivery }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="text-start mb-50">
                            <h4 class="mb-20" style="font-weight: 700; color: #253D4E;">Instruksi Pembayaran</h4>
                            @if($order->payment_method === 'manual_transfer')
                                <div class="p-30 border-radius-20" style="background-color: rgba(106, 27, 27, 0.03); border: 1px solid rgba(106, 27, 27, 0.1);">
                                    <h6 class="mb-20" style="color: #6A1B1B;"><i class="fi-rs-info mr-10"></i>Silakan transfer ke salah satu rekening:</h6>
                                    <div class="row g-3">
                                        @foreach([['BCA', '1234567890'], ['Mandiri', '0987654321'], ['BNI', '5678901234']] as $bank)
                                            <div class="col-md-4">
                                                <div class="bg-white p-15 border-radius-12 text-center shadow-sm">
                                                    <strong class="d-block mb-1" style="color: #6A1B1B;">{{ $bank[0] }}</strong>
                                                    <span class="d-block fw-bold" style="font-size: 16px; color: #253D4E;">{{ $bank[1] }}</span>
                                                    <small class="text-muted">a.n PT Rasa Group</small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="mt-20 mb-0 text-center font-sm" style="color: #7E7E7E;">Bayar sebelum: <strong>{{ $order->created_at->addDay()->format('d M Y, H:i') }}</strong></p>
                                </div>
                            @elseif($order->payment_method === 'xendit')
                                <div id="checkout-xendit-root">
                                    <p id="checkout-payment-sync-hint" class="text-center font-sm mb-20" style="color: #7E7E7E; display: none;">
                                        <span class="d-inline-block animate-pulse" style="animation: checkoutPulse 1.2s ease-in-out infinite;">Memverifikasi status pembayaran…</span>
                                    </p>
                                    <div id="checkout-xendit-paid-pane" class="p-30 border-radius-20 text-center" style="background-color: #e8f5e9; border: 1px solid #c8e6c9; {{ $order->payment_status === 'paid' ? '' : 'display: none;' }}">
                                        <h6 class="mb-10" style="color: #2e7d32;"><i class="fi-rs-check-circle mr-10"></i>Pembayaran Berhasil!</h6>
                                        <p class="mb-0" style="color: #2e7d32;">Terima kasih, pembayaran Anda telah kami terima secara otomatis.</p>
                                    </div>
                                    <div id="checkout-xendit-failed-pane" class="p-30 border-radius-20 text-center" style="background-color: #ffebee; border: 1px solid #ffcdd2; {{ in_array($order->payment_status, ['failed', 'refunded'], true) ? '' : 'display: none;' }}">
                                        <h6 class="mb-10" style="color: #c62828;"><i class="fi-rs-close-circle mr-10"></i>Pembayaran tidak berhasil</h6>
                                        <p class="mb-0" style="color: #5d4037;">Status: {{ strtoupper($order->payment_status) }}. Silakan buat pesanan baru atau hubungi kami jika Anda sudah membayar.</p>
                                    </div>
                                    @php $paymentUrl = $order->faspay_redirect_url ?? $order->xendit_invoice_url; @endphp
                                    <div id="checkout-xendit-pending-pane" class="p-30 border-radius-20 text-center" style="background-color: rgba(106, 27, 27, 0.03); border: 1px solid rgba(106, 27, 27, 0.1); {{ ($order->payment_status === 'paid' || in_array($order->payment_status, ['failed', 'refunded'], true) || ! $paymentUrl) ? 'display: none;' : '' }}">
                                        <p class="mb-15 text-brand" style="font-size: 16px;"><strong>Selesaikan Pembayaran Anda</strong></p>
                                        <p class="mb-20 text-muted">Pesanan ini sedang menunggu pembayaran. Silakan klik tombol di bawah untuk melanjutkan ke halaman pembayaran.</p>
                                        <a id="checkout-xendit-pay-link" href="{{ $paymentUrl }}" class="btn" target="_blank">Bayar Sekarang</a>
                                    </div>
                                </div>
                                @if($order->payment_status === 'pending')
                                    <style>
                                        @keyframes checkoutPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.45; } }
                                    </style>
                                    <script>
                                        (function () {
                                            var url = @json(route('checkout.success.payment-status', $order));
                                            var pollMs = 2500;
                                            var maxTicks = 120;
                                            var token = document.querySelector('meta[name="csrf-token"]');
                                            token = token ? token.getAttribute('content') : '';
                                            var syncHint = document.getElementById('checkout-payment-sync-hint');
                                            var paidPane = document.getElementById('checkout-xendit-paid-pane');
                                            var pendingPane = document.getElementById('checkout-xendit-pending-pane');
                                            var failedPane = document.getElementById('checkout-xendit-failed-pane');
                                            var payLink = document.getElementById('checkout-xendit-pay-link');
                                            var ticks = 0;
                                            function show(el, on) { if (el) el.style.display = on ? '' : 'none'; }
                                            function apply(data) {
                                                var st = data.payment_status;
                                                if (st === 'paid') {
                                                    show(syncHint, false);
                                                    show(paidPane, true);
                                                    show(pendingPane, false);
                                                    show(failedPane, false);
                                                    return true;
                                                }
                                                if (st === 'failed' || st === 'refunded') {
                                                    show(syncHint, false);
                                                    show(paidPane, false);
                                                    show(pendingPane, false);
                                                    show(failedPane, true);
                                                    if (failedPane) failedPane.querySelector('p').textContent = 'Status: ' + String(st).toUpperCase() + '. Silakan buat pesanan baru atau hubungi kami jika Anda sudah membayar.';
                                                    return true;
                                                }
                                                if (data.payment_url && payLink) payLink.href = data.payment_url;
                                                show(paidPane, false);
                                                show(failedPane, false);
                                                show(pendingPane, !!data.payment_url);
                                                return false;
                                            }
                                            function tick() {
                                                ticks++;
                                                if (ticks > maxTicks) {
                                                    show(syncHint, false);
                                                    return;
                                                }
                                                fetch(url, {
                                                    headers: {
                                                        'Accept': 'application/json',
                                                        'X-Requested-With': 'XMLHttpRequest',
                                                        'X-CSRF-TOKEN': token
                                                    },
                                                    credentials: 'same-origin'
                                                })
                                                    .then(function (r) {
                                                        if (!r.ok) throw new Error('HTTP ' + r.status);
                                                        return r.json();
                                                    })
                                                    .then(function (data) {
                                                        if (apply(data)) return;
                                                        setTimeout(tick, pollMs);
                                                    })
                                                    .catch(function () {
                                                        setTimeout(tick, pollMs);
                                                    });
                                            }
                                            show(syncHint, true);
                                            tick();
                                        })();
                                    </script>
                                @endif
                            @endif
                        </div>

                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="{{ route('buyer.orders.show', $order) }}" class="btn btn-outline-rasa" style="min-width: 200px;">
                                <i class="fi-rs-file-text mr-10"></i>Detail Pesanan
                            </a>
                            <a href="{{ route('products.index') }}" class="btn" style="min-width: 200px;">
                                <i class="fi-rs-shopping-bag mr-10"></i>Lanjut Belanja
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
