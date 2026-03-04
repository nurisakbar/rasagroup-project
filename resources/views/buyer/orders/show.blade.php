@extends('layouts.shop')

@section('title', 'Detail Pesanan')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('buyer.orders.index') }}">Pesanan Saya</a>
            <span></span> Detail Pesanan
        </div>
    </div>
</div>

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4">
                    <div class="dashboard-menu">
                        <ul class="nav flex-column" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('buyer.dashboard') }}"><i class="fi-rs-settings-sliders mr-10"></i>Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('buyer.orders.index') }}"><i class="fi-rs-shopping-bag mr-10"></i>Pesanan Saya</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('buyer.addresses.index') }}"><i class="fi-rs-marker mr-10"></i>Alamat Saya</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('buyer.profile') }}"><i class="fi-rs-user mr-10"></i>Detail Akun</a>
                            </li>
                            <li class="nav-item">
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-order-show">
                                    @csrf
                                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-order-show').submit();">
                                        <i class="fi-rs-sign-out mr-10"></i>Keluar
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="tab-content account dashboard-content">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                                <div>
                                    <a href="{{ route('buyer.orders.index') }}" class="text-brand font-sm fw-bold mb-2 d-inline-block">
                                        <i class="fi-rs-arrow-left mr-5"></i> Kembali ke Daftar Pesanan
                                    </a>
                                    <h3 class="mb-0">Detail Pesanan <span class="text-brand">#{{ $order->order_number }}</span></h3>
                                </div>
                                <div class="badge-group">
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
                                    @endphp
                                    <span class="badge rounded-pill {{ $statusClass }} px-4 py-2 text-white font-sm">{{ $statusLabel }}</span>
                                </div>
                            </div>

                            <div class="row g-4">
                                <!-- Order Info Card -->
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 shadow-sm border-radius-15 overflow-hidden">
                                        <div class="card-header bg-brand-light border-0 p-3">
                                            <h5 class="mb-0 text-brand font-md"><i class="fi-rs-document mr-10"></i>Informasi Pesanan</h5>
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="info-list">
                                                <div class="info-item d-flex justify-content-between mb-3 pb-2 border-bottom">
                                                    <span class="text-dark font-sm">Tanggal Transaksi</span>
                                                    <span class="fw-bold font-sm text-dark">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                                </div>
                                                <div class="info-item d-flex justify-content-between mb-3 pb-2 border-bottom">
                                                    <span class="text-dark font-sm">Metode Pembayaran</span>
                                                    <span class="fw-bold font-sm text-dark">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                                                </div>
                                                <div class="info-item d-flex justify-content-between">
                                                    <span class="text-dark font-sm">Status Pembayaran</span>
                                                    <span class="badge rounded-pill {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning' }} px-3">
                                                        {{ strtoupper($order->payment_status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Status Card -->
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 shadow-sm border-radius-15 overflow-hidden">
                                        <div class="card-header bg-info-light border-0 p-3">
                                            <h5 class="mb-0 text-info font-md"><i class="fi-rs-truck-side mr-10"></i>Status Pengiriman</h5>
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="info-list">
                                                <div class="info-item d-flex justify-content-between mb-3 pb-2 border-bottom">
                                                    <span class="text-dark font-sm">Kurir & Layanan</span>
                                                    <span class="fw-bold font-sm text-dark text-end">
                                                        {{ $order->expedition ? $order->expedition->name : '-' }} 
                                                        <br><small class="text-dark fw-bold">({{ $order->expedition_service ?? 'Standard' }})</small>
                                                    </span>
                                                </div>
                                                <div class="info-item d-flex justify-content-between mb-3 pb-2 border-bottom align-items-center">
                                                    <span class="text-dark font-sm">Nomor Resi</span>
                                                    @if($order->tracking_number)
                                                        <div class="text-end">
                                                            <span class="fw-bold font-sm text-brand d-block">{{ $order->tracking_number }}</span>
                                                            <a href="javascript:void(0)" class="font-xs text-info fw-bold" id="btn-track-order">
                                                                <i class="fi-rs-search mr-5"></i> Lacak Sekarang
                                                            </a>
                                                        </div>
                                                    @else
                                                        <span class="text-dark font-sm fw-bold">Belum tersedia</span>
                                                    @endif
                                                </div>
                                                <div class="info-item d-flex justify-content-between">
                                                    <span class="text-dark font-sm">Tanggal Pengiriman</span>
                                                    <span class="fw-bold font-sm text-dark">{{ $order->shipped_at ? $order->shipped_at->format('d M Y') : '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Warehouse & Shipping Address Card -->
                                <div class="col-12 mt-4">
                                    <div class="card border border-light-2 shadow-sm border-radius-15 p-4">
                                        <div class="row g-4">
                                            @if($order->sourceWarehouse)
                                            <div class="col-md-6 border-end-md">
                                                <h6 class="mb-3 text-dark text-uppercase fw-bold letter-spacing-1 font-xs">Dikirim Dari</h6>
                                                <div class="d-flex">
                                                    <div class="icon-circle bg-brand-light text-brand shadow-sm mr-15">
                                                        <i class="fi-rs-shop"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 text-dark fw-bold">{{ $order->sourceWarehouse->name }}</h6>
                                                        <p class="font-sm text-dark mb-0">
                                                            {{ $order->sourceWarehouse->address }},<br>
                                                            {{ $order->sourceWarehouse->full_location }}<br>
                                                            @if($order->sourceWarehouse->phone) 
                                                                <span class="font-sm mt-2 d-block text-dark fw-bold"><i class="fi-rs-smartphone mr-5"></i> {{ $order->sourceWarehouse->phone }}</span> 
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-{{ $order->sourceWarehouse ? '6' : '12' }}">
                                                <h6 class="mb-3 text-dark text-uppercase fw-bold letter-spacing-1 font-xs">Alamat Penerima</h6>
                                                <div class="d-flex">
                                                    <div class="icon-circle bg-info-light text-info shadow-sm mr-15">
                                                        <i class="fi-rs-marker"></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-sm text-dark fw-bold mb-1">
                                                            {!! nl2br(e($order->shipping_address)) !!}
                                                        </p>
                                                        @if($order->notes)
                                                            <div class="mt-2 p-2 bg-light border-radius-5 border-start border-3 border-brand">
                                                                <p class="font-sm text-dark mb-0 italic">
                                                                    <i class="fi-rs-edit mr-5"></i> Catatan: {{ $order->notes }}
                                                                </p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Items List -->
                                <div class="col-12 mt-4">
                                    <div class="card border-0 shadow-sm border-radius-15 overflow-hidden">
                                        <div class="card-header bg-white p-4 border-bottom">
                                            <h5 class="mb-0">Daftar Produk</h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-borderless align-middle mb-0">
                                                    <thead class="bg-brand-light">
                                                        <tr>
                                                            <th class="ps-4 py-3 font-sm text-dark fw-bold">Produk</th>
                                                            <th class="py-3 font-sm text-dark fw-bold text-center">Harga Satuan</th>
                                                            <th class="py-3 font-sm text-dark fw-bold text-center">Jumlah</th>
                                                            <th class="pe-4 py-3 font-sm text-dark fw-bold text-end">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($order->items as $item)
                                                            <tr class="border-bottom">
                                                                <td class="ps-4 py-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="product-img-wrap mr-15">
                                                                            <img src="{{ $item->product->image_url ? $item->product->image_url : asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" 
                                                                                 alt="{{ $item->product->display_name }}" 
                                                                                 class="rounded" 
                                                                                 style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #eee;"
                                                                                 onerror="this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}'">
                                                                        </div>
                                                                        <div>
                                                                            <h6 class="font-sm mb-1 text-dark">{{ $item->product->display_name }}</h6>
                                                                            <span class="font-xs text-muted">SKU: {{ $item->sku ?? '-' }}</span>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="py-3 text-center font-sm" data-label="Harga">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                                                <td class="py-3 text-center font-sm" data-label="Jumlah">{{ $item->quantity }}</td>
                                                                <td class="pe-4 py-3 text-end fw-bold text-brand" data-label="Subtotal">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-white p-4 border-top">
                                            <div class="row justify-content-end">
                                                <div class="col-md-5 col-lg-4">
                                                    <div class="price-summary">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-dark font-sm fw-bold">Subtotal Produk</span>
                                                            <span class="font-sm text-dark fw-bold">Rp {{ number_format($order->subtotal ?? 0, 0, ',', '.') }}</span>
                                                        </div>
                                                        @if($order->discount_amount > 0)
                                                        <div class="d-flex justify-content-between mb-2 text-danger fw-bold">
                                                            <span class="font-sm">Diskon ({{ $order->discount_percent }}%)</span>
                                                            <span class="font-sm fw-bold">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                                                        </div>
                                                        @endif
                                                        <div class="d-flex justify-content-between mb-3 text-dark fw-bold">
                                                            <span class="font-sm">Ongkos Kirim</span>
                                                            <span class="font-sm text-dark fw-bold">Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between pt-3 border-top">
                                                            <h5 class="mb-0">Total Harga</h5>
                                                            <h4 class="mb-0 text-brand text-nowrap">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h4>
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tracking Modal -->
<div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-radius-10">
      <div class="modal-header">
        <h5 class="modal-title" id="trackingModalLabel">Status Pengiriman</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="tracking-content" style="min-height: 200px;">
        <div class="text-center py-5">
            <div class="spinner-border text-brand" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Sedang melacak status pengiriman...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm rounded" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<style>
    .border-radius-15 { border-radius: 15px; }
    .bg-brand-light { background-color: rgba(59, 183, 126, 0.08); }
    .bg-info-light { background-color: rgba(61, 144, 239, 0.08); }
    .bg-light-gray { background-color: #f8fafc; }
    
    .icon-circle {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 18px;
    }
    
    .letter-spacing-1 { letter-spacing: 1px; }
    .italic { font-style: italic; }
    
    .info-item:last-child { border-bottom: 0 !important; margin-bottom: 0 !important; }
    
    .product-img-wrap {
        flex-shrink: 0;
    }
    
    @media (min-width: 768px) {
        .border-end-md { border-right: 1px solid #edf2f7; }
        .dashboard-content { padding-left: 50px; }
    }
    
    @media (max-width: 767px) {
        .badge-group { margin-top: 10px; width: 100%; }
        .badge-group .badge { width: 100%; display: block; text-align: center; }
        .card-header h5 { font-size: 14px; }
        .info-list .info-item { flex-direction: row; align-items: center; }
        .table thead { display: none; }
        .table tbody tr { display: block; padding: 15px; }
        .table tbody td { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 8px 0 !important;
            text-align: right !important;
            border: 0;
        }
        .table tbody td:first-child { 
            display: block; 
            text-align: left !important; 
            padding-bottom: 15px !important;
            border-bottom: 1px dashed #eee !important;
            margin-bottom: 10px;
        }
        .table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #777;
            text-align: left;
        }
        .table tbody td:first-child::before { display: none; }
        .price-summary { width: 100%; }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trackBtn = document.getElementById('btn-track-order');
    if (trackBtn) {
        trackBtn.addEventListener('click', function() {
            const modalEl = document.getElementById('trackingModal');
            let modalInstance;
            if (typeof bootstrap !== 'undefined') {
                modalInstance = new bootstrap.Modal(modalEl);
                modalInstance.show();
            } else {
                $(modalEl).modal('show');
            }

            const contentDiv = document.getElementById('tracking-content');
            contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-brand" role="status"></div><p class="mt-2">Loading...</p></div>';

            fetch('{{ route("buyer.orders.track", $order->id) }}')
                .then(response => response.json())
                .then(result => {
                    let html = '';
                    if (result.success && result.data) {
                        const data = result.data;
                        const status = data.delivery_status;
                        
                        html += '<div class="alert alert-' + (status.status == 'DELIVERED' ? 'success' : 'info') + ' border-0 bg-light mb-4">';
                        html += '<h6 class="mb-2">Status Terakhir: ' + status.status + '</h6>';
                        html += '<p class="font-sm mb-0">Penerima: ' + (status.pod_receiver || '-') + '<br>';
                        html += 'Waktu: ' + (status.pod_date || '-') + ' ' + (status.pod_time || '') + '</p>';
                        html += '</div>';
                        
                        html += '<div class="timeline-container px-3">';
                        if (data.manifest && data.manifest.length > 0) {
                            data.manifest.forEach(item => {
                                html += '<div class="mb-4 position-relative border-start ps-4 ml-10">';
                                html += '<span class="position-absolute translate-middle-x bg-brand rounded-circle" style="left:0; top:5px; width:12px; height:12px;"></span>';
                                html += '<h6 class="font-sm mb-1">' + item.manifest_description + '</h6>';
                                html += '<p class="font-xs text-muted mb-1">' + item.manifest_date + ' ' + item.manifest_time + '</p>';
                                if (item.city_name) {
                                    html += '<span class="font-xs"><i class="fi-rs-marker mr-5"></i>' + item.city_name + '</span>';
                                }
                                html += '</div>';
                            });
                        } else {
                            html += '<p class="text-center text-muted py-4">Tidak ada data manifest detail.</p>';
                        }
                        html += '</div>';
                    } else {
                        html = '<div class="alert alert-warning border-0 bg-light">' + (result.message || 'Data pelacakan saat ini belum tersedia') + '</div>';
                    }
                    contentDiv.innerHTML = html;
                })
                .catch(error => {
                    contentDiv.innerHTML = '<div class="alert alert-danger border-0 bg-light">Terjadi kesalahan: ' + error.message + '</div>';
                });
        });
    }
});
</script>
@endpush
@endsection








