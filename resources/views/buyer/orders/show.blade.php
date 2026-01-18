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
@endsection








