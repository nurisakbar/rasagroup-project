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
                        @if($order->payment_method !== 'term_of_payment')
                            <div class="text-start mb-50">
                                <h4 class="mb-20" style="font-weight: 700; color: #253D4E;">Instruksi Pembayaran</h4>
                                @if(in_array($order->payment_method, ['manual_transfer', 'transfer']))
                                    <div class="p-30 border-radius-20 bg-white shadow-sm" style="border: 1px solid #ECECEC;">
                                        <div class="d-flex align-items-center mb-25">
                                            <div class="icon-wrap mr-15" style="width: 45px; height: 45px; border-radius: 50%; background: rgba(106, 27, 27, 0.05); display: flex; align-items: center; justify-content: center;">
                                                <i class="fi-rs-bank" style="font-size: 22px; color: #6A1B1B;"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-1" style="color: #253D4E;">Bank BCA</h5>
                                                <span class="font-sm" style="color: #7E7E7E;">KCP MUARA KARANG 2</span>
                                            </div>
                                        </div>
                                        
                                        <div class="p-25 border-radius-12 mb-20 text-center" style="background: #F8F9FA; border: 1.5px dashed #E2E2E2;">
                                            <div class="mb-15">
                                                <span class="font-sm d-block mb-5" style="color: #7E7E7E;">Nomor Rekening</span>
                                                <h2 class="mb-0" style="color: #6A1B1B; letter-spacing: 1.5px; font-weight: 700;">6371 7598 99</h2>
                                            </div>
                                            <div>
                                                <span class="font-sm d-block mb-5" style="color: #7E7E7E;">Atas Nama</span>
                                                <h5 class="mb-0" style="color: #253D4E;">RASA DISTRIBUSI INDONESIA PT</h5>
                                            </div>
                                        </div>
                                        <div class="text-center mt-25">
                                            <p class="mb-0 font-sm" style="color: #7E7E7E;">
                                                <i class="fi-rs-time mr-5"></i>Batas Waktu Pembayaran: <strong style="color: #253D4E;">{{ $order->created_at->addMinutes(15)->format('d M Y, H:i') }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                @elseif(str_starts_with($order->payment_method, 'faspay_'))
                                    <!-- Faspay SNAP UI -->
                                    <div class="p-30 border-radius-20 bg-white shadow-sm" style="border: 1px solid #ECECEC;">
                                        @if($order->payment_method === 'faspay_qris')
                                            <div class="text-center mb-20">
                                                <h5 class="mb-15" style="color: #253D4E;">Scan QRIS untuk Membayar</h5>
                                                @if($order->virtual_account_no)
                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($order->virtual_account_no) }}" alt="QRIS Barcode" style="max-width: 250px; border: 2px solid #ECECEC; padding: 10px; border-radius: 10px;">
                                                @else
                                                    <p class="text-danger">Gagal memuat QRIS. Silakan hubungi admin.</p>
                                                @endif
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center mb-25">
                                                <div class="icon-wrap mr-15" style="width: 45px; height: 45px; border-radius: 50%; background: rgba(106, 27, 27, 0.05); display: flex; align-items: center; justify-content: center;">
                                                    <i class="fi-rs-bank" style="font-size: 22px; color: #6A1B1B;"></i>
                                                </div>
                                                <div>
                                                    @php
                                                        $bankNames = [
                                                            'faspay_permata_va' => 'Bank Permata',
                                                            'faspay_mandiri_va' => 'Bank Mandiri',
                                                            'faspay_bri_va' => 'Bank BRI',
                                                            'faspay_bni_va' => 'Bank BNI',
                                                            'faspay_cimb_va' => 'Bank CIMB Niaga',
                                                        ];
                                                        $bankName = $bankNames[$order->payment_method] ?? 'Virtual Account';
                                                    @endphp
                                                    <h5 class="mb-1" style="color: #253D4E;">{{ $bankName }}</h5>
                                                    <span class="font-sm" style="color: #7E7E7E;">Virtual Account</span>
                                                </div>
                                            </div>
                                            
                                            <div class="p-25 border-radius-12 mb-20 text-center" style="background: #F8F9FA; border: 1.5px dashed #E2E2E2;">
                                                <div class="mb-15">
                                                    <span class="font-sm d-block mb-5" style="color: #7E7E7E;">Nomor Virtual Account</span>
                                                    <h2 class="mb-0" style="color: #6A1B1B; letter-spacing: 1.5px; font-weight: 700;">{{ $order->virtual_account_no }}</h2>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="text-center mt-25">
                                            <p class="mb-0 font-sm" style="color: #7E7E7E;">
                                                Status: <strong style="color: #253D4E;">{{ strtoupper($order->payment_status) }}</strong>
                                            </p>
                                            <p class="mt-10 font-sm text-muted">Pesanan akan otomatis diproses setelah pembayaran berhasil diverifikasi oleh sistem.</p>
                                        </div>
                                    </div>
                                @elseif($order->payment_method === 'xendit' || $order->payment_method === 'faspay')
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
                        @endif

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
