@extends('layouts.shop')

@section('title', 'Dashboard')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Akun Saya
            <span></span> Dashboard
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
                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <h3 class="mb-0">Halo, {{ Auth::user()->name }}!</h3>
                                </div>
                                <div class="card-body p-4 pt-0">
                                    <p class="font-md text-muted mb-4">
                                        @if($isDistributor)
                                            Sebagai <strong>Distributor</strong>, Anda dapat mengelola pesanan yang masuk ke warehouse Anda, memantau stok, dan melakukan pemesanan produk ke pusat.
                                        @else
                                            Dari dashboard akun Anda, Anda dapat dengan mudah melihat <a href="{{ route('buyer.orders.index') }}" class="text-brand">pesanan terbaru</a>, mengelola <a href="{{ route('buyer.addresses.index') }}" class="text-brand">alamat pengiriman</a>, serta mengedit <a href="{{ route('buyer.profile') }}" class="text-brand">kata sandi dan detail akun Anda</a>.
                                        @endif
                                    </p>

                                    <!-- My Activity Stats -->
                                    <h5 class="mb-3">Aktivitas Belanja Saya</h5>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <div class="card p-3 border-0 bg-brand-light border-radius-10 h-100">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-white p-3 rounded-circle me-3">
                                                        <i class="fi-rs-shopping-cart text-brand"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="mb-0 text-brand">{{ $totalOrders }}</h4>
                                                        <span class="font-xs text-muted">Total Pesanan</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card p-3 border-0 bg-warning-light border-radius-10 h-100">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-white p-3 rounded-circle me-3">
                                                        <i class="fi-rs-time-past text-warning"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="mb-0 text-warning">{{ $pendingOrders }}</h4>
                                                        <span class="font-xs text-muted">Menunggu</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <a href="{{ route('buyer.addresses.index') }}" class="text-decoration-none h-100">
                                                <div class="card p-3 border-0 bg-info-light border-radius-10 h-100">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-white p-3 rounded-circle me-3">
                                                            <i class="fi-rs-marker text-info"></i>
                                                        </div>
                                                        <div>
                                                            <h4 class="mb-0 text-info">{{ Auth::user()->addresses()->count() }}</h4>
                                                            <span class="font-xs text-muted">Alamat Saya</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>

                                    @if($isDistributor)
                                    <!-- Distributor Activity Stats -->
                                    <h5 class="mb-3">Aktivitas Distributor (Incoming)</h5>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="card p-3 border-0 bg-primary-light border-radius-10 h-100">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-white p-3 rounded-circle me-3 text-primary">
                                                        <i class="fi-rs-shopping-bag"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="mb-0 text-primary">{{ $totalIncomingOrders }}</h4>
                                                        <span class="font-xs text-muted">Pesanan Masuk</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card p-3 border-0 bg-danger-light border-radius-10 h-100">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-white p-3 rounded-circle me-3 text-danger">
                                                        <i class="fi-rs-clock"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="mb-0 text-danger">{{ $pendingIncomingOrders }}</h4>
                                                        <span class="font-xs text-muted">Menunggu Diproses</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    @if(Auth::user()->isDriippreneurApproved())
                                        <div class="card mt-4 border-radius-10 border-0 bg-brand text-white">
                                            <div class="card-body p-4">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <h6 class="text-white mb-2">Member DRiiPPreneur</h6>
                                                        <p class="font-sm mb-0">Total Poin yang dapat ditarik:</p>
                                                        <h3 class="text-white mb-0">{{ number_format(Auth::user()->points, 0, ',', '.') }} <span class="font-sm">Poin</span></h3>
                                                    </div>
                                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                                        <a href="{{ route('buyer.point-withdrawals.index') }}" class="btn btn-sm btn-outline-white rounded-pill px-4">
                                                            Kelola Poin
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-5">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                                            <div class="nav-tabs-wrap mb-3 mb-md-0">
                                                <ul class="nav nav-pills dash-tabs" id="pills-tab" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                      <button class="nav-link active" id="type-own-tab" data-type="own" type="button">Pesanan Saya</button>
                                                    </li>
                                                    @if($isDistributor)
                                                    <li class="nav-item" role="presentation">
                                                      <button class="nav-link" id="type-incoming-tab" data-type="incoming" type="button">Pesanan Masuk</button>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                            
                                            <div class="d-flex align-items-center">
                                                <label class="font-xs text-muted mr-10 mb-0 d-none d-md-block">Filter:</label>
                                                <select id="filter-status" class="form-select form-select-sm font-sm rounded border-radius-5" style="width: 150px;">
                                                    <option value="">Semua Status</option>
                                                    <option value="pending">Menunggu</option>
                                                    <option value="processing">Diproses</option>
                                                    <option value="shipped">Dikirim</option>
                                                    <option value="delivered">Selesai</option>
                                                    <option value="cancelled">Dibatalkan</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table id="orders-table" class="table table-clean font-sm w-100">
                                                <thead>
                                                    <tr class="main-heading">
                                                        <th>No. Pesanan</th>
                                                        <th>Tanggal</th>
                                                        <th>Status</th>
                                                        <th>Total</th>
                                                        <th class="text-end">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- DataTables Loaded -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_filter { display: none; }
    #orders-table_wrapper .row:first-child { display: none; }
    .bg-brand-light { background-color: rgba(59, 183, 126, 0.08); }
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.08); }
    .bg-info-light { background-color: rgba(13, 202, 240, 0.08); }
    .btn-outline-white { color: white; border: 1px solid white; }
    .btn-outline-white:hover { background-color: white; color: #3BB77E; }
    
    #orders-table thead th { border-top: 0; border-bottom: 1px solid #f2f2f2; color: #253D4E; font-weight: 700; padding-bottom: 15px; }
    #orders-table tbody td { vertical-align: middle; padding: 15px 0; border-top: 0; border-bottom: 1px solid #f2f2f2; }
    
    .page-link { border-radius: 5px !important; margin: 0 3px; color: #253D4E; border-color: #f2f2f2; font-size: 13px; }
    .page-item.active .page-link { background-color: #3BB77E; border-color: #3BB77E; color: #fff; }
    
    .dataTables_processing { 
        position: absolute; top: 50%; left: 50%; width: 200px; margin-left: -100px; margin-top: -26px; 
        text-align: center; padding: 1em 0; z-index: 100; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .bg-primary-light { background-color: rgba(69, 115, 248, 0.08); }
    .bg-danger-light { background-color: rgba(253, 61, 17, 0.08); }
    
    .dash-tabs .nav-link { 
        background-color: #f7f8f9; color: #253D4E; border-radius: 30px; padding: 8px 20px; font-weight: 600; font-size: 13px; margin-right: 10px; border: 1px solid #f2f2f2; 
    }
    .dash-tabs .nav-link.active { background-color: #3BB77E; color: #fff; border-color: #3BB77E; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    var orderType = 'own';
    
    var table = $('#orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('buyer.dashboard') }}",
            data: function(d) {
                d.status = $('#filter-status').val();
                d.type = orderType;
            }
        },
        columns: [
            { data: 'order_info', name: 'order_number' },
            { data: 'date_formatted', name: 'created_at', render: function(data) { return '<span class="text-muted">' + data + '</span>'; } },
            { data: 'status_badge', name: 'order_status' },
            { data: 'total_formatted', name: 'total_amount', render: function(data) { return '<strong class="text-brand">' + data + '</strong>'; } },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[1, 'desc']],
        pageLength: 5,
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-brand" role="status"></div>',
            info: "Menampilkan _START_ - _END_ dari _TOTAL_",
            paginate: {
                previous: '<i class="fi-rs-angle-left"></i>',
                next: '<i class="fi-rs-angle-right"></i>'
            }
        }
    });

    $('#filter-status').change(function() {
        table.draw();
    });

    $('.dash-tabs .nav-link').on('click', function() {
        $('.dash-tabs .nav-link').removeClass('active');
        $(this).addClass('active');
        orderType = $(this).data('type');
        table.draw();
    });
});
</script>
@endpush
