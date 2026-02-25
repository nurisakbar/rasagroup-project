@extends('layouts.shop')

@section('title', 'Pemesanan Berhasil')

@section('content')
<div class="container mb-80 mt-50">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="p-5 border-radius-20 bg-white shadow-sm border">
                <div class="mb-4">
                    <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-check-big.svg') }}" alt="Success" style="width: 100px;">
                </div>
                <h2 class="mb-2">Terima Kasih!</h2>
                <h4 class="text-brand mb-4">Pesanan Anda Berhasil Dibuat</h4>
                <p class="text-muted mb-5 px-lg-5">
                    Pesanan #<strong>{{ $order->order_number }}</strong> telah kami terima. Silakan lakukan pembayaran agar pesanan dapat segera diproses oleh Hub pengirim.
                </p>

                <div class="bg-light border-radius-15 p-4 mb-5 text-start">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted font-sm">Total Pembayaran</span>
                        <h4 class="text-brand mb-0">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h4>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted font-sm">Metode Pembayaran</span>
                        <span class="font-sm fw-bold">Transfer Bank Manual</span>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('distributor.orders.history') }}" class="btn btn-brand rounded-pill">Lihat Riwayat Pesanan</a>
                    <a href="{{ route('home') }}" class="btn btn-secondary rounded-pill">Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
