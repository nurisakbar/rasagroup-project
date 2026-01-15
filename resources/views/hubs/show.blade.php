@extends('layouts.shop')

@section('title', $warehouse->name . ' - ' . config('app.name'))

@push('styles')
<style>
    .hub-detail-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 60px 0;
        color: white;
        margin-bottom: 30px;
    }
    .hub-detail-hero h1 {
        font-weight: 700;
        margin-bottom: 10px;
    }
    .hub-info-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        padding: 25px;
        margin-bottom: 30px;
    }
    .hub-info-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    .hub-info-item i {
        width: 40px;
        height: 40px;
        background: #f0f4ff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
        margin-right: 15px;
        flex-shrink: 0;
    }
    .hub-info-item .info-content h6 {
        margin: 0;
        font-weight: 600;
        color: #333;
    }
    .hub-info-item .info-content p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }
    .product-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        overflow: hidden;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }
    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .product-img {
        height: 180px;
        object-fit: cover;
        width: 100%;
    }
    .product-img-placeholder {
        height: 180px;
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
    }
    .product-body {
        padding: 15px;
    }
    .product-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
        font-size: 0.95rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-brand {
        font-size: 0.8rem;
        color: #667eea;
        margin-bottom: 10px;
    }
    .product-price {
        font-weight: 700;
        color: #28a745;
        font-size: 1.1rem;
    }
    .stock-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .stock-available {
        background: #d4edda;
        color: #155724;
    }
    .stock-low {
        background: #fff3cd;
        color: #856404;
    }
    .stock-empty {
        background: #f8d7da;
        color: #721c24;
    }
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #667eea;
    }
    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }
    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hub-detail-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3" style="background: transparent;">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hubs.index') }}" class="text-white-50">Hub & Distributor</a></li>
                <li class="breadcrumb-item active text-white">{{ $warehouse->name }}</li>
            </ol>
        </nav>
        <h1><i class="bi bi-building me-2"></i> {{ $warehouse->name }}</h1>
        <p class="mb-0 opacity-75">
            <i class="bi bi-geo-alt me-1"></i> {{ $warehouse->full_location }}
        </p>
    </div>
</section>

<div class="container mb-5">
    <div class="row">
        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="hub-info-card">
                <h5 class="mb-4 fw-bold"><i class="bi bi-info-circle me-2"></i> Informasi Hub</h5>
                
                @if($warehouse->address)
                    <div class="hub-info-item">
                        <i class="bi bi-geo-alt"></i>
                        <div class="info-content">
                            <h6>Alamat</h6>
                            <p>{{ $warehouse->address }}</p>
                        </div>
                    </div>
                @endif
                
                <div class="hub-info-item">
                    <i class="bi bi-pin-map"></i>
                    <div class="info-content">
                        <h6>Lokasi</h6>
                        <p>{{ $warehouse->full_location }}</p>
                    </div>
                </div>
                
                @if($warehouse->phone)
                    <div class="hub-info-item">
                        <i class="bi bi-telephone"></i>
                        <div class="info-content">
                            <h6>Telepon</h6>
                            <p>{{ $warehouse->phone }}</p>
                        </div>
                    </div>
                @endif
                
                @if($warehouse->description)
                    <div class="hub-info-item">
                        <i class="bi bi-card-text"></i>
                        <div class="info-content">
                            <h6>Deskripsi</h6>
                            <p>{{ $warehouse->description }}</p>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="stat-card">
                        <div class="stat-value">{{ $productsWithStock->count() }}</div>
                        <div class="stat-label">Produk Tersedia</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format($productsWithStock->sum(function($stock) { return $stock->stock ?? 0; })) }}</div>
                        <div class="stat-label">Total Stock</div>
                    </div>
                </div>
            </div>
            
            <a href="{{ route('hubs.index') }}" class="btn btn-outline-primary w-100">
                <i class="bi bi-arrow-left me-2"></i> Kembali ke Daftar Hub
            </a>
        </div>
        
        <!-- Products List -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-box-seam me-2"></i> Produk Tersedia
                </h4>
                <span class="badge bg-primary px-3 py-2">{{ $productsWithStock->count() }} Produk</span>
            </div>
            
            @if($productsWithStock->count() > 0)
                <div class="row">
                    @foreach($productsWithStock as $stock)
                        @php 
                            $product = $stock->product ?? null;
                        @endphp
                        @if($product && isset($product->name) && isset($product->price))
                        <div class="col-md-6 col-lg-4">
                            <div class="product-card">
                                @if(!empty($product->image))
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-img">
                                @else
                                    <div class="product-img-placeholder">
                                        <i class="bi bi-image fs-1"></i>
                                    </div>
                                @endif
                                <div class="product-body">
                                    @if($product->brand && isset($product->brand->name))
                                        <div class="product-brand">
                                            <i class="bi bi-bookmark me-1"></i> {{ $product->brand->name }}
                                        </div>
                                    @endif
                                    <h6 class="product-name">{{ $product->name }}</h6>
                                    @if(!empty($product->code))
                                        <small class="text-muted">{{ $product->code }}</small>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span class="product-price">
                                            Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}
                                        </span>
                                        @if($stock->stock > 10)
                                            <span class="stock-badge stock-available">
                                                <i class="bi bi-check-circle me-1"></i> {{ $stock->stock }}
                                            </span>
                                        @elseif($stock->stock > 0)
                                            <span class="stock-badge stock-low">
                                                <i class="bi bi-exclamation-circle me-1"></i> {{ $stock->stock }}
                                            </span>
                                        @else
                                            <span class="stock-badge stock-empty">
                                                <i class="bi bi-x-circle me-1"></i> Habis
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('products.show', ['product' => $product, 'warehouse_id' => $warehouse->id]) }}" class="btn btn-sm btn-outline-primary w-100 mt-3">
                                        <i class="bi bi-eye me-1"></i> Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-box-seam display-1 text-muted"></i>
                    <h5 class="mt-3">Belum Ada Produk</h5>
                    <p class="text-muted">Hub ini belum memiliki produk dengan stock tersedia.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

