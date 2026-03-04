@extends('layouts.shop')

@section('title', 'Pesanan Saya')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Pesanan Saya
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
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h3 class="mb-0">Riwayat Pesanan</h3>
                                <div class="text-muted font-sm">
                                    Total {{ $orders->total() }} pesanan ditemukan
                                </div>
                            </div>

                            <div class="orders-list">
                                @forelse($orders as $order)
                                    @php
                                        $statusClass = match($order->order_status) {
                                            'pending' => 'bg-warning',
                                            'processing' => 'bg-info',
                                            'shipped' => 'bg-primary',
                                            'delivered' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                        $statusLabel = match($order->order_status) {
                                            'pending' => 'Menunggu Pembayaran',
                                            'processing' => 'Sedang Diproses',
                                            'shipped' => 'Dalam Pengiriman',
                                            'delivered' => 'Selesai',
                                            'cancelled' => 'Dibatalkan',
                                            default => ucfirst($order->order_status),
                                        };
                                        
                                        // Get the first item to show in preview
                                        $firstItem = $order->items->first();
                                        $remainingCount = $order->items->count() - 1;
                                    @endphp
                                    <div class="order-card mb-20">
                                        <div class="order-header p-20 d-flex justify-content-between align-items-center bg-light-gray">
                                            <div class="order-meta d-flex align-items-center">
                                                <div class="mr-30">
                                                    <span class="d-block text-muted font-xs mb-1">NO. PESANAN</span>
                                                    <span class="fw-bold text-dark font-sm">#{{ $order->order_number }}</span>
                                                </div>
                                                <div class="mr-30">
                                                    <span class="d-block text-muted font-xs mb-1">TANGGAL</span>
                                                    <span class="fw-bold text-dark font-sm">{{ $order->created_at->format('d M Y') }}</span>
                                                </div>
                                                <div>
                                                    <span class="d-block text-muted font-xs mb-1">TOTAL</span>
                                                    <span class="fw-bold text-brand font-sm">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                            <div class="order-status">
                                                <span class="badge rounded-pill {{ $statusClass }} py-2 px-3 text-white font-xs">{{ $statusLabel }}</span>
                                            </div>
                                        </div>
                                        <div class="order-body p-20">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    @if($firstItem)
                                                        <div class="d-flex align-items-center">
                                                            <div class="product-thumb mr-20">
                                                                <img src="{{ $firstItem->product->image_url ?: asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" 
                                                                     alt="{{ $firstItem->product->display_name }}"
                                                                     class="rounded" style="width: 70px; height: 70px; object-fit: cover; border: 1px solid #eee;">
                                                            </div>
                                                            <div class="product-info">
                                                                <h6 class="mb-1 text-dark truncate-1">{{ $firstItem->product->display_name }}</h6>
                                                                <p class="font-xs text-muted mb-0">{{ $firstItem->quantity }} item x Rp {{ number_format($firstItem->price, 0, ',', '.') }}</p>
                                                                @if($remainingCount > 0)
                                                                    <p class="font-xs text-brand mt-1 fw-bold">+{{ $remainingCount }} produk lainnya</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                                    <div class="d-grid d-md-block gap-2">
                                                        <a href="{{ route('buyer.orders.show', $order) }}" class="btn btn-sm btn-outline-brand rounded-pill py-2 px-4 font-sm">
                                                            <i class="fi-rs-eye mr-5"></i> Lihat Detail
                                                        </a>
                                                        @if($order->order_status === 'shipped' && $order->tracking_number)
                                                            <a href="{{ route('buyer.orders.show', $order) }}#btn-track-order" class="btn btn-sm btn-brand rounded-pill py-2 px-4 font-sm ms-md-2">
                                                                <i class="fi-rs-truck mr-5"></i> Lacak
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-state text-center py-5 border rounded-10 bg-white">
                                        <div class="mb-4">
                                            <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-cart.svg') }}" alt="Empty" style="width: 100px; opacity: 0.3;">
                                        </div>
                                        <h4 class="mb-2">Belum Ada Pesanan</h4>
                                        <p class="text-muted mb-4 px-5">Mulai perjalanan belanja Anda dan temukan berbagai produk berkualitas kami.</p>
                                        <a href="{{ route('products.index') }}" class="btn btn-brand rounded-pill px-5">
                                            <i class="fi-rs-shopping-bag mr-10"></i>Mulai Belanja
                                        </a>
                                    </div>
                                @endforelse
                            </div>
                            
                            <div class="pagination-area mt-40">
                                {{ $orders->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-gray { background-color: #f8fafc; }
    .border-radius-10 { border-radius: 10px; }
    .order-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #edf2f7;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .order-card:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        border-color: #3bb77e33;
        transform: translateY(-2px);
    }
    .order-header {
        border-bottom: 1px solid #edf2f7;
    }
    .product-info h6 {
        font-size: 15px;
        font-weight: 600;
        color: #253D4E;
    }
    .btn-outline-brand {
        color: #3BB77E;
        border: 1px solid #3BB77E;
        background: transparent;
    }
    .btn-outline-brand:hover {
        background: #3BB77E;
        color: #fff;
    }
    .truncate-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .ms-md-2 { margin-left: 0.5rem; }
    @media (max-width: 768px) {
        .order-meta { flex-wrap: wrap; }
        .order-meta > div { margin-right: 20px !important; margin-bottom: 10px; }
        .order-header { flex-direction: column; align-items: flex-start !important; }
        .order-status { margin-top: 15px; }
        .ms-md-2 { margin-left: 0; margin-top: 10px; }
    }
</style>
@endsection

