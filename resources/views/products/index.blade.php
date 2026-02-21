@extends('layouts.shop')

@section('title', 'Katalog Produk')

@section('content')
<main class="main">

    <div class="container mb-30">
        <div class="row">
            <div class="col-12">
                <div class="shop-product-fillter">
                    <div class="totall-product">
                        <p>Kami menemukan <strong class="text-brand">{{ $products->total() }}</strong> produk untuk Anda!</p>
                    </div>
                    <div class="sort-by-product-area">
                        <div class="sort-by-cover mr-10">
                            <div class="sort-by-product-wrap">
                                <div class="sort-by">
                                    <span><i class="fi-rs-apps"></i>Show:</span>
                                </div>
                                <div class="sort-by-dropdown-wrap">
                                    <span> {{ $products->perPage() }} <i class="fi-rs-angle-small-down"></i></span>
                                </div>
                            </div>
                            <div class="sort-by-dropdown">
                                <ul>
                                    <li><a class="{{ request('per_page') == 12 ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['per_page' => 12]) }}">12</a></li>
                                    <li><a class="{{ request('per_page') == 24 ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['per_page' => 24]) }}">24</a></li>
                                    <li><a class="{{ request('per_page') == 48 ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['per_page' => 48]) }}">48</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="sort-by-cover">
                            <div class="sort-by-product-wrap">
                                <div class="sort-by">
                                    <span><i class="fi-rs-apps-sort"></i>Sort by:</span>
                                </div>
                                <div class="sort-by-dropdown-wrap">
                                    <span> 
                                        @switch(request('sort'))
                                            @case('price_low') Harga Terendah @break
                                            @case('price_high') Harga Tertinggi @break
                                            @case('name') Nama A-Z @break
                                            @default Terbaru
                                        @endswitch
                                        <i class="fi-rs-angle-small-down"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="sort-by-dropdown">
                                <ul>
                                    <li><a class="{{ !request('sort') || request('sort') == 'latest' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}">Terbaru</a></li>
                                    <li><a class="{{ request('sort') == 'price_low' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}">Harga Terendah</a></li>
                                    <li><a class="{{ request('sort') == 'price_high' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}">Harga Tertinggi</a></li>
                                    <li><a class="{{ request('sort') == 'name' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'name']) }}">Nama A-Z</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row product-grid">
                    @forelse($products as $product)
                        @include('themes.nest.partials.product-card', ['product' => $product])
                    @empty
                        <div class="col-12 text-center py-5">
                            <div class="no-products">
                                <i class="fi-rs-search mb-3" style="font-size: 3rem; color: #ccc;"></i>
                                <h3 class="mb-3">Tidak ada produk ditemukan</h3>
                                <p class="text-muted">Gunakan kata kunci atau filter yang berbeda.</p>
                                <a href="{{ route('products.index') }}" class="btn btn-sm btn-default mt-4"><i class="fi-rs-refresh mr-5"></i> Reset Filter</a>
                            </div>
                        </div>
                    @endforelse
                </div>
                <!--pagination-->
                <div class="pagination-area mt-20 mb-20">
                    <nav aria-label="Page navigation example">
                        {{ $products->links('vendor.pagination.nest') }}
                    </nav>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
