@extends('layouts.shop')

@section('title', 'Hub & Distributor - ' . config('app.name'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-color: #E63946;
        --primary-dark: #C1121F;
        --secondary-color: #F77F00;
        --accent-color: #FCBF49;
        --dark-color: #1D3557;
        --light-bg: #F8F9FA;
        --white: #FFFFFF;
        --text-dark: #2B2D42;
        --text-light: #6C757D;
        --border-color: #E0E0E0;
        --success-color: #06A77D;
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, var(--dark-color) 0%, var(--primary-dark) 100%);
        padding: 6rem 0 4rem;
        color: white;
        margin-top: 0;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
    }

    .page-header-content {
        position: relative;
        z-index: 1;
    }

    .page-header h1 {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 1rem;
    }

    .page-header p {
        font-size: 1.2rem;
        opacity: 0.9;
    }

    .breadcrumb-nav {
        background: transparent;
        padding: 0;
        margin-bottom: 2rem;
    }

    .breadcrumb-nav .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }

    .breadcrumb-nav .breadcrumb-item.active {
        color: white;
    }

    /* Section Title */
    .section-title {
        text-align: center;
        margin-bottom: 3rem;
        padding-top: 2rem;
    }

    .section-title h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .section-title p {
        font-size: 1.1rem;
        color: var(--text-light);
    }

    .section-title::before {
        content: '';
        display: block;
        width: 80px;
        height: 4px;
        background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        margin: 0 auto 1rem;
        border-radius: 2px;
    }

    /* Filter Section */
    .filter-section {
        background: var(--white);
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        margin-bottom: 3rem;
    }

    .filter-section .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .filter-section .form-select,
    .filter-section .form-control {
        border: 2px solid var(--border-color);
        border-radius: 12px;
        padding: 10px 15px;
        transition: all 0.3s;
    }

    .filter-section .form-select:focus,
    .filter-section .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(230, 57, 70, 0.1);
    }

    .btn-filter {
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-filter:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(230, 57, 70, 0.3);
        color: white;
    }

    /* Hub Card */
    .hub-card {
        background: var(--white);
        border: 2px solid var(--border-color);
        border-radius: 20px;
        overflow: hidden;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        height: 100%;
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    }

    .hub-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        border-color: var(--primary-color);
        text-decoration: none;
    }

    .hub-image-wrapper {
        position: relative;
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: rgba(0, 0, 0, 0.1);
    }

    .hub-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .hub-card:hover .hub-image {
        transform: scale(1.1);
    }

    .hub-image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, transparent, rgba(0, 0, 0, 0.3));
    }

    .hub-card-content {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .hub-icon {
        width: 60px;
        height: 60px;
        margin: -30px auto 1rem;
        background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--dark-color);
        transition: transform 0.3s;
        position: relative;
        z-index: 2;
        border: 3px solid var(--white);
    }

    .hub-card:hover .hub-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .hub-card h3 {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .hub-card p {
        font-size: 0.95rem;
        color: var(--text-light);
        margin-bottom: 1rem;
    }

    .hub-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    .hub-info-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-light);
    }

    .hub-info-item i {
        color: var(--primary-color);
        font-size: 1rem;
    }

    .hub-stats {
        display: flex;
        gap: 1rem;
        margin: 1rem 0;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    .hub-stat {
        flex: 1;
        text-align: center;
    }

    .hub-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        display: block;
    }

    .hub-stat-label {
        font-size: 0.75rem;
        color: var(--text-light);
        text-transform: uppercase;
        margin-top: 0.25rem;
    }

    .hub-badge {
        display: inline-block;
        background: var(--success-color);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .btn-hub-detail {
        width: 100%;
        background: var(--primary-color);
        color: white;
        padding: 12px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s;
        text-decoration: none;
        display: block;
        text-align: center;
        margin-top: 1rem;
    }

    .btn-hub-detail:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(230, 57, 70, 0.3);
        color: white;
        text-decoration: none;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--border-color);
        margin-bottom: 20px;
    }

    .empty-state h4 {
        color: var(--text-dark);
        margin-bottom: 10px;
    }

    .empty-state p {
        color: var(--text-light);
        margin-bottom: 20px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 2rem;
        }

        .page-header p {
            font-size: 1rem;
        }

        .section-title h2 {
            font-size: 2rem;
        }

        .hub-image-wrapper {
            height: 150px;
        }

        .hub-icon {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
            margin-top: -25px;
        }

        .hub-card h3 {
            font-size: 1.1rem;
        }

        .hub-info-item {
            font-size: 0.8rem;
        }
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="page-header-content">
            <nav aria-label="breadcrumb" class="breadcrumb-nav">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" style="color: rgba(255,255,255,0.8);">Beranda</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: white;">Hub & Distributor</li>
                </ol>
            </nav>
            <h1>Hub & Distributor</h1>
            <p>Temukan Hub atau Distributor terdekat dengan lokasi Anda untuk pengiriman yang lebih cepat dan efisien</p>
        </div>
    </div>
