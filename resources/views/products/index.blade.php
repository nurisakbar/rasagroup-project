@extends('layouts.shop')

@section('title', 'Katalog Produk')

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="section-title mb-5">
        <h2>Katalog Produk</h2>
        <div class="divider"></div>
        <p>Temukan berbagai varian sirup dengan rasa yang nikmat dan menyegarkan</p>
    </div>
    
    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-5" style="border-radius: 15px;">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('products.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold"><i class="bi bi-search me-1"></i> Cari Produk</label>
                        <input type="text" name="search" class="form-control form-control-lg" placeholder="Ketik nama produk..." value="{{ request('search') }}" style="border-radius: 10px;">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Harga Min</label>
                        <input type="number" name="min_price" class="form-control" placeholder="Rp 0" value="{{ request('min_price') }}" style="border-radius: 10px;">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Harga Max</label>
                        <input type="number" name="max_price" class="form-control" placeholder="Rp 100.000" value="{{ request('max_price') }}" style="border-radius: 10px;">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Urutkan</label>
                        <select name="sort" class="form-select" style="border-radius: 10px;">
                            <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nama A-Z</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn w-100" style="background: var(--gradient-primary); color: white; border-radius: 10px; padding: 10px;">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row g-4">
        @forelse($products as $product)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card product-card h-100">
                    <div class="card-img-wrapper">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}">
                        @else
                            <img src="https://via.placeholder.com/300x220/e74c3c/fff?text={{ urlencode($product->name) }}" class="card-img-top" alt="{{ $product->name }}">
                        @endif
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="text-muted mb-2" style="font-size: 0.9rem;">{{ Str::limit($product->description, 60) }}</p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            <small class="text-muted"><i class="bi bi-box-seam"></i> {{ $product->formatted_weight }}</small>
                        </div>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-add-cart mt-auto">
                            <i class="bi bi-eye me-1"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center py-5" style="border-radius: 15px;">
                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                    <h4>Tidak ada produk ditemukan</h4>
                    <p class="text-muted mb-3">Coba ubah filter pencarian Anda</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">Reset Filter</a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-5">
        {{ $products->links() }}
    </div>
</div>
@endsection

@push('styles')
<style>
    .section-title {
        text-align: center;
    }
    
    .section-title h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--dark-color);
    }
    
    .section-title .divider {
        width: 80px;
        height: 4px;
        background: var(--gradient-primary);
        margin: 15px auto;
        border-radius: 2px;
    }
    
    .section-title p {
        color: #7f8c8d;
        font-size: 1.1rem;
    }
</style>
@endpush

