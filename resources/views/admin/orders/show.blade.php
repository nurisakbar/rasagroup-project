@extends('layouts.admin')

@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')
@section('page-description', 'Detail informasi pesanan')

@section('breadcrumb')
    <li><a href="{{ route('admin.orders.index') }}">Pesanan</a></li>
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
                    @if($order->shouldSyncToJubelio() && \App\Support\SalesOrderSyncDispatcher::isJubelioEnabled())
                    <div class="pull-right" style="margin-right: 10px;">
                        <form action="{{ route('admin.orders.sync-qad', $order) }}" method="POST" style="display: inline;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Menunggu...';">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-success" title="Sinkronkan sales order ke Jubelio" onclick="return confirm('Sinkronkan pesanan hub ini ke Jubelio? Proses akan berjalan di background.')">
                                <i class="fa fa-refresh"></i> Sinkron Jubelio
                            </button>
                        </form>
                    </div>
                    @elseif($order->shouldSyncToQad() && \App\Support\SalesOrderSyncDispatcher::isQadEnabled())
                    <div class="pull-right" style="margin-right: 10px;">
                        <form action="{{ route('admin.orders.sync-qad', $order) }}" method="POST" style="display: inline;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Menunggu...';">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-success" title="Sinkronkan sales order ke QAD" onclick="return confirm('Sinkronkan pesanan distributor ini ke QAD? Proses akan berjalan di background.')">
                                <i class="fa fa-refresh"></i> Sinkron QAD
                            </button>
                        </form>
                    </div>
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
                        @if($order->preferred_shipping_date)
                        <tr class="bg-yellow">
                            <th><i class="fa fa-calendar"></i> Rencana Pengiriman</th>
                            <td><strong>{{ $order->preferred_shipping_date->format('d M Y') }}</strong></td>
                        </tr>
                        @endif
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
                        @if($order->payment_method === 'manual_transfer' && $order->payment_proof)
                        <tr>
                            <th>Bukti Pembayaran</th>
                            <td>
                                <a href="{{ Storage::url($order->payment_proof) }}" target="_blank" class="btn btn-sm btn-info" style="margin-bottom: 5px;">
                                    <i class="fa fa-image"></i> Lihat Bukti Transfer
                                </a>
                                @if($order->payment_submit_note)
                                    <p class="text-muted mb-0" style="margin-top: 5px;"><small><i class="fa fa-info-circle"></i> Catatan: {{ $order->payment_submit_note }}</small></p>
                                @endif
                                @if($order->payment_submitted_at)
                                    <p class="text-muted mb-0"><small><i class="fa fa-clock-o"></i> Dikirim: {{ \Carbon\Carbon::parse($order->payment_submitted_at)->format('d M Y, H:i') }}</small></p>
                                @endif
                            </td>
                        </tr>
                        @endif
                        @if($order->affiliate)
                        <tr>
                            <th>Kode Referal</th>
                            <td>
                                <span class="label label-info">{{ $order->affiliate->referral_code }}</span>
                                <br><small class="text-muted">Oleh: {{ $order->affiliate->name }}</small>
                            </td>
                        </tr>
                        @endif
                        @if($order->notes)
                        <tr>
                            <th>Catatan</th>
                            <td>{{ $order->notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- ERP: Jubelio (order hub/online) -->
            @if($order->shouldSyncToJubelio() || $order->jubelio_salesorder_id)
            <div class="box box-success">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-exchange"></i> Informasi Jubelio (ERP Hub)</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Jubelio Sales Order No.</th>
                            <td>
                                @if($order->jubelio_salesorder_no)
                                    <span class="label label-success" style="font-size: 14px;">{{ $order->jubelio_salesorder_no }}</span>
                                @elseif($order->jubelio_salesorder_id)
                                    <span class="label label-info" style="font-size: 14px;">ID {{ $order->jubelio_salesorder_id }}</span>
                                @else
                                    <span class="text-muted">Belum tersinkron</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            <!-- ERP: QAD (order distributor/POS) -->
            @if(($order->shouldSyncToQad() || $order->qad_so_number) && \App\Support\QadIntegration::isConfigured())
            <div class="box box-warning">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-exchange"></i> Informasi QAD (ERP Distributor)</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">QAD Customer Code</th>
                            <td>
                                @if($order->user->qad_customer_code)
                                    <span class="label label-default" style="font-size: 14px;">{{ $order->user->qad_customer_code }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>QAD Sales Order No.</th>
                            <td>
                                @if($order->qad_so_number)
                                    <span class="label label-default" style="font-size: 14px;">{{ $order->qad_so_number }}</span>
                                @else
                                    <span class="text-muted">Belum tersinkron</span>
                                @endif
                            </td>
                        </tr>
                        @if($order->qid_sales_order_number)
                        <tr>
                            <th>No. Sales Order QID</th>
                            <td>
                                <span class="label label-default" style="font-size: 14px;">{{ $order->qid_sales_order_number }}</span>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            @endif

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
                                @php
                                    $ekspedisikuCouriers = ['lion_parcel', 'lalamove', 'jne', 'jnt', 'sicepat', 'sap', 'ninja', 'idexpress', 'borzo'];
                                    $supportsEkspedisiBooking = $order->expedition && in_array($order->expedition->code, $ekspedisikuCouriers, true);
                                    // EkspedisiKu Pickup API currently only supports Lion Parcel
                                    $supportsPickup = $order->expedition && in_array($order->expedition->code, ['lion_parcel'], true);
                                    $isLalamove = $order->expedition && $order->expedition->code === 'lalamove';
                                @endphp

                                @if($order->tracking_number)
                                    <strong style="font-size: 16px; letter-spacing: 1px;">{{ $order->tracking_number }}</strong>
                                    @if($isLalamove)
                                        <br><small class="text-muted">Order ID Lalamove</small>
                                    @endif
                                @else
                                    <span class="text-muted">Belum diisi</span>
                                @endif
                            </td>
                        </tr>
                        @if($supportsEkspedisiBooking)
                        <tr>
                            <td colspan="2" style="padding: 15px;">
                                <div class="well well-sm" style="background-color: #f9fafc; border-left: 3px solid #00c0ef; margin-bottom: 0;">
                                    <h4 style="margin-top:0; margin-bottom: 15px; font-size: 16px;"><i class="fa fa-paper-plane-o"></i> Proses Pengiriman (EkspedisiKu)</h4>
                                    <div class="row">
                                        <!-- Step 1: Booking -->
                                        <div class="col-md-4">
                                            <div class="box box-solid {{ $order->tracking_number ? 'box-success' : 'box-primary' }}" style="border: 1px solid #d2d6de; box-shadow: none; margin-bottom: 0;">
                                                <div class="box-header with-border" style="padding: 8px;">
                                                    <h3 class="box-title" style="font-size: 13px;">1. Buat Booking</h3>
                                                </div>
                                                <div class="box-body text-center" style="padding: 10px;">
                                                    @if($order->tracking_number)
                                                        <span class="text-success"><i class="fa fa-check-circle fa-2x"></i></span><br>
                                                        <strong style="display:inline-block; margin-top:5px; font-size: 13px;">{{ $order->tracking_number }}</strong>
                                                        <br>
                                                        <form action="{{ route('admin.orders.ekspedisiku-reset-booking', $order) }}" method="POST" style="margin-top: 8px;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-default text-danger" onclick="return confirm('Reset booking/resi ini? Resi akan dikosongkan agar bisa booking ulang.')">
                                                                <i class="fa fa-trash"></i> Reset Booking
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.orders.ekspedisiku-booking', $order) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Buat booking di EkspedisiKu?')">
                                                                <i class="fa fa-plus"></i> Buat Booking
                                                            </button>
                                                        </form>
                                                        @if($order->ekspedisiku_booking_status === 'failed' && $order->ekspedisiku_booking_last_error)
                                                            <div class="text-danger" style="margin-top:5px; font-size:11px;">{{ $order->ekspedisiku_booking_last_error }}</div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step 2: Request Pickup -->
                                        <div class="col-md-4">
                                            @if($supportsPickup)
                                                <div class="box box-solid {{ $order->ekspedisiku_pickup_status === 'success' ? 'box-success' : ($order->tracking_number ? 'box-primary' : 'box-default') }}" style="border: 1px solid #d2d6de; box-shadow: none; margin-bottom: 0;">
                                                    <div class="box-header with-border" style="padding: 8px;">
                                                        <h3 class="box-title" style="font-size: 13px;">2. Request Pickup</h3>
                                                    </div>
                                                    <div class="box-body text-center" style="padding: 10px;">
                                                        @if(!$order->tracking_number)
                                                            <span class="text-muted"><i class="fa fa-lock fa-2x"></i><br><small>Selesaikan Step 1</small></span>
                                                        @else
                                                            @if($order->ekspedisiku_pickup_status === 'success')
                                                                <span class="text-success"><i class="fa fa-check-circle fa-2x"></i></span><br>
                                                                <small style="display:block; margin-top:2px;">Requested: {{ $order->ekspedisiku_pickup_requested_at ? $order->ekspedisiku_pickup_requested_at->format('d M H:i') : '' }}</small>
                                                                <form action="{{ route('admin.orders.cancel-pickup', $order) }}" method="POST" style="margin-top: 8px;">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-xs btn-default text-danger" onclick="return confirm('Cancel request pickup?')">
                                                                        <i class="fa fa-ban"></i> Cancel Pickup
                                                                    </button>
                                                                </form>
                                                            @elseif($order->ekspedisiku_pickup_status === 'cancelled')
                                                                <span class="text-warning"><i class="fa fa-ban fa-2x"></i><br>Dibatalkan</span>
                                                                <form action="{{ route('admin.orders.request-pickup', $order) }}" method="POST" style="margin-top: 8px;">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-xs btn-warning">Request Ulang</button>
                                                                </form>
                                                            @else
                                                                <form action="{{ route('admin.orders.request-pickup', $order) }}" method="POST">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Kirim request pickup?')">
                                                                        <i class="fa fa-truck"></i> Request Pickup
                                                                    </button>
                                                                </form>
                                                                @if($order->ekspedisiku_pickup_status === 'failed' || $order->ekspedisiku_pickup_status === 'cancel_failed')
                                                                    <div class="text-danger" style="margin-top:5px; font-size:11px;">Gagal: {{ $order->ekspedisiku_pickup_last_error }}</div>
                                                                @endif
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="box box-solid box-default" style="border: 1px solid #d2d6de; box-shadow: none; margin-bottom: 0; opacity: 0.7;">
                                                    <div class="box-header with-border" style="padding: 8px;">
                                                        <h3 class="box-title" style="font-size: 13px;">2. Request Pickup</h3>
                                                    </div>
                                                    <div class="box-body text-center" style="padding: 10px;">
                                                        <span class="text-muted"><i class="fa fa-ban fa-2x"></i><br><small>Tidak didukung / Instant</small></span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Step 3: Track -->
                                        <div class="col-md-4">
                                            <div class="box box-solid {{ $order->tracking_number ? 'box-primary' : 'box-default' }}" style="border: 1px solid #d2d6de; box-shadow: none; margin-bottom: 0;">
                                                <div class="box-header with-border" style="padding: 8px;">
                                                    <h3 class="box-title" style="font-size: 13px;">3. Lacak Pengiriman</h3>
                                                </div>
                                                <div class="box-body text-center" style="padding: 10px;">
                                                    @if(!$order->tracking_number)
                                                        <span class="text-muted"><i class="fa fa-lock fa-2x"></i><br><small>Selesaikan Step 1</small></span>
                                                    @else
                                                        <a href="#" id="btn-track-order" class="btn btn-sm btn-info">
                                                            <i class="fa fa-search"></i> Lacak Resi
                                                        </a>
                                                        @if($order->shipped_at)
                                                            <br><small class="text-muted" style="display:block; margin-top:5px;">Dikirim: {{ $order->shipped_at->format('d M H:i') }}</small>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @else
                            @if($order->tracking_number && $order->expedition)
                                <tr>
                                    <th>Pelacakan</th>
                                    <td>
                                        <a href="#" id="btn-track-order" class="btn btn-xs btn-info">
                                            <i class="fa fa-search"></i> Lacak Resi
                                        </a>
                                    </td>
                                </tr>
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
                                    <td>{{ $item->product->display_name ?? 'Produk tidak tersedia' }}</td>
                                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        {{ $item->orderedQuantityDescription() }}
                                        <div class="text-muted small">Basis: {{ number_format($item->quantity) }}</div>
                                    </td>
                                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Subtotal:</th>
                                <td class="text-right">Rp {{ number_format($order->subtotal ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @if($order->discount_amount > 0)
                            <tr>
                                <th colspan="3" class="text-right text-danger">Potongan Harga ({{ $order->discount_percent }}%):</th>
                                <td class="text-right text-danger">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                            </tr>
                            @endif
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
                                    <strong>+{{ number_format($order->points_earned, 0, ',', '.') }}</strong>
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
            <!-- Update Order Information -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-edit"></i> Update Pesanan</h3>
                </div>
                <div class="box-body">
                    <form action="{{ route('admin.orders.update', $order) }}" method="POST" id="updateOrderForm">
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

                        <!-- Expedition Selection -->
                        <div class="form-group">
                            <label>Ekspedisi Pengiriman</label>
                            <select name="expedition_id" class="form-control">
                                <option value="">-- Pilih Ekspedisi --</option>
                                @foreach($expeditions as $exped)
                                    <option value="{{ $exped->id }}" {{ ($order->expedition_id == $exped->id) ? 'selected' : '' }}>
                                        {{ $exped->name }} ({{ strtoupper($exped->code) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

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
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-default">Kembali</a>
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

@if($order->tracking_number && $order->expedition)
<div class="modal fade" id="trackingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Status Pengiriman ({{ $order->expedition->name }} - {{ $order->tracking_number }})</h4>
            </div>
            <div class="modal-body" id="tracking-content">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                    <p>Sedang melacak status pengiriman...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$('#btn-track-order').click(function(e) {
    e.preventDefault();
    $('#trackingModal').modal('show');
    $('#tracking-content').html('<div class="text-center" style="padding: 20px;"><i class="fa fa-spinner fa-spin fa-3x"></i><p style="margin-top: 10px;">Sedang melacak status pengiriman...</p></div>');
    
    $.ajax({
        url: "{{ route('admin.orders.track', $order->id) }}",
        type: 'GET',
        success: function(response) {
            var html = '';
            
            if (response.success && response.data) {
                var data = response.data;

                // If RajaOngkir-like format exists, render it.
                if (data.delivery_status && data.manifest) {
                    var status = data.delivery_status;
                    html += '<div class="row" style="margin-bottom: 20px;">';
                    html += '<div class="col-md-12">';
                    html += '<div class="callout callout-' + (status.status == 'DELIVERED' ? 'success' : 'info') + '">';
                    html += '<h4>Status: ' + status.status + '</h4>';
                    html += '<p>Penerima: <strong>' + (status.pod_receiver || '-') + '</strong></p>';
                    html += '<p>Waktu: ' + (status.pod_date || '-') + ' ' + (status.pod_time || '') + '</p>';
                    html += '</div></div></div>';

                    html += '<ul class="timeline">';
                    if (data.manifest && data.manifest.length > 0) {
                        data.manifest.forEach(function(item) {
                            html += '<li>';
                            html += '<i class="fa fa-truck bg-blue"></i>';
                            html += '<div class="timeline-item">';
                            html += '<span class="time"><i class="fa fa-clock-o"></i> ' + item.manifest_date + ' ' + item.manifest_time + '</span>';
                            html += '<h3 class="timeline-header"><strong>' + item.manifest_description + '</strong></h3>';
                            if (item.city_name) {
                                html += '<div class="timeline-body"><i class="fa fa-map-marker"></i> ' + item.city_name + '</div>';
                            }
                            html += '</div>';
                            html += '</li>';
                        });
                        html += '<li><i class="fa fa-clock-o bg-gray"></i></li>';
                    } else {
                        html += '<li><div class="timeline-item"><div class="timeline-body">Tidak ada data manifest.</div></div></li>';
                    }
                    html += '</ul>';
                } else if (data.carriers && data.carriers.length > 0) {
                    // EkspedisiKu normalized tracking format: { carriers: [ { events: [...] } ] }
                    var carrier = data.carriers[0];
                    var events = carrier.events || [];

                    var latest = events.length > 0 ? events[0] : null;
                    html += '<div class="row" style="margin-bottom: 20px;">';
                    html += '<div class="col-md-12">';
                    html += '<div class="callout callout-info">';
                    html += '<h4>' + (carrier.label || carrier.id || 'Tracking') + '</h4>';
                    if (latest) {
                        html += '<p>Status: <strong>' + (latest.status || '-') + '</strong></p>';
                        html += '<p>Waktu: ' + (latest.time || '-') + '</p>';
                        html += '<p>Lokasi: ' + (latest.location || '-') + '</p>';
                        html += '<p>Keterangan: ' + (latest.remarks || '-') + '</p>';
                    } else {
                        html += '<p>Tidak ada event tracking.</p>';
                    }
                    html += '</div></div></div>';

                    html += '<ul class="timeline">';
                    if (events.length > 0) {
                        events.forEach(function(item) {
                            html += '<li>';
                            html += '<i class="fa fa-truck bg-blue"></i>';
                            html += '<div class="timeline-item">';
                            html += '<span class="time"><i class="fa fa-clock-o"></i> ' + (item.time || '-') + '</span>';
                            html += '<h3 class="timeline-header"><strong>' + (item.status || '-') + '</strong></h3>';
                            html += '<div class="timeline-body">';
                            if (item.location) {
                                html += '<div><i class="fa fa-map-marker"></i> ' + item.location + '</div>';
                            }
                            if (item.remarks) {
                                html += '<div>' + item.remarks + '</div>';
                            }
                            html += '</div>';
                            html += '</div>';
                            html += '</li>';
                        });
                        html += '<li><i class="fa fa-clock-o bg-gray"></i></li>';
                    } else {
                        html += '<li><div class="timeline-item"><div class="timeline-body">Tidak ada data tracking.</div></div></li>';
                    }
                    html += '</ul>';
                } else {
                    html = '<div class="alert alert-warning">Format data tracking tidak dikenali.</div>';
                }
                
            } else {
                html = '<div class="alert alert-warning">Gagal mendapatkan data tracking atau data tidak ditemukan.</div>';
            }
            
            $('#tracking-content').html(html);
        },
        error: function(xhr) {
            var msg = 'Gagal melacak resi.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            $('#tracking-content').html('<div class="alert alert-danger">' + msg + '</div>');
        }
    });
});

$(document).ready(function() {
    function showToast(message, type = 'success') {
        var bg = type === 'success' ? '#00a65a' : (type === 'info' ? '#00c0ef' : '#dd4b39');
        var icon = type === 'success' ? 'fa-check' : (type === 'info' ? 'fa-info-circle' : 'fa-times');
        var $toast = $('<div style="position:fixed; top:20px; right:20px; background:'+bg+'; color:white; padding:15px 25px; border-radius:4px; z-index:9999; box-shadow:0 4px 6px rgba(0,0,0,0.1); font-size:15px; display:none;"><i class="fa '+icon+'" style="margin-right:8px;"></i> <strong>'+message+'</strong></div>');
        $('body').append($toast);
        $toast.fadeIn();
        setTimeout(function() {
            $toast.fadeOut(function() { $(this).remove(); });
        }, 3000);
    }

    $('#order_status, #payment_status').on('change', function() {
        var $select = $(this);
        var $form = $('#updateOrderForm');
        
        $select.attr('disabled', true);
        
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                $select.attr('disabled', false);
                if(response.success) {
                    showToast('Status tersimpan otomatis', 'success');
                } else {
                    showToast('Tidak ada perubahan disimpan', 'info');
                }
            },
            error: function(xhr) {
                $select.attr('disabled', false);
                showToast('Gagal menyimpan status', 'error');
            }
        });
    });
});
</script>
@endif
@endpush


