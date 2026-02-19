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

<div class="container mb-80 mt-50">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg text-center p-30">
                <div class="card-body">
                    <div class="mb-30">
                        <i class="fi-rs-check-circle text-brand" style="font-size: 80px;"></i>
                    </div>
                    <h2 class="heading-2 mb-20">Pesanan Berhasil Dibuat!</h2>
                    <p class="text-muted font-lg mb-30">Terima kasih atas pembelian Anda. Pesanan Anda sedang diproses dan akan segera dikirim.</p>
                    
                    <div class="invoice-header mb-30 bg-light p-30 rounded-3">
                        <div class="row">
                            <div class="col-md-6 text-start">
                                <span class="font-md text-muted d-block mb-5">Nomor Pesanan</span>
                                <h4 class="text-brand">{{ $order->order_number }}</h4>
                            </div>
                            <div class="col-md-6 text-md-end text-start mt-md-0 mt-3">
                                <span class="font-md text-muted d-block mb-5">Total Pembayaran</span>
                                <h4 class="text-brand">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row text-start mb-40">
                        <!-- Shipping Address -->
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-radius-10">
                                <div class="card-header bg-transparent border-0 pb-0">
                                    <h5 class="mb-0"><i class="fi-rs-marker mr-10 text-muted"></i>Alamat Pengiriman</h5>
                                </div>
                                <div class="card-body">
                                    @if($order->address)
                                        <strong class="d-block mb-5">{{ $order->address->recipient_name }}</strong>
                                        <p class="text-muted font-sm mb-0">
                                            {{ $order->address->phone }}<br>
                                            {{ $order->address->address_detail }}<br>
                                            {{ $order->address->village?->name }}, Kec. {{ $order->address->district?->name }}<br>
                                            {{ $order->address->regency?->name }}, {{ $order->address->province?->name }}
                                            @if($order->address->postal_code) {{ $order->address->postal_code }} @endif
                                        </p>
                                    @else
                                        <p class="mb-0 font-sm text-muted">{!! nl2br(e($order->shipping_address)) !!}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Shipping Method -->
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-radius-10">
                                <div class="card-header bg-transparent border-0 pb-0">
                                    <h5 class="mb-0"><i class="fi-rs-truck-side mr-10 text-muted"></i>Pengiriman</h5>
                                </div>
                                <div class="card-body">
                                    @if($order->expedition)
                                        <strong class="d-block mb-5">{{ $order->expedition->name }}</strong>
                                        <p class="text-muted font-sm mb-0">
                                            Layanan: {{ $order->expedition_service }}<br>
                                            Estimasi: {{ $order->expedition->estimated_delivery }}
                                        </p>
                                    @else
                                        <p class="mb-0 text-muted">-</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                         <!-- Source Warehouse -->
                        @if($order->sourceWarehouse)
                        <div class="col-12 mt-3">
                            <div class="card border-radius-10 bg-success-light">
                                <div class="card-body d-flex align-items-center">
                                    <i class="fi-rs-building text-brand font-xxl mr-20"></i>
                                    <div>
                                        <h6 class="mb-5">Dikirim dari Hub {{ $order->sourceWarehouse->name }}</h6>
                                        <p class="font-sm text-muted mb-0">{{ $order->sourceWarehouse->full_location }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Payment Information -->
                     <div class="text-start mb-40">
                         <h4 class="mb-20">Instruksi Pembayaran</h4>
                        @if($order->payment_method === 'manual_transfer')
                            <div class="alert alert-info border-radius-10">
                                <h6 class="mb-15"><i class="fi-rs-info mr-10"></i>Silakan transfer ke salah satu rekening berikut:</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="bg-white p-15 rounded text-center">
                                            <strong class="d-block text-brand">BCA</strong>
                                            <span class="d-block font-lg fw-bold">1234567890</span>
                                            <small class="text-muted">a.n PT Rasa Group</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="bg-white p-15 rounded text-center">
                                            <strong class="d-block text-brand">Mandiri</strong>
                                            <span class="d-block font-lg fw-bold">0987654321</span>
                                            <small class="text-muted">a.n PT Rasa Group</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="bg-white p-15 rounded text-center">
                                            <strong class="d-block text-brand">BNI</strong>
                                            <span class="d-block font-lg fw-bold">5678901234</span>
                                            <small class="text-muted">a.n PT Rasa Group</small>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-15 mb-0 text-center text-muted font-sm">Mohon lakukan pembayaran sebelum <strong>{{ $order->created_at->addDay()->format('d M Y, H:i') }}</strong></p>
                            </div>
                        @elseif($order->payment_method === 'xendit')
                            @if($order->xendit_invoice_url)
                                <div class="alert alert-warning border-radius-10 text-center">
                                    <h6 class="mb-15"><i class="fi-rs-credit-card mr-10"></i>Selesaikan Pembayaran Anda</h6>
                                    <p class="mb-20">Silakan klik tombol di bawah untuk melanjutkan pembayaran via Xendit.</p>
                                    <a href="{{ $order->xendit_invoice_url }}" class="btn btn-primary" target="_blank">Bayar Sekarang</a>
                                </div>
                            @else
                                <div class="alert alert-success border-radius-10">
                                    <h6 class="mb-0"><i class="fi-rs-check mr-10"></i>Pembayaran Online (Xendit)</h6>
                                    <p class="mb-0 mt-2">Link pembayaran telah dikirim ke email/WhatsApp Anda.</p>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-success border-radius-10">
                                <h6 class="mb-5"><i class="fi-rs-check mr-10"></i>COD (Bayar di Tempat)</h6>
                                <p class="mb-0">Siapkan uang tunai pas saat kurir mengantar pesanan Anda.</p>
                            </div>
                        @endif
                     </div>

                    <div class="mt-40">
                        <a href="{{ route('buyer.orders.show', $order) }}" class="btn btn-outline-primary btn-lg mr-10 mb-2">
                            <i class="fi-rs-file-text mr-10"></i>Detail Pesanan
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg mb-2">
                            <i class="fi-rs-shopping-bag mr-10"></i>Lanjut Belanja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
