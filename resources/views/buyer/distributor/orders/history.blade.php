@extends('layouts.shop')

@section('title', 'Riwayat Order Produk')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Riwayat Order Produk
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
                                    <h3 class="mb-0">Riwayat Order Produk ke Pusat</h3>
                                    <p class="text-muted font-sm">Daftar pesanan restok yang Anda buat ke pusat.</p>
                                </div>
                                <div class="card-body p-4">
                                    <div class="table-responsive">
                                        <table class="table table-clean font-sm">
                                            <thead>
                                                <tr class="main-heading">
                                                    <th class="pl-10">Tanggal</th>
                                                    <th>No. Pesanan</th>
                                                    <th>Status</th>
                                                    <th>Total</th>
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
                                                        <td class="text-end pr-10">
                                                            <a href="{{ route('distributor.orders.show', $order) }}" class="btn-small d-block">Detail</a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat pesanan.</td>
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
