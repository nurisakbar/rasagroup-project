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
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h3 class="mb-0">Detail Pesanan</h3>
                                @php
                                    $statusClass = match($order->order_status) {
                                        'pending' => 'bg-warning',
                                        'processing', 'shipped' => 'bg-info',
                                        'delivered' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                    $statusLabel = match($order->order_status) {
                                        'pending' => 'Menunggu',
                                        'processing' => 'Diproses',
                                        'shipped' => 'Dikirim',
                                        'delivered' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        default => ucfirst($order->order_status),
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $statusClass }} text-white font-md">{{ $statusLabel }}</span>
                            </div>

                            <!-- Order Info & Shipping -->
                            <div class="card mb-4 border-0 shadow-sm border-radius-10">
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-4 mb-md-0">
                                            <h5 class="mb-3 text-brand border-bottom pb-2">Informasi Pesanan</h5>
                                            <div class="table-responsive">
                                                <table class="table table-clean font-sm">
                                                    <tbody>
                                                        <tr>
                                                            <td class="fw-bold py-1">No. Pesanan</td>
                                                            <td class="py-1 text-end"><span class="text-brand">#{{ $order->order_number }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-bold py-1">Tanggal</td>
                                                            <td class="py-1 text-end">{{ $order->created_at->format('d M Y, H:i') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-bold py-1">Pembayaran</td>
                                                            <td class="py-1 text-end">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-bold py-1">Status Bayar</td>
                                                            <td class="py-1 text-end">
                                                                <span class="badge rounded-pill bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} text-white font-xs px-2">
                                                                    {{ ucfirst($order->payment_status) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="mb-3 text-brand border-bottom pb-2">Pengiriman</h5>
                                             <div class="table-responsive">
                                                <table class="table table-clean font-sm">
                                                    <tbody>
                                                        <tr>
                                                            <td class="fw-bold py-1">Ekspedisi</td>
                                                            <td class="py-1 text-end">{{ $order->expedition ? $order->expedition->name . ' (' . strtoupper($order->expedition->code) . ')' : '-' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-bold py-1">Layanan</td>
                                                            <td class="py-1 text-end">{{ $order->expedition_service ?? '-' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-bold py-1">No. Resi</td>
                                                            <td class="py-1 text-end">
                                                                @if($order->tracking_number)
                                                                    <strong class="text-brand">{{ $order->tracking_number }}</strong>
                                                                    @if($order->expedition)
                                                                        <br><a href="javascript:void(0)" class="btn-small p-0 mt-1" id="btn-track-order">
                                                                            <i class="fi-rs-search"></i> Lacak Pengiriman
                                                                        </a>
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-bold py-1">Dikirim</td>
                                                            <td class="py-1 text-end">{{ $order->shipped_at ? $order->shipped_at->format('d M Y') : '-' }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="divider mt-2 mb-4"></div>
                                    
                                    <div class="row">
                                         <!-- Dikirim Dari -->
                                        @if($order->sourceWarehouse)
                                        <div class="col-md-6 mb-4 mb-md-0">
                                            <h6 class="mb-3 text-muted"><i class="fi-rs-shop mr-10"></i>Pengirim</h6>
                                            <div class="bg-light p-3 border-radius-5">
                                                <h6 class="mb-1">{{ $order->sourceWarehouse->name }}</h6>
                                                <p class="font-sm text-muted mb-0">
                                                    {{ $order->sourceWarehouse->address }}<br>
                                                    {{ $order->sourceWarehouse->full_location }}<br>
                                                    @if($order->sourceWarehouse->phone) <span class="font-xs"><i class="fi-rs-smartphone mr-5"></i> {{ $order->sourceWarehouse->phone }}</span> @endif
                                                </p>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Alamat Penerima -->
                                        <div class="{{ $order->sourceWarehouse ? 'col-md-6' : 'col-12' }}">
                                            <h6 class="mb-3 text-muted"><i class="fi-rs-marker mr-10"></i>Penerima</h6>
                                            <div class="bg-light p-3 border-radius-5">
                                                <p class="font-sm mb-0">
                                                    {!! nl2br(e($order->shipping_address)) !!}
                                                </p>
                                                @if($order->notes)
                                                    <div class="mt-2 text-brand italic font-xs border-top pt-2">
                                                        <i class="fi-rs-edit mr-5"></i> Catatan: {{ $order->notes }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Items -->
                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom-0 p-4 pb-0">
                                     <h5 class="mb-0">Item Pesanan</h5>
                                </div>
                                <div class="card-body p-4 pt-0">
                                    <div class="table-responsive">
                                        <table class="table table-clean font-sm">
                                            <thead>
                                                <tr class="main-heading">
                                                    <th>Produk</th>
                                                    <th>Harga</th>
                                                    <th class="text-center">Jml</th>
                                                    <th class="text-end">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($order->items as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ $item->product->image_url ? $item->product->image_url : asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" 
                                                                     alt="{{ $item->product->display_name }}" 
                                                                     class="img-fluid rounded mr-15" 
                                                                     style="width: 60px; height: 60px; object-fit: cover;"
                                                                     onerror="this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}'">
                                                                <div>
                                                                    <h6 class="font-sm mb-0">{{ $item->product->display_name }}</h6>
                                                                    <span class="font-xs text-muted">{{ $item->sku ?? '' }}</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                                        <td class="text-center">{{ $item->quantity }}</td>
                                                        <td class="text-end product-price">
                                                            <strong class="text-brand">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-end fw-bold py-2">Subtotal</td>
                                                    <td class="text-end py-2">Rp {{ number_format($order->subtotal ?? $order->total_amount, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end fw-bold py-2">Biaya Pengiriman</td>
                                                    <td class="text-end py-2">Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr class="bg-light">
                                                    <td colspan="3" class="text-end fw-bold py-3"><h5 class="mb-0 text-brand">Total Pembayaran</h5></td>
                                                    <td class="text-end py-3"><h5 class="mb-0 text-brand text-nowrap">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h5></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="mt-4">
                                         <a href="{{ route('buyer.orders.index') }}" class="btn btn-outline-secondary rounded font-sm"><i class="fi-rs-arrow-left mr-5"></i>Kembali ke Pesanan</a>
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
    .table-clean tbody td { vertical-align: middle; padding: 10px 0; border-top: 1px solid #f2f2f2; }
    .table-clean thead th { border-top: 0; border-bottom-width: 1px; color: #253D4E; font-weight: 700; }
    .divider { height: 1px; background-color: #f2f2f2; width: 100%; }
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








