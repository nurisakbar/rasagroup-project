@extends('layouts.distributor')

@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')
@section('page-description', $order->order_number)

@section('breadcrumb')
    <li><a href="{{ route('distributor.orders.history') }}">Riwayat Pesanan</a></li>
    <li class="active">Detail</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <!-- Order Info -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-file-text-o"></i> Informasi Pesanan</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-condensed">
                                <tr>
                                    <th style="width: 150px;">No. Pesanan</th>
                                    <td><strong>{{ $order->order_number }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td>{{ $order->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
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
                                </tr>
                                <tr>
                                    <th>Pembayaran</th>
                                    <td>
                                        {{ $order->payment_method == 'transfer' ? 'Transfer Bank' : 'COD' }}
                                        <span class="label label-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-condensed">
                                <tr>
                                    <th style="width: 150px;">Ekspedisi</th>
                                    <td>{{ $order->expedition->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Layanan</th>
                                    <td>{{ $order->expedition_service }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-map-marker"></i> Alamat Pengiriman</h3>
                </div>
                <div class="box-body">
                    <div style="background: #f9f9f9; padding: 15px; border-radius: 5px;">
                        {!! nl2br(e($order->shipping_address)) !!}
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Item Pesanan</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Gambar</th>
                                <th>Produk</th>
                                <th class="text-center" style="width: 80px;">Qty</th>
                                <th class="text-right" style="width: 120px;">Harga</th>
                                <th class="text-right" style="width: 120px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        @if($item->product && $item->product->image)
                                            <img src="{{ $item->product->image_url }}" alt="" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div style="width: 60px; height: 60px; background: #f4f4f4; display: flex; align-items: center; justify-content: center;">
                                                <i class="fa fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $item->product->name ?? 'Produk tidak tersedia' }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Payment Summary -->
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-calculator"></i> Ringkasan Pembayaran</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed">
                        <tr>
                            <td>Subtotal</td>
                            <td class="text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Ongkos Kirim</td>
                            <td class="text-right">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                        </tr>
                        <tr style="font-size: 18px; font-weight: bold;">
                            <td>Total</td>
                            <td class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            @if($order->notes)
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-comment"></i> Catatan</h3>
                    </div>
                    <div class="box-body">
                        <p>{{ $order->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Convert to Stock -->
            @if(in_array($order->order_status, ['delivered', 'completed']) && $warehouse)
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cubes"></i> Konversi ke Stock</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">
                        <i class="fa fa-info-circle"></i> Konversi semua item dari pesanan ini ke stock warehouse. 
                        Item yang sudah ada di stock akan ditambahkan, item baru akan dibuat dengan stock sesuai quantity.
                    </p>
                    <div class="alert alert-info">
                        <strong>Item yang akan dikonversi:</strong>
                        <ul style="margin-bottom: 0; margin-top: 10px;">
                            @foreach($order->items as $item)
                                @if($item->product)
                                    <li>
                                        <strong>{{ $item->product->name }}</strong> - <strong>{{ $item->quantity }} unit</strong>
                                        @php
                                            $currentStock = null;
                                            if ($warehouse) {
                                                $currentStock = \App\Models\WarehouseStock::where('warehouse_id', $warehouse->id)
                                                    ->where('product_id', $item->product_id)
                                                    ->first();
                                            }
                                        @endphp
                                        @if($currentStock)
                                            <br><small class="text-muted">Stock saat ini: {{ $currentStock->stock }} unit → Akan menjadi: {{ $currentStock->stock + $item->quantity }} unit</small>
                                        @else
                                            <br><small class="text-muted">Stock baru: {{ $item->quantity }} unit</small>
                                        @endif
                                    </li>
                                @else
                                    <li class="text-danger">Produk tidak tersedia (akan dilewati)</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    <form action="{{ route('distributor.orders.convert-to-stock', $order) }}" method="POST" onsubmit="return confirm('Konversi semua item dari pesanan ini ke stock warehouse?\n\nItem yang sudah ada di stock akan ditambahkan, item baru akan dibuat.');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block btn-lg">
                            <i class="fa fa-cubes"></i> Konversi ke Stock
                        </button>
                    </form>
                </div>
            </div>
            @elseif(!in_array($order->order_status, ['delivered', 'completed']))
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">
                        <i class="fa fa-info-circle"></i> Fitur konversi ke stock hanya tersedia untuk pesanan yang sudah berstatus <strong>Delivered</strong> atau <strong>Completed</strong>.
                    </p>
                    <p class="text-muted">
                        Status pesanan saat ini: <strong>{{ ucfirst($order->order_status) }}</strong>
                    </p>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="box">
                <div class="box-body">
                    <a href="{{ route('distributor.orders.history') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> Kembali ke Riwayat
                    </a>
                    <a href="{{ route('distributor.orders.products') }}" class="btn btn-warning btn-block">
                        <i class="fa fa-shopping-cart"></i> Order Lagi
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