</section>

<!-- Hubs Section -->
<section class="distributors-section" style="background: var(--light-bg); padding: 4rem 0;">
    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('hubs.index') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">
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
                        <label class="form-label">
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
                        <label class="form-label">
                            <i class="bi bi-search me-1"></i> Cari
                        </label>
                        <input type="text" name="search" class="form-control" placeholder="Nama hub..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-filter w-100">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="section-title">
            <h2>Lokasi Hub & Distributor</h2>
            <p>{{ $warehouses->count() }} lokasi tersedia untuk melayani Anda</p>
        </div>

        @if($warehouses->count() > 0)
            <div class="row g-4">
                @foreach($warehouses as $warehouse)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('hubs.show', $warehouse) }}" class="hub-card" style="text-decoration: none; color: inherit;">
                            <div class="hub-image-wrapper">
                                <img src="https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=400&q=100&auto=format&fit=crop" alt="{{ $warehouse->name }}" class="hub-image">
                                <div class="hub-image-overlay"></div>
                            </div>
                            <div class="hub-card-content">
                                <div class="hub-icon">
                                    <i class="bi bi-building"></i>
                                </div>
                                <h3>{{ $warehouse->name }}</h3>
                                <p>{{ $warehouse->regency->name ?? '' }}, {{ $warehouse->province->name ?? '' }}</p>
                                
                                <div class="hub-stats">
                                    <div class="hub-stat">
                                        <span class="hub-stat-value">{{ $warehouse->products_count ?? 0 }}</span>
                                        <span class="hub-stat-label">Produk</span>
                                    </div>
                                    <div class="hub-stat">
                                        <span class="hub-stat-value">{{ number_format($warehouse->stocks_sum_stock ?? 0) }}</span>
                                        <span class="hub-stat-label">Stock</span>
                                    </div>
                                </div>

                                <div class="hub-info">
                                    @if($warehouse->address)
                                    <div class="hub-info-item">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span style="font-size: 0.85rem;">{{ Str::limit($warehouse->address, 30) }}</span>
                                    </div>
                                    @endif
                                    @if($warehouse->phone)
                                    <div class="hub-info-item">
                                        <i class="bi bi-telephone-fill"></i>
                                        <span>{{ $warehouse->phone }}</span>
                                    </div>
                                    @endif
                                </div>
                                
                                <span class="hub-badge">
                                    <i class="bi bi-check-circle me-1"></i>Aktif
                                </span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-building"></i>
                <h4>Tidak Ada Hub Ditemukan</h4>
                <p>Coba ubah filter pencarian Anda atau lihat semua hub.</p>
                <a href="{{ route('hubs.index') }}" class="btn btn-outline-primary" style="border-radius: 25px;">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset Filter
                </a>
            </div>
        @endif
    </div>
</section>
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
