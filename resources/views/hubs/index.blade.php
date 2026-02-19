@extends('layouts.shop')

@section('title', 'Daftar Distributor')

@section('content')
    <main class="main pages">
        <div class="page-header breadcrumb-wrap">
            <div class="container">
                <div class="breadcrumb">
                    <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
                    <span></span> Distributor
                </div>
            </div>
        </div>
        <div class="page-content pt-50">
<div class="container mb-30">
    <div class="archive-header-3 mt-30 mb-80" style="background-image: url({{ asset('themes/nest-frontend/assets/imgs/vendor/vendor-header-bg.png') }})">
        <div class="archive-header-3-inner">
            <div class="vendor-logo mr-50">
                <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/category-1.svg') }}" alt="Hubs" />
            </div>
            <div class="vendor-content">
                <div class="product-category">
                    <span class="text-muted">Network</span>
                </div>
                <h3 class="mb-5 text-white"><a href="#" class="text-white">Hub & Distributor</a></h3>
                <div class="row">
                    <div class="col-lg-7">
                        <div class="vendor-des mb-15">
                            <p class="font-sm text-white">Temukan lokasi Hub atau Distributor resmi kami di seluruh wilayah Indonesia. Kami hadir lebih dekat untuk memastikan produk sampai ke tangan Anda dengan kualitas terbaik dan waktu pengiriman yang lebih efisien.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row flex-row-reverse">
        <div class="col-lg-4-5">
            <div class="shop-product-fillter">
                <div class="totall-product">
                    <p>Kami menemukan <strong class="text-brand">{{ $warehouses->count() }}</strong> lokasi tersedia!</p>
                </div>
                <div class="sort-by-product-area">
                    <div class="sort-by-cover">
                        <div class="sort-by-product-wrap">
                            <div class="sort-by">
                                <span><i class="fi-rs-apps-sort"></i>Filter:</span>
                            </div>
                            <div class="sort-by-dropdown-wrap">
                                <span> Lokasi <i class="fi-rs-angle-small-down"></i></span>
                            </div>
                        </div>
                        <div class="sort-by-dropdown" style="width: 300px; padding: 20px;">
                            <form method="GET" action="{{ route('hubs.index') }}">
                                <div class="mb-10">
                                    <select name="province_id" id="province_id" class="form-select select-active">
                                        <option value="">Semua Provinsi</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->id }}" {{ request('province_id') == $province->id ? 'selected' : '' }}>
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-10">
                                    <select name="regency_id" id="regency_id" class="form-select select-active">
                                        <option value="">Semua Kota/Kab</option>
                                        @foreach($regencies as $regency)
                                            <option value="{{ $regency->id }}" {{ request('regency_id') == $regency->id ? 'selected' : '' }}>
                                                {{ $regency->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-xs w-100">Terapkan Filter</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row vendor-grid">
                @forelse($warehouses as $warehouse)
                <div class="col-lg-4 col-md-6 col-12 col-sm-6">
                    <div class="vendor-wrap mb-40">
                        <div class="vendor-img-action-wrap">
                            <div class="vendor-img">
                                <a href="{{ route('hubs.show', $warehouse) }}">
                                    <img class="default-img" src="{{ asset('themes/nest-frontend/assets/imgs/vendor/vendor-1.png') }}" alt="{{ $warehouse->name }}" />
                                </a>
                            </div>
                            <div class="product-badges product-badges-position product-badges-mrg">
                                <span class="hot">Hub</span>
                            </div>
                        </div>
                        <div class="vendor-content-wrap">
                            <div class="d-flex justify-content-between align-items-end mb-30">
                                <div>
                                    <div class="product-category">
                                        <span class="text-muted">{{ $warehouse->province->name ?? 'Indonesia' }}</span>
                                    </div>
                                    <h4 class="mb-5"><a href="{{ route('hubs.show', $warehouse) }}">{{ $warehouse->name }}</a></h4>
                                    <div class="product-rate-cover">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 100%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-10">
                                    <span class="font-small total-product">{{ $warehouse->stocks_sum_stock ?? 0 }} items</span>
                                </div>
                            </div>
                            <div class="vendor-info mb-30">
                                <ul class="contact-infor text-muted">
                                    <li><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-location.svg') }}" alt="" /><strong>Alamat: </strong> <span>{{ Str::limit($warehouse->address ?? $warehouse->full_location, 50) }}</span></li>
                                    @if($warehouse->phone)
                                    <li><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-contact.svg') }}" alt="" /><strong>Telp:</strong><span>{{ $warehouse->phone }}</span></li>
                                    @endif
                                </ul>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('hubs.show', $warehouse) }}" class="btn btn-xs btn-outline-primary">Detail <i class="fi-rs-arrow-small-right"></i></a>
                                <form action="{{ route('hubs.select') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="warehouse_id" value="{{ $warehouse->id }}">
                                    <button type="submit" class="btn btn-xs">Pilih Hub Ini <i class="fi-rs-check"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center">
                    <div class="mt-40 mb-40">
                        <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-location.svg') }}" alt="No Hubs" style="width: 60px; opacity: 0.5;">
                        <h4 class="mt-20 text-muted">Tidak ada hub ditemukan</h4>
                        <p class="text-muted">Cobalah mengubah filter pencarian Anda</p>
                    </div>
                </div>
                @endforelse
            </div>
            
            <div class="pagination-area mt-20 mb-20">
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-start">
                        <li class="page-item"><a class="page-link active" href="#">1</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="col-lg-1-5 primary-sidebar sticky-sidebar">
            <div class="sidebar-widget-2 widget_search mb-50">
                <div class="search-form">
                    <form method="GET" action="{{ route('hubs.index') }}">
                        <input type="text" name="search" placeholder="Cari nama hub..." value="{{ request('search') }}" />
                        <button type="submit"><i class="fi-rs-search"></i></button>
                    </form>
                </div>
            </div>
            
            <div class="sidebar-widget widget-category-2 mb-30">
                <h5 class="section-title style-1 mb-30">Filter Wilayah</h5>
                <ul>
                    @foreach($provinces->take(10) as $prov)
                    <li>
                        <a href="{{ route('hubs.index', ['province_id' => $prov->id]) }}">
                            <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/category-1.svg') }}" alt="" />{{ $prov->name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            
            <div class="banner-img wow fadeIn mb-lg-0 animated d-lg-block d-none">
                <img src="{{ asset('themes/nest-frontend/assets/imgs/banner/banner-11.png') }}" alt="" />
                <div class="banner-text">
                    <span>Layanan</span>
                    <h4>
                        Pengiriman <br />
                        Lebih <span class="text-brand">Cepat</span><br />
                        dari Hub
                    </h4>
                </div>
            </div>
        </div>
    </div>
        </div>
    </main>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Province change handler
        $('#province_id').on('change', function() {
            var provinceId = $(this).val();
            var regencySelect = $('#regency_id');
            
            // Show loading state
            regencySelect.html('<option value="">Memuat...</option>');
            
            if(provinceId) {
                $.ajax({
                    url: "{{ route('hubs.get-regencies') }}",
                    type: "GET",
                    data: { province_id: provinceId },
                    success: function(data) {
                        var html = '<option value="">Semua Kota/Kab</option>';
                        $.each(data, function(key, value) {
                            html += '<option value="'+value.id+'">'+value.name+'</option>';
                        });
                        regencySelect.html(html);
                    }
                });
            } else {
                regencySelect.html('<option value="">Semua Kota/Kab</option>');
            }
        });
    });
</script>
@endpush
