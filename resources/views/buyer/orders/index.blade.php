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
                                        <a href="{{ route('buyer.orders.show', $order) }}" class="stretched-link order-card-stretched-link" aria-label="Lihat detail pesanan #{{ $order->order_number }}"></a>
                                        <div class="order-header p-25 d-flex justify-content-between align-items-center">
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
                                                    <span class="fw-bold text-maroon font-sm">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                            <div class="order-status">
                                                <span class="badge rounded-pill {{ $statusClass }} py-2 px-3 text-white font-xs">{{ $statusLabel }}</span>
                                            </div>
                                        </div>
                                        <div class="order-body p-25">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    @if($firstItem)
                                                        <div class="d-flex align-items-center">
                                                            <div class="product-thumb mr-20">
                                                                <img src="{{ $firstItem->product->image_url ?: asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" 
                                                                     alt="{{ $firstItem->product->display_name }}"
                                                                     class="rounded-12" style="width: 75px; height: 75px; object-fit: cover; border: 1.5px solid #f1f1f1;">
                                                            </div>
                                                            <div class="product-info">
                                                                <h6 class="mb-1 text-dark truncate-1">{{ $firstItem->product->display_name }}</h6>
                                                                <p class="font-sm text-muted mb-0">{{ $firstItem->quantity }} item x Rp {{ number_format($firstItem->price, 0, ',', '.') }}</p>
                                                                @if($remainingCount > 0)
                                                                    <p class="font-xs text-maroon mt-1 fw-bold">+{{ $remainingCount }} produk lainnya</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                                    <div class="d-grid d-md-block gap-2">
                                                        <a href="{{ route('buyer.orders.show', $order) }}" class="btn btn-sm btn-outline rounded-pill">
                                                            <i class="fi-rs-eye mr-5"></i> Lihat Detail
                                                        </a>
                                                        @if($order->order_status === 'shipped' && $order->tracking_number)
                                                            <a href="{{ route('buyer.orders.show', $order) }}#btn-track-order" class="btn btn-sm rounded-pill ms-md-2">
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
                                        <a href="{{ route('products.index') }}" class="btn rounded-pill">
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
    .bg-light-maroon { background-color: rgba(106, 27, 27, 0.03); }
    .order-card {
        background: #fff;
        position: relative;
        border-radius: 20px;
        border: 1.5px solid #edf2f7;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.02);
    }
    .order-card:hover {
        box-shadow: 0 10px 30px rgba(106, 27, 27, 0.08);
        border-color: #6A1B1B88;
        transform: translateY(-3px);
    }
    .order-header {
        border-bottom: 1.5px solid #edf2f7;
        background-color: #F8F9FA !important;
        position: relative;
        z-index: 2;
    }
    .order-body {
        position: relative;
        z-index: 2;
    }
    .order-card-stretched-link {
        z-index: 1;
    }
    
    .text-maroon { color: #6A1B1B !important; }

    .product-info h6 {
        font-size: 16px;
        font-weight: 600;
        color: #253D4E;
    }
    
    .truncate-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection

