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

<div class="page-content pt-100 pb-100" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm p-40 border-radius-30" style="background-color: #ffffff;">
                    <div class="card-body text-center">
                        <div class="mb-30">
                            <i class="fi-rs-check-circle" style="font-size: 80px; color: #6A1B1B;"></i>
                        </div>
                        <h2 class="mb-20" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Pesanan Berhasil Dibuat!</h2>
                        <p class="font-lg mb-40" style="font-family: 'Lato', sans-serif; color: #7E7E7E;">Terima kasih atas pembelian Anda. Pesanan Anda sedang kami proses dengan cinta.</p>
                        
                        <div class="mb-40 p-30 border-radius-20" style="background-color: #F8F9FA; border: 1.5px dashed #ECECEC;">
                            <div class="row align-items-center">
                                <div class="col-md-6 text-md-start text-center mb-md-0 mb-3">
                                    <span class="font-md d-block mb-1" style="color: #7E7E7E;">Nomor Pesanan</span>
                                    <h4 style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B; margin: 0;">{{ $order->order_number }}</h4>
                                </div>
                                <div class="col-md-6 text-md-end text-center">
                                    <span class="font-md d-block mb-1" style="color: #7E7E7E;">Total Pembayaran</span>
                                    <h4 style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B; margin: 0;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h4>
                                </div>
                            </div>
                        </div>

                        <div class="row text-start mb-40 g-4">
                            <!-- Shipping Address -->
                            <div class="col-md-6">
                                <div class="p-25 h-100 border-radius-20" style="background-color: #ffffff; border: 1.5px solid #ECECEC;">
                                    <h5 class="mb-15" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;"><i class="fi-rs-marker mr-10 text-muted"></i>Alamat Kirim</h5>
                                    @if($order->address)
                                        <strong class="d-block mb-2" style="color: #253D4E;">{{ $order->address->recipient_name }}</strong>
                                        <p style="font-family: 'Lato', sans-serif; color: #7E7E7E; font-size: 14px; line-height: 1.6; margin: 0;">
                                            {{ $order->address->phone }}<br>
                                            {{ $order->address->address_detail }}<br>
                                            {{ $order->address->village?->name }}, Kec. {{ $order->address->district?->name }}<br>
                                            {{ $order->address->regency?->name }}, {{ $order->address->province?->name }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Shipping Method -->
                            <div class="col-md-6">
                                <div class="p-25 h-100 border-radius-20" style="background-color: #ffffff; border: 1.5px solid #ECECEC;">
                                    <h5 class="mb-15" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;"><i class="fi-rs-truck-side mr-10 text-muted"></i>Pengiriman</h5>
                                    @if($order->expedition)
                                        <strong class="d-block mb-2" style="color: #253D4E;">{{ $order->expedition->name }}</strong>
                                        <p style="font-family: 'Lato', sans-serif; color: #7E7E7E; font-size: 14px; line-height: 1.6; margin: 0;">
                                            Layanan: {{ $order->expedition_service }}<br>
                                            Estimasi: {{ $order->expedition->estimated_delivery }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="text-start mb-50">
                            <h4 class="mb-20" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Instruksi Pembayaran</h4>
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
                                @if($order->payment_status === 'paid')
                                    <div class="p-30 border-radius-20 text-center" style="background-color: #e8f5e9; border: 1px solid #c8e6c9;">
                                        <h6 class="mb-10" style="color: #2e7d32;"><i class="fi-rs-check-circle mr-10"></i>Pembayaran Berhasil!</h6>
                                        <p class="mb-0" style="color: #2e7d32;">Terima kasih, pembayaran Anda telah kami terima secara otomatis.</p>
                                    </div>
                                @elseif($order->xendit_invoice_url)
                                    <div class="p-30 border-radius-20 text-center" style="background-color: rgba(106, 27, 27, 0.03); border: 1px solid rgba(106, 27, 27, 0.1);">
                                        <h6 class="mb-15" style="color: #6A1B1B;"><i class="fi-rs-credit-card mr-10"></i>Selesaikan Pembayaran</h6>
                                        <p class="mb-25">Silakan klik tombol di bawah untuk membayar melalui Xendit.</p>
                                        <a href="{{ $order->xendit_invoice_url }}" class="btn btn-heading" style="background-color: #6A1B1B; color: #ffffff; border-radius: 12px; padding: 15px 35px; font-weight: 700;" target="_blank">Bayar Sekarang</a>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="{{ route('buyer.orders.show', $order) }}" class="btn btn-outline-heading" style="border: 2px solid #6A1B1B; color: #6A1B1B; background: transparent; border-radius: 12px; padding: 18px 35px; font-weight: 700; min-width: 200px;">
                                <i class="fi-rs-file-text mr-10"></i>Detail Pesanan
                            </a>
                            <a href="{{ route('products.index') }}" class="btn btn-heading" style="background-color: #1a1a1a; color: #ffffff; border-radius: 12px; padding: 18px 35px; font-weight: 700; min-width: 200px; border: none;">
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
