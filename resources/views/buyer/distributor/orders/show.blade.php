@extends('layouts.shop')

@section('title', 'Detail Order Produk')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('distributor.orders.history') }}">Riwayat Order</a>
            <span></span> Detail
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
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="card border-0 shadow-sm border-radius-10 overflow-hidden">
                                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0">Order #{{ $order->order_number }}</h3>
                                        <p class="text-muted font-sm mb-0">Dibuat pada {{ $order->created_at->format('d M Y H:i') }}</p>
                                    </div>
                                    <div>
                                        @php
                                            $statusClass = [
                                                'pending' => 'bg-warning',
                                                'processing' => 'bg-info',
                                                'shipped' => 'bg-primary',
                                                'delivered' => 'bg-success',
                                                'completed' => 'bg-success',
                                                'cancelled' => 'bg-danger',
                                            ][$order->order_status] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $statusClass }} fs-6 px-3 py-2">{{ strtoupper($order->order_status) }}</span>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-4 order-info-cards mb-4">
                                        <div class="col-md-4">
                                            <div class="p-3 bg-light border-radius-10 h-100">
                                                <h6 class="font-sm mb-2 text-muted uppercase">Pengiriman Ke</h6>
                                                <p class="font-sm mb-0">
                                                    <strong>{{ Auth::user()->name }}</strong><br>
                                                    {!! nl2br(e($order->shipping_address)) !!}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-light border-radius-10 h-100">
                                                <h6 class="font-sm mb-2 text-muted uppercase">Ekspedisi</h6>
                                                <p class="font-sm mb-0">
                                                    <strong>{{ $order->expedition->name ?? '-' }}</strong><br>
                                                    Layanan: {{ $order->expedition_service ?? '-' }}<br>
                                                    Resi: <span class="text-brand fw-bold">{{ $order->tracking_number ?? 'Belum ada' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-light border-radius-10 h-100">
                                                <h6 class="font-sm mb-2 text-muted uppercase">Pembayaran</h6>
                                                <p class="font-sm mb-0">
                                                    Metode: {{ ucfirst($order->payment_method) }}<br>
                                                    Status: <span class="fw-bold {{ $order->payment_status === 'paid' ? 'text-success' : 'text-warning' }}">{{ strtoupper($order->payment_status) }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mb-3">Item Pesanan</h5>
                                    <div class="table-responsive">
                                        <table class="table font-sm">
                                            <thead>
                                                <tr class="main-heading">
                                                    <th>Produk</th>
                                                    <th>Harga</th>
                                                    <th class="text-center">Jumlah</th>
                                                    <th class="text-end">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($order->items as $item)
                                                    <tr>
                                                        <td class="image product-thumbnail pl-0" width="60">
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ asset($item->product->image_url ?? '') }}" class="border-radius-10 me-3" style="width: 50px;">
                                                                <div>
                                                                    <h6 class="fs-tiny mb-0">{{ $item->product->display_name ?? 'Produk Dihapus' }}</h6>
                                                                    <p class="font-xs text-muted mb-0">{{ $item->product->code ?? '-' }}</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                                        <td class="align-middle text-center">{{ $item->quantity }}</td>
                                                        <td class="align-middle text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="fw-bold">
                                                <tr>
                                                    <td colspan="3" class="text-end border-0">Subtotal</td>
                                                    <td class="text-end border-0">Rp {{ number_format($order->subtotal ?? $order->total_amount - ($order->shipping_cost ?? 0), 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end border-0">Ongkos Kirim</td>
                                                    <td class="text-end border-0">Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr class="text-brand fs-5">
                                                    <td colspan="3" class="text-end">Total</td>
                                                    <td class="text-end">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    @if(in_array($order->order_status, ['delivered', 'completed']))
                                        <div class="alert alert-info border-radius-15 p-4 mt-4 d-flex align-items-center">
                                            <div class="me-4 fs-1"><i class="fi-rs-box"></i></div>
                                            <div>
                                                <h5 class="mb-1 text-info">Pesanan Tiba!</h5>
                                                <p class="font-sm mb-3">Pesanan Anda telah sampai. Jangan lupa untuk memasukkan item pesanan ini ke dalam stok warehouse Anda.</p>
                                                <form action="{{ route('distributor.orders.convert-to-stock', $order) }}" method="POST" onsubmit="return confirm('Konversi item pesanan ini ke stok warehouse?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-brand rounded-pill px-4">Masukkan ke Stock</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer bg-white border-top p-4 d-flex justify-content-between align-items-center">
                                    <a href="{{ route('distributor.orders.history') }}" class="btn btn-sm btn-secondary rounded-pill px-4">Kembali</a>
                                    @if($order->payment_status === 'pending')
                                        <a href="#" class="btn btn-sm btn-fill-out rounded-pill px-4">Konfirmasi Pembayaran</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
