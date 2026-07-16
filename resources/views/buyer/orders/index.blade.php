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
                             <h3 class="mb-25">Riwayat Pesanan</h3>

                            <div class="order-tabs-container bg-white mb-3" style="border-radius: 2px; box-shadow: 0 1px 1px 0 rgba(0,0,0,.05);">
                                <ul class="nav border-0 flex-nowrap" style="overflow-x: auto; display: flex; width: 100%;">
                                    <li class="nav-item flex-fill text-center">
                                        <a class="nav-link order-status-tab {{ empty($status) || $status === 'all' ? 'active' : '' }}" 
                                           href="{{ route('buyer.orders.index') }}">
                                            Semua
                                        </a>
                                    </li>
                                    <li class="nav-item flex-fill text-center">
                                        <a class="nav-link order-status-tab {{ $status === 'pending' ? 'active' : '' }}" 
                                           href="{{ route('buyer.orders.index', ['status' => 'pending']) }}">
                                            Belum Bayar
                                        </a>
                                    </li>
                                    <li class="nav-item flex-fill text-center">
                                        <a class="nav-link order-status-tab {{ $status === 'processing' ? 'active' : '' }}" 
                                           href="{{ route('buyer.orders.index', ['status' => 'processing']) }}">
                                            Sedang Dikemas {!! ($counts['processing'] ?? 0) > 0 ? '<span class="text-theme-orange">(' . $counts['processing'] . ')</span>' : '' !!}
                                        </a>
                                    </li>
                                    <li class="nav-item flex-fill text-center">
                                        <a class="nav-link order-status-tab {{ $status === 'shipped' ? 'active' : '' }}" 
                                           href="{{ route('buyer.orders.index', ['status' => 'shipped']) }}">
                                            Dikirim {!! ($counts['shipped'] ?? 0) > 0 ? '<span class="text-theme-orange">(' . $counts['shipped'] . ')</span>' : '' !!}
                                        </a>
                                    </li>
                                    <li class="nav-item flex-fill text-center">
                                        <a class="nav-link order-status-tab {{ $status === 'delivered' ? 'active' : '' }}" 
                                           href="{{ route('buyer.orders.index', ['status' => 'delivered']) }}">
                                            Selesai
                                        </a>
                                    </li>
                                    <li class="nav-item flex-fill text-center">
                                        <a class="nav-link order-status-tab {{ $status === 'cancelled' ? 'active' : '' }}" 
                                           href="{{ route('buyer.orders.index', ['status' => 'cancelled']) }}">
                                            Dibatalkan
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="search-bar-container mb-4">
                                <div class="d-flex align-items-center" style="background: #eaeaea; border-radius: 2px; padding: 8px 15px;">
                                    <i class="fi-rs-search" style="color: #999; font-size: 16px; margin-right: 10px;"></i>
                                    <input type="text" class="form-control border-0 bg-transparent shadow-none p-0" placeholder="Kamu bisa cari berdasarkan Nama Penjual, No. Pesanan atau Nama Produk" style="font-size: 14px; color: #333;">
                                </div>
                            </div>

                            <div id="orders-container">
                                @include('buyer.orders.partials.list')
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
    
    .text-maroon { color: #6A1B1B !important; }

    .order-tabs-container::-webkit-scrollbar {
        display: none;
    }
    .order-tabs-container {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .order-status-tab {
        display: block;
        padding: 16px 5px;
        font-size: 15px;
        font-weight: 400;
        color: rgba(0,0,0,.8) !important;
        background-color: #fff;
        border-bottom: 2px solid transparent;
        transition: color 0.2s ease;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
    }
    .order-status-tab:hover {
        color: #ee4d2d !important;
    }
    .order-status-tab.active {
        color: #ee4d2d !important;
        border-bottom: 2px solid #ee4d2d;
    }
    .text-theme-orange {
        color: #ee4d2d;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Intercept tab clicks
        $(document).on('click', '.order-status-tab', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            
            // Update active class immediately for better feedback
            $('.order-status-tab').removeClass('active');
            $(this).addClass('active');
            
            loadOrders(url);
            
            // Update browser URL without reloading
            window.history.pushState({path: url}, '', url);
        });

        // Intercept pagination clicks
        $(document).on('click', '#orders-container .pagination a', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            loadOrders(url);
            
            // Scroll smoothly to top of orders list
            $('html, body').animate({
                scrollTop: $("#orders-container").offset().top - 120
            }, 300);
        });

        function loadOrders(url) {
            // Add loading state
            $('#orders-container').css('opacity', '0.5');
            
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Update HTML
                    $('#orders-container').html(response.html);
                    $('#orders-container').css('opacity', '1');
                    
                    // Update tab count badges dynamically
                    if (response.counts) {
                        $('.order-status-tab').each(function() {
                            var href = $(this).attr('href');
                            var badge = $(this).find('.text-theme-orange');
                            if(badge.length === 0) {
                                $(this).append(' <span class="text-theme-orange"></span>');
                                badge = $(this).find('.text-theme-orange');
                            }
                            
                            var count = 0;
                            if (href.includes('status=pending')) {
                                count = response.counts.pending || 0;
                            } else if (href.includes('status=processing')) {
                                count = response.counts.processing || 0;
                            } else if (href.includes('status=shipped')) {
                                count = response.counts.shipped || 0;
                            } else if (href.includes('status=delivered')) {
                                count = response.counts.delivered || 0;
                            } else if (href.includes('status=cancelled')) {
                                count = response.counts.cancelled || 0;
                            } else {
                                // "Semua" tab doesn't show badge in shopee UI usually, but if needed we can handle
                                count = 0;
                            }
                            
                            badge.text(count > 0 ? '(' + count + ')' : '');
                        });
                    }
                },
                error: function(xhr) {
                    $('#orders-container').css('opacity', '1');
                    console.error('Failed to load orders', xhr);
                }
            });
        }
        
        // Handle back/forward browser buttons
        window.onpopstate = function(event) {
            var url = window.location.href;
            loadOrders(url);
            
            // Set active tab based on path
            $('.order-status-tab').removeClass('active');
            $('.order-status-tab').each(function() {
                if ($(this).attr('href') === url) {
                    $(this).addClass('active');
                } else if (!url.includes('status') && $(this).attr('href').indexOf('status') === -1) {
                    $(this).addClass('active');
                }
            });
        };
    });
</script>
@endsection

