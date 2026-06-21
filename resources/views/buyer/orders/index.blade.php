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

                            <div class="order-tabs-container mt-15 mb-30" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                <ul class="nav flex-nowrap pb-10" style="gap: 8px; border-bottom: 2px solid #edf2f7;">
                                    <li class="nav-item">
                                        <a class="order-status-tab {{ empty($status) || $status === 'all' ? 'active' : '' }} {{ $totalCount === 0 ? 'zero-count' : '' }}" 
                                           href="{{ route('buyer.orders.index') }}">
                                            <span>Semua</span>
                                            <span class="count-badge">{{ $totalCount }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="order-status-tab {{ $status === 'pending' ? 'active' : '' }} {{ ($counts['pending'] ?? 0) === 0 ? 'zero-count' : '' }}" 
                                           href="{{ route('buyer.orders.index', ['status' => 'pending']) }}">
                                            <span>Menunggu Pembayaran</span>
                                            <span class="count-badge">{{ $counts['pending'] ?? 0 }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="order-status-tab {{ $status === 'processing' ? 'active' : '' }} {{ ($counts['processing'] ?? 0) === 0 ? 'zero-count' : '' }}" 
                                           href="{{ route('buyer.orders.index', ['status' => 'processing']) }}">
                                            <span>Dikemas</span>
                                            <span class="count-badge">{{ $counts['processing'] ?? 0 }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="order-status-tab {{ $status === 'shipped' ? 'active' : '' }} {{ ($counts['shipped'] ?? 0) === 0 ? 'zero-count' : '' }}" 
                                           href="{{ route('buyer.orders.index', ['status' => 'shipped']) }}">
                                            <span>Dikirim</span>
                                            <span class="count-badge">{{ $counts['shipped'] ?? 0 }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="order-status-tab {{ $status === 'delivered' ? 'active' : '' }} {{ ($counts['delivered'] ?? 0) === 0 ? 'zero-count' : '' }}" 
                                           href="{{ route('buyer.orders.index', ['status' => 'delivered']) }}">
                                            <span>Selesai</span>
                                            <span class="count-badge">{{ $counts['delivered'] ?? 0 }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="order-status-tab {{ $status === 'cancelled' ? 'active' : '' }} {{ ($counts['cancelled'] ?? 0) === 0 ? 'zero-count' : '' }}" 
                                           href="{{ route('buyer.orders.index', ['status' => 'cancelled']) }}">
                                            <span>Batal</span>
                                            <span class="count-badge">{{ $counts['cancelled'] ?? 0 }}</span>
                                        </a>
                                    </li>
                                </ul>
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

    .order-tabs-container::-webkit-scrollbar {
        display: none;
    }
    .order-tabs-container {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }

    .order-status-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.25s ease;
        border: 1.5px solid #edf2f7;
        background-color: #fff;
        color: #718096 !important;
        white-space: nowrap;
    }
    .order-status-tab:hover {
        border-color: #cbd5e0;
        color: #4a5568 !important;
        transform: translateY(-1px);
    }
    .order-status-tab.active {
        background-color: #6A1B1B !important;
        border-color: #6A1B1B !important;
        color: #fff !important;
        box-shadow: 0 4px 15px rgba(106, 27, 27, 0.15);
    }
    .order-status-tab .count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 7px;
        border-radius: 11px;
        font-size: 11px;
        font-weight: 700;
        background-color: #edf2f7;
        color: #4a5568;
        transition: all 0.2s ease;
    }
    .order-status-tab.active .count-badge {
        background-color: rgba(255, 255, 255, 0.25);
        color: #fff;
    }
    .order-status-tab.zero-count {
        opacity: 0.55;
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
                            var badge = $(this).find('.count-badge');
                            
                            if (href.includes('status=pending')) {
                                badge.text(response.counts.pending || 0);
                                toggleZeroCountClass($(this), response.counts.pending || 0);
                            } else if (href.includes('status=processing')) {
                                badge.text(response.counts.processing || 0);
                                toggleZeroCountClass($(this), response.counts.processing || 0);
                            } else if (href.includes('status=shipped')) {
                                badge.text(response.counts.shipped || 0);
                                toggleZeroCountClass($(this), response.counts.shipped || 0);
                            } else if (href.includes('status=delivered')) {
                                badge.text(response.counts.delivered || 0);
                                toggleZeroCountClass($(this), response.counts.delivered || 0);
                            } else if (href.includes('status=cancelled')) {
                                badge.text(response.counts.cancelled || 0);
                                toggleZeroCountClass($(this), response.counts.cancelled || 0);
                            } else {
                                // "Semua" tab
                                badge.text(response.total_count || 0);
                                toggleZeroCountClass($(this), response.total_count || 0);
                            }
                        });
                    }
                },
                error: function(xhr) {
                    $('#orders-container').css('opacity', '1');
                    console.error('Failed to load orders', xhr);
                }
            });
        }

        function toggleZeroCountClass(element, count) {
            if (parseInt(count) === 0) {
                element.addClass('zero-count');
            } else {
                element.removeClass('zero-count');
            }
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

