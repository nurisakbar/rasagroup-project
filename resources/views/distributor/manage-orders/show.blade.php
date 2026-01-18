@extends('layouts.distributor')

@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')
@section('page-description', 'Detail informasi pesanan')

@section('breadcrumb')
    <li><a href="{{ route('distributor.dashboard') }}">Dashboard</a></li>
    <li><a href="{{ route('distributor.manage-orders.index') }}">Kelola Pesanan</a></li>
    <li class="active">Detail</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-file-text-o"></i> Informasi Pesanan</h3>
                    @if($order->order_type === 'pos')
                        <span class="label label-info pull-right"><i class="fa fa-cash-register"></i> OFFLINE (POS)</span>
                    @elseif($order->order_type === 'distributor')
                        <span class="label label-warning pull-right">ORDER DISTRIBUTOR</span>
                    @else
                        <span class="label label-primary pull-right"><i class="fa fa-globe"></i> ONLINE</span>
                    @endif
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">No. Pesanan</th>
                            <td><strong>{{ $order->order_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Pembeli</th>
                            <td>
                                {{ $order->user->name }} ({{ $order->user->email }})
                                @if($order->user->phone)
                                    <br><small class="text-muted"><i class="fa fa-phone"></i> {{ $order->user->phone }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pesanan</th>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Tipe Order</th>
                            <td>
                                @if($order->order_type === 'pos')
                                    <span class="label label-info"><i class="fa fa-cash-register"></i> Offline (POS)</span>
                                @elseif($order->order_type === 'distributor')
                                    <span class="label label-warning">Distributor</span>
                                @else
                                    <span class="label label-primary"><i class="fa fa-globe"></i> Online</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Metode Pembayaran</th>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                        </tr>
                        <tr>
                            <th>Status Pembayaran</th>
                            <td>
                                <span class="label {{ $order->payment_status === 'paid' ? 'label-success' : 'label-warning' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                        </tr>
                        @if($order->notes)
                        <tr>
                            <th>Catatan</th>
                            <td>{{ $order->notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="box box-info">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-truck"></i> Informasi Pengiriman</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Alamat Pengiriman</th>
                            <td>{!! nl2br(e($order->shipping_address)) !!}</td>
                        </tr>
                        <tr>
                            <th>Ekspedisi</th>
                            <td>
                                @if($order->expedition)
                                    <strong>{{ $order->expedition->name }}</strong>
                                    @if($order->expedition_service)
                                        <br><small class="text-muted">Layanan: {{ $order->expedition_service }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Tidak tersedia</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Nomor Resi</th>
                            <td>
                                @if($order->tracking_number)
                                    <strong style="font-size: 16px; letter-spacing: 1px;">{{ $order->tracking_number }}</strong>
                                    @if($order->shipped_at)
                                        <br><small class="text-muted">Dikirim: {{ $order->shipped_at->format('d M Y H:i') }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Belum diisi</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Ongkos Kirim</th>
                            <td>Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @if($order->sourceWarehouse)
                        <tr class="bg-info">
                            <th><i class="fa fa-building"></i> Hub Pengirim</th>
                            <td>
                                <strong>{{ $order->sourceWarehouse->name }}</strong>
                                <br><small class="text-muted">{{ $order->sourceWarehouse->full_location }}</small>
                                @if($order->sourceWarehouse->phone)
                                    <br><small class="text-muted"><i class="fa fa-phone"></i> {{ $order->sourceWarehouse->phone }}</small>
                                @endif
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Item Pesanan</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-right">Harga</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->display_name ?? 'Produk tidak tersedia' }}</strong>
                                        @if($item->product && $item->product->code)
                                            <br><small class="text-muted">Kode: {{ $item->product->code }}</small>
                                        @endif
                                        @if($item->product && $item->product->brand)
                                            <br><small class="text-info"><i class="fa fa-bookmark"></i> {{ $item->product->brand->name }}</small>
                                        @endif
                                    </td>
                                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Subtotal:</th>
                                <td class="text-right">Rp {{ number_format($order->subtotal ?? $order->total_amount - ($order->shipping_cost ?? 0), 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-right">Ongkos Kirim:</th>
                                <td class="text-right">Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr style="font-size: 16px;">
                                <th colspan="3" class="text-right">Total:</th>
                                <th class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Update Order Information -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-edit"></i> Update Pesanan</h3>
                </div>
                <div class="box-body">
                    <form action="{{ route('distributor.manage-orders.update', $order) }}" method="POST" id="updateOrderForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Current Status Display -->
                        <div class="form-group">
                            <label>Status Pesanan Saat Ini</label>
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
                            <div class="text-center" style="margin-bottom: 10px;">
                                <span class="label label-{{ $statusClass }}" style="font-size: 14px; padding: 8px 15px;">
                                    {{ strtoupper($order->order_status) }}
                                </span>
                            </div>
                            <select name="order_status" id="order_status" class="form-control">
                                <option value="">-- Pilih Status Baru (Opsional) --</option>
                                <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->order_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="completed" {{ $order->order_status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <hr>

                        <!-- Tracking Number -->
                        <div class="form-group">
                            <label for="tracking_number">Nomor Resi Pengiriman</label>
                            @if($order->tracking_number)
                                <div class="callout callout-info" style="margin-bottom: 10px; padding: 10px;">
                                    <strong>Resi Saat Ini:</strong> {{ $order->tracking_number }}
                                    @if($order->expedition)
                                        <br><small>{{ $order->expedition->name }}</small>
                                    @endif
                                </div>
                            @endif
                            <input type="text" class="form-control" id="tracking_number" name="tracking_number" 
                                   value="{{ $order->tracking_number }}" 
                                   placeholder="Contoh: JNE12345678">
                            <p class="help-block">
                                @if($order->expedition)
                                    Ekspedisi: <strong>{{ $order->expedition->name }}</strong>
                                @else
                                    <span class="text-warning">Ekspedisi belum dipilih</span>
                                @endif
                            </p>
                            @if(!$order->tracking_number)
                                <small class="text-info">
                                    <i class="fa fa-info-circle"></i> Mengisi nomor resi akan otomatis mengubah status menjadi "Shipped" jika masih pending/processing
                                </small>
                            @endif
                        </div>

                        <hr>

                        <!-- Payment Status -->
                        <div class="form-group">
                            <label>Status Pembayaran Saat Ini</label>
                            @php
                                $paymentClass = [
                                    'pending' => 'warning',
                                    'paid' => 'success',
                                    'failed' => 'danger',
                                    'refunded' => 'info',
                                ][$order->payment_status] ?? 'default';
                            @endphp
                            <div class="text-center" style="margin-bottom: 10px;">
                                <span class="label label-{{ $paymentClass }}" style="font-size: 14px; padding: 8px 15px;">
                                    {{ strtoupper($order->payment_status) }}
                                </span>
                                <p class="text-muted" style="margin-top: 5px; margin-bottom: 0;">
                                    <i class="fa fa-{{ $order->payment_method == 'transfer' ? 'bank' : 'money' }}"></i>
                                    {{ $order->payment_method == 'transfer' ? 'Transfer Bank' : ($order->payment_method == 'cod' ? 'COD (Bayar di Tempat)' : ucfirst($order->payment_method)) }}
                                </p>
                            </div>
                            <select name="payment_status" id="payment_status" class="form-control">
                                <option value="">-- Pilih Status Pembayaran Baru (Opsional) --</option>
                                <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                                <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed (Gagal)</option>
                                <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded (Dikembalikan)</option>
                            </select>
                        </div>

                        <hr>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            <i class="fa fa-save"></i> Simpan Perubahan
                        </button>
                        <p class="text-muted text-center" style="margin-top: 10px; font-size: 12px;">
                            <i class="fa fa-info-circle"></i> Hanya field yang diubah yang akan diperbarui
                        </p>
                    </form>
                </div>
            </div>

            <!-- Convert to Stock -->
            @if(in_array($order->order_status, ['delivered', 'completed']))
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
                                        <strong>{{ $item->product->display_name }}</strong> - <strong>{{ $item->quantity }} unit</strong>
                                        @php
                                            $currentStock = \App\Models\WarehouseStock::where('warehouse_id', $warehouse->id)
                                                ->where('product_id', $item->product_id)
                                                ->first();
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
                    <form action="{{ route('distributor.manage-orders.convert-to-stock', $order) }}" method="POST" onsubmit="return confirm('Konversi semua item dari pesanan ini ke stock warehouse?\n\nItem yang sudah ada di stock akan ditambahkan, item baru akan dibuat.');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block btn-lg">
                            <i class="fa fa-cubes"></i> Konversi ke Stock
                        </button>
                    </form>
                </div>
            </div>
            @else
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
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('distributor.manage-orders.index') }}" class="btn btn-default">Kembali</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Store original values
    var originalOrderStatus = $('#order_status').val();
    var originalTrackingNumber = $('#tracking_number').val();
    var originalPaymentStatus = $('#payment_status').val();
    
    $('#updateOrderForm').on('submit', function(e) {
        var orderStatus = $('#order_status').val();
        var trackingNumber = $('#tracking_number').val();
        var paymentStatus = $('#payment_status').val();
        
        // Check if at least one field has changed
        var hasChanges = false;
        
        if (orderStatus && orderStatus !== originalOrderStatus) {
            hasChanges = true;
        }
        
        if (trackingNumber !== originalTrackingNumber) {
            hasChanges = true;
        }
        
        if (paymentStatus && paymentStatus !== originalPaymentStatus) {
            hasChanges = true;
        }
        
        if (!hasChanges) {
            e.preventDefault();
            alert('Tidak ada perubahan yang dilakukan. Silakan ubah setidaknya satu field sebelum menyimpan.');
            return false;
        }
    });
});
</script>
@endpush

