@extends('layouts.shop')

@section('title', 'Detail Pesanan Masuk')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('distributor.manage-orders.index') }}">Kelola Pesanan Masuk</a>
            <span></span> Detail
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
                            <div class="card border-0 shadow-sm border-radius-10 overflow-hidden">
                                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0">Pesanan #{{ $order->order_number }}</h3>
                                        <p class="text-muted font-sm mb-0">Ditempatkan pada {{ $order->created_at->format('d M Y H:i') }}</p>
                                    </div>
                                    <div>
                                        @if($order->order_type === 'pos')
                                            <span class="badge bg-info">OFFLINE (POS)</span>
                                        @elseif($order->order_type === 'distributor')
                                            <span class="badge bg-warning">DISTRIBUTOR</span>
                                        @else
                                            <span class="badge bg-primary">ONLINE</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <!-- Left Column: Order Content -->
                                        <div class="col-md-7">
                                            <div class="mb-4">
                                                <h5 class="mb-3">Informasi Pembeli</h5>
                                                <div class="p-3 bg-light border-radius-10 font-sm">
                                                    <p class="mb-1"><strong>Nama:</strong> {{ $order->user->name }}</p>
                                                    <p class="mb-1"><strong>Email:</strong> {{ $order->user->email }}</p>
                                                    <p class="mb-0"><strong>Telepon:</strong> {{ $order->user->phone ?? '-' }}</p>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <h5 class="mb-3">Informasi Pengiriman</h5>
                                                <div class="p-3 bg-light border-radius-10 font-sm">
                                                    <p class="mb-2"><strong>Alamat:</strong><br>{!! nl2br(e($order->shipping_address)) !!}</p>
                                                    <p class="mb-1"><strong>Ekspedisi:</strong> {{ $order->expedition->name ?? '-' }} ({{ $order->expedition_service ?? '-' }})</p>
                                                    <p class="mb-0"><strong>No. Resi:</strong> <span class="text-brand fw-bold">{{ $order->tracking_number ?? 'Belum tersedia' }}</span></p>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <h5 class="mb-3">Item Pesanan</h5>
                                                <div class="table-responsive">
                                                    <table class="table font-xs">
                                                        <thead>
                                                            <tr>
                                                                <th>Produk</th>
                                                                <th>Harga</th>
                                                                <th class="text-center">Jumlah</th>
                                                                <th class="text-end">Subtotal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($order->items as $item)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $item->product->display_name ?? 'Produk dihapus' }}</strong>
                                                                </td>
                                                                <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                                                <td class="text-center">{{ $item->quantity }}</td>
                                                                <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot class="fw-bold">
                                                            <tr>
                                                                <td colspan="3" class="text-end">Subtotal:</td>
                                                                <td class="text-end">Rp {{ number_format($order->subtotal ?? $order->total_amount - ($order->shipping_cost ?? 0), 0, ',', '.') }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3" class="text-end">Ongkos Kirim:</td>
                                                                <td class="text-end">Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</td>
                                                            </tr>
                                                            <tr class="text-brand fs-6">
                                                                <td colspan="3" class="text-end">Total:</td>
                                                                <td class="text-end">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Column: Actions -->
                                        <div class="col-md-5">
                                            <div class="card border border-radius-10 shadow-none">
                                                <div class="card-body">
                                                    <h5 class="mb-4">Kelola Status</h5>
                                                    <form action="{{ route('distributor.manage-orders.update', $order) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label font-sm">Status Pesanan</label>
                                                            <select name="order_status" class="form-select font-sm">
                                                                <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing</option>
                                                                <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                                                <option value="delivered" {{ $order->order_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                                <option value="completed" {{ $order->order_status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                                <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label font-sm">Nomor Resi</label>
                                                            <input type="text" name="tracking_number" class="form-control font-sm" value="{{ $order->tracking_number }}" placeholder="Contoh: JNE12345678">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label font-sm">Status Pembayaran</label>
                                                            <select name="payment_status" class="form-select font-sm">
                                                                <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                                                <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                                                                <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                                            </select>
                                                        </div>

                                                        <button type="submit" class="btn btn-sm btn-fill-out w-100 mt-2">Update Pesanan</button>
                                                    </form>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top p-4">
                                    <a href="{{ route('distributor.manage-orders.index') }}" class="btn btn-sm btn-secondary rounded-pill px-4">Kembali</a>
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
