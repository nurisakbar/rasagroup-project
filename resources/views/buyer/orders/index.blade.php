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
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-orders">
                                    @csrf
                                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-orders').submit();">
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
                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <h3 class="mb-0">Pesanan Saya</h3>
                                </div>
                                <div class="card-body p-4 pt-0">
                                    @if($orders->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-clean font-sm">
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
                                                    @foreach($orders as $order)
                                                        <tr>
                                                            <td>
                                                                <strong class="text-brand">#{{ $order->order_number }}</strong>
                                                            </td>
                                                            <td class="text-muted">
                                                                {{ $order->created_at->format('d M Y, H:i') }}
                                                            </td>
                                                            <td>
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
                                                                <span class="badge rounded-pill {{ $statusClass }} text-white font-xs">{{ $statusLabel }}</span>
                                                            </td>
                                                            <td class="product-price">
                                                                <strong class="text-brand">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                                                            </td>
                                                            <td class="text-end">
                                                                <a href="{{ route('buyer.orders.show', $order) }}" class="btn-small d-block">
                                                                    Detail
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="pagination-area mt-30 mb-50">
                                            {{ $orders->links() }}
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <div class="mb-4">
                                                <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-cart.svg') }}" alt="Empty" style="width: 80px; opacity: 0.5;">
                                            </div>
                                            <h4 class="mb-3 text-muted">Belum ada pesanan</h4>
                                            <p class="text-muted mb-4 font-sm">Anda belum melakukan transaksi apapun saat ini.</p>
                                            <a href="{{ route('products.index') }}" class="btn btn-fill-out"><i class="fi-rs-shopping-bag mr-10"></i>Mulai Belanja</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-clean thead th { border-top: 0; border-bottom-width: 1px; color: #253D4E; font-weight: 700; }
    .table-clean tbody td { vertical-align: middle; padding: 15px 0; }
</style>
@endsection

