@extends('layouts.shop')

@section('title', 'Hub & Distributor - ' . config('app.name'))

@push('styles')
<style>
    .hub-hero {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        padding: 60px 0;
        color: white;
        margin-bottom: 30px;
    }
    .hub-hero h1 {
        font-weight: 700;
        margin-bottom: 15px;
    }
    .hub-hero p {
        font-size: 1.1rem;
        opacity: 0.9;
    }
    .filter-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        padding: 25px;
        margin-bottom: 30px;
    }
    .hub-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        overflow: hidden;
        margin-bottom: 25px;
    }
    .hub-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.12);
    }
    .hub-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        position: relative;
    }
    .hub-card-header h5 {
        font-weight: 600;
        margin: 0;
        font-size: 1.1rem;
    }
    .hub-card-header .hub-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255,255,255,0.2);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
    }
    .hub-card-body {
        padding: 20px;
    }
    .hub-location {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
        color: #6c757d;
    }
    .hub-location i {
        margin-right: 10px;
        margin-top: 3px;
        color: #667eea;
    }
    .hub-stats {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }
    .hub-stat {
        flex: 1;
        background: #f8f9fa;
        padding: 12px;
        border-radius: 10px;
        text-align: center;
    }
    .hub-stat-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: #667eea;
    }
    .hub-stat-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
    }
    .hub-phone {
        display: flex;
        align-items: center;
        color: #6c757d;
        font-size: 0.9rem;
    }
    .hub-phone i {
        margin-right: 8px;
        color: #28a745;
    }
    .btn-hub-detail {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }
    .distance-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .distance-same-city {
        background: #d4edda;
        color: #155724;
    }
    .distance-same-province {
        background: #fff3cd;
        color: #856404;
    }
    .distance-other {
        background: #f8d7da;
        color: #721c24;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hub-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1><i class="bi bi-building me-2"></i> Hub & Distributor</h1>
                <p>Temukan Hub atau Distributor terdekat dengan lokasi Anda untuk pengiriman yang lebih cepat dan efisien.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <span class="badge bg-light text-dark fs-6 px-3 py-2">
                    <i class="bi bi-geo-alt me-1"></i> {{ $warehouses->count() }} Lokasi Tersedia
                </span>
            </div>
        </div>
    </div>
</section>

<div class="container mb-5">
    <!-- Filter Section -->
    <div class="filter-card">
        <form method="GET" action="{{ route('hubs.index') }}" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-geo me-1"></i> Provinsi
                    </label>
                    <select name="province_id" id="province_id" class="form-select">
                        <option value="">Semua Provinsi</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province->id }}" {{ request('province_id') == $province->id ? 'selected' : '' }}>
                                {{ $province->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-pin-map me-1"></i> Kabupaten/Kota
                    </label>
                    <select name="regency_id" id="regency_id" class="form-select">
                        <option value="">Semua Kabupaten/Kota</option>
                        @foreach($regencies as $regency)
                            <option value="{{ $regency->id }}" {{ request('regency_id') == $regency->id ? 'selected' : '' }}>
                                {{ $regency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search me-1"></i> Cari
                    </label>
                    <input type="text" name="search" class="form-control" placeholder="Nama hub..." value="{{ request('search') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Hub List -->
    @if($warehouses->count() > 0)
        <div class="row">
            @foreach($warehouses as $warehouse)
                <div class="col-lg-4 col-md-6">
                    <div class="hub-card">
                        <div class="hub-card-header">
                            <h5><i class="bi bi-building me-2"></i> {{ $warehouse->name }}</h5>
                            <span class="hub-badge">
                                <i class="bi bi-check-circle me-1"></i> Aktif
                            </span>
                        </div>
                        <div class="hub-card-body">
                            <div class="hub-location">
                                <i class="bi bi-geo-alt-fill"></i>
                                <div>
                                    @if($warehouse->address)
                                        {{ $warehouse->address }}<br>
                                    @endif
                                    <strong>{{ $warehouse->full_location }}</strong>
                                </div>
                            </div>
                            
                            <div class="hub-stats">
                                <div class="hub-stat">
                                    <div class="hub-stat-value">{{ $warehouse->products_count ?? 0 }}</div>
                                    <div class="hub-stat-label">Produk</div>
                                </div>
                                <div class="hub-stat">
                                    <div class="hub-stat-value">{{ number_format($warehouse->stocks_sum_stock ?? 0) }}</div>
                                    <div class="hub-stat-label">Total Stock</div>
                                </div>
                            </div>
                            
                            @if($warehouse->phone)
                                <div class="hub-phone mb-3">
                                    <i class="bi bi-telephone"></i>
                                    {{ $warehouse->phone }}
                                </div>
                            @endif
                            
                            <a href="{{ route('hubs.show', $warehouse) }}" class="btn btn-primary btn-hub-detail">
                                <i class="bi bi-box-seam me-2"></i> Lihat Produk & Stock
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-building"></i>
            <h4>Tidak Ada Hub Ditemukan</h4>
            <p class="text-muted">Coba ubah filter pencarian Anda atau lihat semua hub.</p>
            <a href="{{ route('hubs.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-clockwise me-1"></i> Reset Filter
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#province_id').change(function() {
        var provinceId = $(this).val();
        var regencySelect = $('#regency_id');
        
        regencySelect.html('<option value="">Memuat...</option>');
        
        if (provinceId) {
            $.get("{{ route('hubs.get-regencies') }}", { province_id: provinceId }, function(data) {
                var options = '<option value="">Semua Kabupaten/Kota</option>';
                $.each(data, function(index, regency) {
                    options += '<option value="' + regency.id + '">' + regency.name + '</option>';
                });
                regencySelect.html(options);
            });
        } else {
            regencySelect.html('<option value="">Semua Kabupaten/Kota</option>');
        }
    });
});
</script>
@endpush

