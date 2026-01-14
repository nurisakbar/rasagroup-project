@extends('layouts.distributor')

@section('title', 'Pesanan Berhasil')
@section('page-title', 'Pesanan Berhasil')
@section('page-description', 'Terima kasih atas pesanan Anda')

@section('breadcrumb')
    <li><a href="{{ route('distributor.orders.products') }}">Order Produk</a></li>
    <li class="active">Sukses</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-success">
                <div class="box-body text-center" style="padding: 40px;">
                    <i class="fa fa-check-circle text-green" style="font-size: 80px;"></i>
                    <h2 style="color: #00a65a;">Pesanan Berhasil Dibuat!</h2>
                    <p class="text-muted">Nomor Pesanan: <strong>{{ $order->order_number }}</strong></p>
                    
                    <div class="alert alert-warning" style="max-width: 400px; margin: 20px auto;">
                        <i class="fa fa-star"></i> Anda akan mendapatkan <strong>{{ number_format($order->points_earned, 0, ',', '.') }} poin</strong> setelah pesanan ini selesai!
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-file-text-o"></i> Detail Pesanan</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Informasi Pesanan</h4>
                            <table class="table table-bordered table-condensed">
                                <tr>
                                    <th>No. Pesanan</th>
                                    <td>{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td>{{ $order->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td><span class="label label-warning">{{ ucfirst($order->order_status) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Pembayaran</th>
                                    <td>{{ $order->payment_method == 'transfer' ? 'Transfer Bank' : 'COD' }}</td>
                                </tr>
                                <tr>
                                    <th>Ekspedisi</th>
                                    <td>{{ $order->expedition->name ?? '-' }} ({{ $order->expedition_service }})</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4>Alamat Pengiriman</h4>
                            <div style="background: #f9f9f9; padding: 15px; border-radius: 5px;">
                                {!! nl2br(e($order->shipping_address)) !!}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h4>Item Pesanan</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center" style="width: 100px;">Qty</th>
                                <th class="text-right" style="width: 150px;">Harga</th>
                                <th class="text-right" style="width: 150px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'Produk tidak tersedia' }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right">Subtotal</td>
                                <td class="text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right">Ongkos Kirim</td>
                                <td class="text-right">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                            </tr>
                            <tr style="font-weight: bold; font-size: 16px;">
                                <td colspan="3" class="text-right">Total</td>
                                <td class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="bg-yellow">
                                <td colspan="3" class="text-right"><i class="fa fa-star"></i> Poin Didapat</td>
                                <td class="text-right"><strong>+{{ number_format($order->points_earned, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="box-footer">
                    <a href="{{ route('distributor.orders.history') }}" class="btn btn-primary">
                        <i class="fa fa-list"></i> Lihat Riwayat Pesanan
                    </a>
                    <a href="{{ route('distributor.orders.products') }}" class="btn btn-default">
                        <i class="fa fa-shopping-cart"></i> Order Lagi
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

