@extends('layouts.shop')

@section('title', 'Detail Pesanan')

@section('content')
<div class="container">
    <h2 class="my-4">Detail Pesanan</h2>

    <div class="card">
        <div class="card-header">
            <h5>Informasi Pesanan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>No. Pesanan:</strong> {{ $order->order_number }}</p>
                    <p><strong>Tanggal:</strong> {{ $order->created_at->format('d M Y H:i') }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'cancelled' ? 'danger' : 'warning') }}">
                            {{ ucfirst($order->order_status) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Total:</strong> Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                    <p><strong>Metode Pembayaran:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                    <p><strong>Status Pembayaran:</strong> 
                        <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </p>
                </div>
            </div>

            <hr>

            @if($order->sourceWarehouse)
                <h6>Dikirim Dari:</h6>
                <p>
                    <strong>{{ $order->sourceWarehouse->name }}</strong><br>
                    {{ $order->sourceWarehouse->address }}<br>
                    {{ $order->sourceWarehouse->full_location }}
                    @if($order->sourceWarehouse->phone)
                        <br>Telp: {{ $order->sourceWarehouse->phone }}
                    @endif
                </p>
                <hr>
            @endif


            <h6>Informasi Pengiriman:</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Ekspedisi:</strong> {{ $order->expedition ? $order->expedition->name . ' (' . strtoupper($order->expedition->code) . ')' : '-' }}</p>
                    <p><strong>Layanan:</strong> {{ $order->expedition_service ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>No. Resi:</strong> 
                        @if($order->tracking_number)
                            <span class="fw-bold">{{ $order->tracking_number }}</span>
                            @if($order->expedition)
                                <button type="button" class="btn btn-sm btn-info ms-2 text-white" id="btn-track-order">
                                    <i class="fa fa-search"></i> Lacak
                                </button>
                            @endif
                        @else
                            <span class="text-muted">Belum tersedia</span>
                        @endif
                    </p>
                    @if($order->shipped_at)
                        <p><strong>Dikirim:</strong> {{ $order->shipped_at->format('d M Y H:i') }}</p>
                    @endif
                </div>
            </div>
            <hr>

            <h6>Alamat Pengiriman:</h6>
            <p>{{ $order->shipping_address }}</p>

            @if($order->notes)
                <h6>Catatan:</h6>
                <p>{{ $order->notes }}</p>
            @endif
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Item Pesanan</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->display_name }}</td>
                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('buyer.orders.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<!-- Tracking Modal -->
<div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="trackingModalLabel">Status Pengiriman</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="tracking-content">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Sedang melacak status pengiriman...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trackBtn = document.getElementById('btn-track-order');
    if (trackBtn) {
        trackBtn.addEventListener('click', function() {
            const modalEl = document.getElementById('trackingModal');
            // Check if bootstrap is available
            if (typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            } else {
                // Fallback / try jQuery
                $(modalEl).modal('show');
            }

            const contentDiv = document.getElementById('tracking-content');
            contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>';

            fetch('{{ route("buyer.orders.track", $order->id) }}')
                .then(response => response.json())
                .then(result => {
                    let html = '';
                    if (result.success && result.data) {
                        const data = result.data;
                        const status = data.delivery_status;
                        
                        html += '<div class="alert alert-' + (status.status == 'DELIVERED' ? 'success' : 'info') + ' mb-3">';
                        html += '<strong>Status: ' + status.status + '</strong><br>';
                        html += 'Penerima: ' + (status.pod_receiver || '-') + '<br>';
                        html += 'Waktu: ' + (status.pod_date || '-') + ' ' + (status.pod_time || '');
                        html += '</div>';
                        
                        html += '<ul class="list-group timeline">';
                        if (data.manifest && data.manifest.length > 0) {
                            data.manifest.forEach(item => {
                                html += '<li class="list-group-item">';
                                html += '<div class="d-flex w-100 justify-content-between">';
                                html += '<h6 class="mb-1">' + item.manifest_description + '</h6>';
                                html += '<small>' + item.manifest_date + ' ' + item.manifest_time + '</small>';
                                html += '</div>';
                                if (item.city_name) {
                                    html += '<p class="mb-1"><i class="fa fa-map-marker-alt"></i> ' + item.city_name + '</p>';
                                }
                                html += '</li>';
                            });
                        } else {
                            html += '<li class="list-group-item">Tidak ada data manifest.</li>';
                        }
                        html += '</ul>';
                    } else {
                        html = '<div class="alert alert-warning">' + (result.message || 'Data tidak ditemukan') + '</div>';
                    }
                    contentDiv.innerHTML = html;
                })
                .catch(error => {
                    contentDiv.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan: ' + error.message + '</div>';
                });
        });
    }
});
</script>
@endpush
@endsection








