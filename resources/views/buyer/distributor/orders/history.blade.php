@extends('layouts.shop')

@section('title', 'Riwayat Pembelian')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Riwayat Pembelian
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
                                <div class="card-header bg-white border-bottom p-4">
                                    <h3 class="mb-0">Riwayat Pembelian</h3>
                                    <p class="text-muted font-sm">Pesanan restok ke pusat dan pembelian melalui toko online (keranjang reguler).</p>
                                </div>
                                <div class="card-body p-4">
                                    <div class="mb-3">
                                        <a href="{{ route('distributor.profile') }}" class="font-sm text-brand fw-bold">
                                            <i class="fi-rs-user mr-5"></i>Profil distributor &amp; hub
                                        </a>
                                    </div>

                                    @if($monthlyTarget > 0)
                                        <div class="mb-4 p-3 border-radius-10" style="background-color: #f7fef9; border: 1px solid #3BB77E;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0 text-brand"><i class="fi-rs-chart-pie mr-5"></i> Target Belanja Bulanan ({{ now()->translatedFormat('F Y') }})</h6>
                                                <span class="font-sm fw-bold {{ $progressPercentage >= 100 ? 'text-success' : 'text-muted' }}">
                                                    {{ number_format($progressPercentage, 1) }}% Tercapai
                                                </span>
                                            </div>
                                            <div class="progress mb-2" style="height: 12px; background-color: #eee; border-radius: 6px; overflow: hidden;">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                                                    style="width: {{ $progressPercentage }}%; background-color: #3BB77E;" 
                                                    aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between font-xs text-muted">
                                                <span>Realisasi: <strong>Rp {{ number_format($totalSpentThisMonth, 0, ',', '.') }}</strong></span>
                                                <span>Target: <strong>Rp {{ number_format($monthlyTarget, 0, ',', '.') }}</strong></span>
                                            </div>
                                        </div>
                                    @endif
                                    <!-- Filter Box -->
                                    <form action="{{ route('distributor.orders.history') }}" method="GET" class="mb-4">
                                        <div class="row g-2">
                                            <div class="col-md-3">
                                                <label class="font-xs text-muted mb-1">Status</label>
                                                <select name="status" class="form-select form-select-sm">
                                                    <option value="">Semua Status</option>
                                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="font-xs text-muted mb-1">Dari</label>
                                                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="font-xs text-muted mb-1">Sampai</label>
                                                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to', now()->endOfMonth()->format('Y-m-d')) }}">
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <button type="submit" class="btn btn-sm btn-brand w-100" style="padding: 10px;">Filter</button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="table-responsive">
                                        <table class="table table-clean font-sm">
                                            <thead>
                                                <tr class="main-heading">
                                                    <th class="pl-10">Tanggal</th>
                                                    <th>No. Pesanan</th>
                                                    <th>Status Order</th>
                                                    <th>Total</th>
                                                    <th>Pembayaran</th>
                                                    <th class="text-end pr-10">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($orders as $order)
                                                    <tr>
                                                        <td class="pl-10 text-muted">{{ $order->created_at->format('d M Y') }}</td>
                                                        <td><strong class="text-heading">{{ $order->order_number }}</strong></td>
                                                        <td>
                                                            @php
                                                                $statusClass = [
                                                                    'pending' => 'bg-warning',
                                                                    'processing' => 'bg-info',
                                                                    'shipped' => 'bg-primary',
                                                                    'delivered' => 'bg-success',
                                                                    'completed' => 'bg-success',
                                                                    'cancelled' => 'bg-danger',
                                                                ][$order->order_status] ?? 'bg-secondary';
                                                            @endphp
                                                            <span class="badge {{ $statusClass }}">{{ ucfirst($order->order_status) }}</span>
                                                        </td>
                                                        <td><strong class="text-brand">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
                                                        <td>
                                                            @php
                                                                $paymentClass = [
                                                                    'pending' => 'bg-warning',
                                                                    'paid' => 'bg-success',
                                                                    'failed' => 'bg-danger',
                                                                    'refunded' => 'bg-info',
                                                                ][$order->payment_status] ?? 'bg-secondary';
                                                            @endphp
                                                            <span class="badge {{ $paymentClass }}">{{ ucfirst($order->payment_status) }}</span>
                                                        </td>
                                                        <td class="text-end pr-10">
                                                            @if($order->order_type === \App\Models\Order::TYPE_DISTRIBUTOR)
                                                                <a href="{{ route('distributor.orders.show', $order) }}" class="btn-small d-block">Detail</a>
                                                            @else
                                                                <a href="{{ route('buyer.orders.show', $order) }}" class="btn-small d-block">Detail</a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat pesanan.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-30">
                                        {{ $orders->links() }}
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
