@extends('layouts.distributor')

@section('title', 'Riwayat Pesanan')
@section('page-title', 'Riwayat Pesanan')
@section('page-description', 'Semua pesanan Anda')

@section('breadcrumb')
    <li class="active">Riwayat Pesanan</li>
@endsection

@section('content')
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-history"></i> Riwayat Pesanan</h3>
            <div class="box-tools pull-right">
                <a href="{{ route('distributor.orders.products') }}" class="btn btn-warning btn-sm">
                    <i class="fa fa-plus"></i> Order Baru
                </a>
            </div>
        </div>
        <div class="box-body">
            <!-- Filter -->
            <form action="{{ route('distributor.orders.history') }}" method="GET" class="form-inline" style="margin-bottom: 20px;">
                <div class="form-group">
                    <select name="status" class="form-control">
                        <option value="">-- Semua Status --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-default">
                    <i class="fa fa-filter"></i> Filter
                </button>
                @if(request('status'))
                    <a href="{{ route('distributor.orders.history') }}" class="btn btn-default">
                        <i class="fa fa-times"></i> Reset
                    </a>
                @endif
            </form>

            @if($orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Tanggal</th>
                                <th>Item</th>
                                <th>Total</th>
                                <th>Poin</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <strong>{{ $order->order_number }}</strong><br>
                                        <small class="text-muted">{{ $order->expedition->name ?? '-' }}</small>
                                    </td>
                                    <td>{{ $order->created_at->format('d M Y') }}<br>
                                        <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>{{ $order->items->sum('quantity') }} item</td>
                                    <td><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
                                    <td>
                                        @if($order->points_credited)
                                            <span class="text-green"><i class="fa fa-check"></i> +{{ number_format($order->points_earned, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">+{{ number_format($order->points_earned, 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = [
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'shipped' => 'primary',
                                                'delivered' => 'success',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                            ][$order->order_status] ?? 'default';
                                        @endphp
                                        <span class="label label-{{ $statusClass }}">{{ ucfirst($order->order_status) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('distributor.orders.show', $order) }}" class="btn btn-info btn-xs">
                                            <i class="fa fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="text-center">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center" style="padding: 50px;">
                    <i class="fa fa-inbox fa-3x text-muted"></i>
                    <p class="text-muted" style="margin-top: 15px;">Belum ada pesanan</p>
                    <a href="{{ route('distributor.orders.products') }}" class="btn btn-warning">
                        <i class="fa fa-shopping-cart"></i> Mulai Order
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

