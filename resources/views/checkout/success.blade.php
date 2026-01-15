@extends('layouts.shop')

@section('title', 'Pesanan Berhasil')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm text-center">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="mb-2">Pesanan Berhasil Dibuat!</h2>
                    <p class="text-muted mb-4">Terima kasih atas pembelian Anda. Pesanan Anda sedang diproses.</p>
                    
                    <div class="bg-light rounded p-4 mb-4">
                        <small class="text-muted">Nomor Pesanan</small>
                        <h3 class="text-primary mb-0">{{ $order->order_number }}</h3>
                    </div>

                    <div class="row text-start mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="bi bi-geo-alt"></i> Alamat Pengiriman</h6>
                                    @if($order->address)
                                        <strong>{{ $order->address->recipient_name }}</strong>
                                        <p class="mb-0 small">
                                            {{ $order->address->phone }}<br>
                                            {{ $order->address->address_detail }}<br>
                                            {{ $order->address->village?->name }}, Kec. {{ $order->address->district?->name }}<br>
                                            {{ $order->address->regency?->name }}, {{ $order->address->province?->name }}
                                            @if($order->address->postal_code) {{ $order->address->postal_code }} @endif
                                        </p>
                                    @else
                                        <p class="mb-0 small">{!! nl2br(e($order->shipping_address)) !!}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="bi bi-truck"></i> Pengiriman</h6>
                                    @if($order->expedition)
                                        <p class="mb-1">
                                            <strong>{{ $order->expedition->name }}</strong>
                                        </p>
                                        <p class="mb-0 small text-muted">
                                            Layanan: {{ $order->expedition_service }}<br>
                                            Estimasi: {{ $order->expedition->estimated_delivery }}
                                        </p>
                                    @else
                                        <p class="mb-0 text-muted">-</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Source Warehouse -->
                    @if($order->sourceWarehouse)
                    <div class="row text-start mb-4">
                        <div class="col-12">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="bi bi-building"></i> Dikirim dari Hub</h6>
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $order->sourceWarehouse->name }}</strong>
                                            <p class="mb-0 small text-muted">
                                                <i class="bi bi-geo-alt"></i> {{ $order->sourceWarehouse->full_location }}
                                                @if($order->sourceWarehouse->phone)
                                                    <br><i class="bi bi-telephone"></i> {{ $order->sourceWarehouse->phone }}
                                                @endif
                                            </p>
                                        </div>
                                        <span class="badge bg-primary"><i class="bi bi-box-seam"></i> Hub Pengirim</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row text-start mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="bi bi-credit-card"></i> Pembayaran</h6>
                                    <p class="mb-2">
                                        <strong>Metode:</strong> 
                                        {{ $order->payment_method === 'manual_transfer' ? 'Transfer Bank' : 'COD (Bayar di Tempat)' }}
                                    </p>
                                    <p class="mb-0">
                                        <strong>Status:</strong> 
                                        <span class="badge bg-warning">{{ ucfirst($order->payment_status) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2"><i class="bi bi-calendar-check"></i> Tanggal Order</h6>
                                    <p class="mb-0">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card mb-4">
                        <div class="card-header bg-white text-start">
                            <h6 class="mb-0"><i class="bi bi-bag"></i> Produk Dipesan</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td class="text-start">
                                                <div class="d-flex align-items-center">
                                                    @if($item->product->image_url)
                                                        <img src="{{ asset($item->product->image_url) }}" 
                                                             alt="{{ $item->product->name }}" 
                                                             class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                    @endif
                                                    <div>
                                                        <strong>{{ $item->product->name }}</strong>
                                                        <small class="d-block text-muted">{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end align-middle">
                                                <strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pricing Summary -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ongkos Kirim</span>
                                <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong class="fs-5">Total Pembayaran</strong>
                                <strong class="fs-5 text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>

                    @if($order->payment_method === 'manual_transfer')
                        <div class="alert alert-info text-start">
                            <h6><i class="bi bi-info-circle"></i> Instruksi Pembayaran</h6>
                            <p class="mb-2">Silakan transfer ke salah satu rekening berikut:</p>
                            <ul class="mb-2">
                                <li><strong>BCA:</strong> 1234567890 a.n PT Rasa Group Indonesia</li>
                                <li><strong>Mandiri:</strong> 0987654321 a.n PT Rasa Group Indonesia</li>
                                <li><strong>BNI:</strong> 5678901234 a.n PT Rasa Group Indonesia</li>
                            </ul>
                            <p class="mb-0 small">Konfirmasi pembayaran dengan mengirim bukti transfer ke WhatsApp kami.</p>
                        </div>
                    @else
                        <div class="alert alert-success text-start">
                            <h6><i class="bi bi-check-circle"></i> Pembayaran COD</h6>
                            <p class="mb-0">Siapkan uang tunai sebesar <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong> saat barang diantar.</p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('buyer.orders.show', $order) }}" class="btn btn-primary btn-lg me-2">
                            <i class="bi bi-eye"></i> Lihat Detail Pesanan
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-shop"></i> Lanjut Belanja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
