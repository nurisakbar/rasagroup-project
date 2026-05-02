@extends('layouts.warehouse')

@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')
@section('page-description', 'Detail informasi pesanan')

@section('breadcrumb')
    <li><a href="{{ route('warehouse.dashboard') }}">Dashboard</a></li>
    <li><a href="{{ route('warehouse.orders.index') }}">Laporan Pemesanan</a></li>
    <li class="active">Detail</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-file-text-o"></i> Informasi Pesanan</h3>
                    @if($order->order_type === 'distributor')
                        <span class="label label-warning pull-right">ORDER DISTRIBUTOR</span>
                    @endif
                    <div class="pull-right" style="margin-right: 10px;">
                        <form action="{{ route('warehouse.orders.sync-qad', $order) }}" method="POST" style="display: inline;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Menunggu...';">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-success" title="Sinkronkan ke QID / QAD" onclick="return confirm('Sinkronkan pesanan ini ke QID/QAD? Proses akan berjalan di background.')">
                                <i class="fa fa-refresh"></i> Sinkron QID
                            </button>
                        </form>
                    </div>
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
                        <tr>
                            <th>Status Pesanan</th>
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
                                <span class="label label-{{ $statusClass }}">
                                    {{ ucfirst($order->order_status) }}
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

            <!-- QAD / QID Information -->
            <div class="box box-success">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-exchange"></i> Informasi QID / QAD (ERP)</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">QAD Customer Code</th>
                            <td>
                                @if($order->user->qad_customer_code)
                                    <span class="label label-success" style="font-size: 14px;">{{ $order->user->qad_customer_code }}</span>
                                @else
                                    <span class="text-muted">Belum tersinkron</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>No. Sales Order QID</th>
                            <td>
                                @if($order->qid_sales_order_number)
                                    <span class="label label-success" style="font-size: 14px;">{{ $order->qid_sales_order_number }}</span>
                                @else
                                    <span class="text-muted">Belum tersinkron</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>QAD Sales Order No.</th>
                            <td>
                                @if($order->qad_so_number)
                                    <span class="label label-success" style="font-size: 14px;">{{ $order->qad_so_number }}</span>
                                @else
                                    <span class="text-muted">Belum tersinkron</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @php
                $canWarehouseDebugPickup = $order->expedition
                    && $order->expedition->code === 'lion_parcel'
                    && $order->ekspedisiku_shipment_id;
            @endphp
            <!-- Debug QID / EkspedisiKu (warehouse) -->
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bug"></i> Debug integrasi</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted small" style="margin-bottom: 12px;">
                        Menampilkan <strong>endpoint</strong>, <strong>payload</strong>, dan <strong>response</strong>.
                        Secara default hanya menyusun payload (dry run); centang untuk memanggil API sungguhan.
                    </p>
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label class="checkbox-inline" style="font-weight: normal;">
                            <input type="checkbox" id="warehouseDebugQidExecute"> Jalankan POST create sales order ke QID
                        </label>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" id="warehouseBtnDebugQidSo">
                        <i class="fa fa-code"></i> Debug Sales Order (QID)
                    </button>
                    <hr style="margin: 12px 0;">
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label class="checkbox-inline" style="font-weight: normal;">
                            <input type="checkbox" id="warehouseDebugPickupExecute" @if(!$canWarehouseDebugPickup) disabled @endif> Jalankan POST request pickup (EkspedisiKu)
                        </label>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" id="warehouseBtnDebugPickup" @if(!$canWarehouseDebugPickup) disabled title="Perlu Lion Parcel + shipment_id" @endif>
                        <i class="fa fa-truck"></i> Debug Request Pickup
                    </button>
                    @if(!$canWarehouseDebugPickup)
                        <p class="text-muted small" style="margin-top: 8px; margin-bottom: 0;">Pickup debug aktif setelah booking Lion sukses (ada <code>shipment_id</code>).</p>
                    @endif
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
                                    @if($order->expedition && $order->expedition->code === 'lion_parcel')
                                        @if(!$order->ekspedisiku_shipment_id)
                                            <span class="label label-danger" style="margin-left: 10px;">shipment_id belum tersimpan</span>
                                            <form action="{{ route('warehouse.orders.ekspedisiku-reset-booking', $order) }}" method="POST" style="display: inline; margin-left: 10px;">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Reset booking/resi ini? Resi akan dikosongkan agar bisa booking ulang.')">
                                                    <i class="fa fa-trash"></i> Reset Booking
                                                </button>
                                            </form>
                                        @endif
                                        @if($order->ekspedisiku_pickup_status === 'success')
                                            <span class="label label-success" style="margin-left: 10px;">
                                                Pickup requested{{ $order->ekspedisiku_pickup_requested_at ? ' @ '.$order->ekspedisiku_pickup_requested_at->format('d M Y H:i') : '' }}
                                            </span>
                                            <form action="{{ route('warehouse.orders.cancel-pickup', $order) }}" method="POST" style="display: inline; margin-left: 10px;">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Cancel request pickup untuk shipment ini?')">
                                                    <i class="fa fa-ban"></i> Cancel Pickup
                                                </button>
                                            </form>
                                        @elseif($order->ekspedisiku_pickup_status === 'cancelled')
                                            <span class="label label-default" style="margin-left: 10px;">
                                                Pickup cancelled{{ $order->ekspedisiku_pickup_requested_at ? ' @ '.$order->ekspedisiku_pickup_requested_at->format('d M Y H:i') : '' }}
                                            </span>
                                        @elseif($order->ekspedisiku_pickup_status === 'cancel_failed')
                                            <span class="label label-danger" style="margin-left: 10px;">
                                                Cancel pickup gagal
                                            </span>
                                            <form action="{{ route('warehouse.orders.cancel-pickup', $order) }}" method="POST" style="display: inline; margin-left: 10px;">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Retry cancel pickup untuk shipment ini?')">
                                                    <i class="fa fa-ban"></i> Cancel Pickup
                                                </button>
                                            </form>
                                            @if($order->ekspedisiku_pickup_last_error)
                                                <br><small class="text-danger">Cancel pickup gagal: {{ $order->ekspedisiku_pickup_last_error }}</small>
                                            @endif
                                        @else
                                            <form action="{{ route('warehouse.orders.request-pickup', $order) }}" method="POST" style="display: inline; margin-left: 10px;">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-warning" onclick="return confirm('Kirim request pickup ke Lion Parcel?')">
                                                    <i class="fa fa-truck"></i> Request Pickup
                                                </button>
                                            </form>
                                            @if($order->ekspedisiku_pickup_status === 'failed')
                                                <br><small class="text-danger">Pickup gagal: {{ $order->ekspedisiku_pickup_last_error }}</small>
                                            @endif
                                        @endif
                                    @endif
                                    @if($order->shipped_at)
                                        <br><small class="text-muted">Dikirim: {{ $order->shipped_at->format('d M Y H:i') }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Belum diisi</span>
                                    @if($order->expedition && $order->expedition->code === 'lion_parcel')
                                        <form action="{{ route('warehouse.orders.ekspedisiku-booking', $order) }}" method="POST" style="display: inline; margin-left: 10px;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Buat booking di EkspedisiKu?')">
                                                <i class="fa fa-plus"></i> Buat Booking (EkspedisiKu)
                                            </button>
                                        </form>
                                    @endif
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
                            @if($order->order_type === 'distributor')
                            <tr class="bg-yellow">
                                <th colspan="3" class="text-right"><i class="fa fa-star"></i> Poin Didapat:</th>
                                <td class="text-right">
                                    <strong>+{{ number_format($order->points_earned ?? 0, 0, ',', '.') }}</strong>
                                    @if($order->points_credited)
                                        <span class="label label-success">Dikreditkan</span>
                                    @else
                                        <span class="label label-default">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Warehouse Info -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-building"></i> Informasi Warehouse</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed">
                        <tr>
                            <th width="40%">Nama</th>
                            <td><strong>{{ $warehouse->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Lokasi</th>
                            <td>{{ $warehouse->full_location }}</td>
                        </tr>
                        @if($warehouse->address)
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $warehouse->address }}</td>
                        </tr>
                        @endif
                        @if($warehouse->phone)
                        <tr>
                            <th>Telepon</th>
                            <td>{{ $warehouse->phone }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($warehouse->is_active)
                                    <span class="label label-success">Aktif</span>
                                @else
                                    <span class="label label-danger">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Update Order Form -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-edit"></i> Kelola Pesanan</h3>
                </div>
                <div class="box-body">
                    <form action="{{ route('warehouse.orders.update', $order) }}" method="POST" id="updateOrderForm">
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
                            <p class="help-block">
                                <small class="text-muted">
                                    <i class="fa fa-info-circle"></i> Ubah status sesuai progress pesanan
                                </small>
                            </p>
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
                            <p class="help-block">
                                <small class="text-muted">
                                    <i class="fa fa-info-circle"></i> Update status pembayaran setelah konfirmasi
                                </small>
                            </p>
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

            <!-- Order Info -->
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">
                        <i class="fa fa-info-circle"></i> Pesanan ini masuk ke warehouse Anda untuk diproses dan dikirim.
                    </p>
                    @if($order->order_status === 'pending')
                        <div class="alert alert-warning">
                            <i class="fa fa-clock-o"></i> Pesanan masih <strong>Pending</strong>. Tunggu konfirmasi pembayaran.
                        </div>
                    @elseif($order->order_status === 'processing')
                        <div class="alert alert-info">
                            <i class="fa fa-cog"></i> Pesanan sedang <strong>Processing</strong>. Siapkan barang untuk pengiriman.
                        </div>
                    @elseif($order->order_status === 'shipped')
                        <div class="alert alert-success">
                            <i class="fa fa-truck"></i> Pesanan sudah <strong>Shipped</strong>. Barang sudah dikirim.
                        </div>
                    @elseif($order->order_status === 'delivered' || $order->order_status === 'completed')
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> Pesanan sudah <strong>Delivered/Completed</strong>.
                        </div>
                    @elseif($order->order_status === 'cancelled')
                        <div class="alert alert-danger">
                            <i class="fa fa-times-circle"></i> Pesanan <strong>Cancelled</strong>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('warehouse.orders.index') }}" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Kembali ke Daftar Pesanan
            </a>
        </div>
    </div>

    <div class="modal fade" id="warehouseIntegrationDebugModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document" style="width: 92%; max-width: 1000px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-bug"></i> Hasil debug</h4>
                </div>
                <div class="modal-body">
                    <pre id="warehouseIntegrationDebugPre" style="max-height: 72vh; overflow: auto; white-space: pre-wrap; word-break: break-word; font-size: 12px; margin: 0;"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
            </div>
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

    var warehouseDebugCsrf = $('meta[name="csrf-token"]').attr('content');
    function warehouseShowDebugModal(obj) {
        $('#warehouseIntegrationDebugPre').text(JSON.stringify(obj, null, 2));
        $('#warehouseIntegrationDebugModal').modal('show');
    }
    function warehouseDebugPost(url, execute, btn) {
        var $btn = $(btn);
        $btn.prop('disabled', true);
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': warehouseDebugCsrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ execute: !!execute })
        })
        .then(function (r) {
            return r.json().then(function (data) {
                warehouseShowDebugModal({ http_status: r.status, body: data });
            });
        })
        .catch(function (e) {
            warehouseShowDebugModal({ error: String(e) });
        })
        .finally(function () {
            $btn.prop('disabled', false);
        });
    }
    $('#warehouseBtnDebugQidSo').on('click', function () {
        warehouseDebugPost(
            @json(route('warehouse.orders.debug-qid-sales-order', $order)),
            $('#warehouseDebugQidExecute').is(':checked'),
            this
        );
    });
    $('#warehouseBtnDebugPickup').on('click', function () {
        warehouseDebugPost(
            @json(route('warehouse.orders.debug-request-pickup', $order)),
            $('#warehouseDebugPickupExecute').is(':checked'),
            this
        );
    });
});
</script>
@endpush

