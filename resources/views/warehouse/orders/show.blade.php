@extends('layouts.warehouse')

@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')
@section('page-description', 'Detail informasi pesanan')

@section('breadcrumb')
    <li><a href="{{ route('warehouse.dashboard') }}">Dashboard</a></li>
    <li><a href="{{ route('warehouse.orders.index') }}">Pesanan</a></li>
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
                        <form action="{{ route('warehouse.orders.sync-qad', $order) }}" method="POST" style="display: inline;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Menunggu...';">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-success" title="Sinkronkan sales order ke Jubelio" onclick="return confirm('Sinkronkan pesanan hub ini ke Jubelio? Proses akan berjalan di background.')">
                                <i class="fa fa-refresh"></i> Sinkron Jubelio
                            </button>
                        </form>
                    </div>
                    @elseif($order->shouldSyncToQad() && \App\Support\SalesOrderSyncDispatcher::isQadEnabled())
                    <div class="pull-right" style="margin-right: 10px;">
                        <form action="{{ route('warehouse.orders.sync-qad', $order) }}" method="POST" style="display: inline;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Menunggu...';">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-success" title="Sinkronkan sales order ke QAD" onclick="return confirm('Sinkronkan pesanan distributor ini ke QAD? Proses akan berjalan di background.')">
                                <i class="fa fa-refresh"></i> Sinkron QAD
                            </button>
                        </form>
                    </div>
                    @endif
                    <div class="pull-right" style="margin-right: 10px;">
                        <a href="{{ route('warehouse.orders.invoice', $order) }}" class="btn btn-xs btn-default" title="Download Proforma Invoice">
                            <i class="fa fa-download"></i> Download Proforma Invoice
                        </a>
                    </div>
                    <div class="pull-right" style="margin-right: 10px;">
                        <a href="{{ route('warehouse.orders.surat-jalan', $order) }}" target="_blank" class="btn btn-xs btn-default" title="Cetak Surat Jalan">
                            <i class="fa fa-print"></i> Cetak Surat Jalan
                        </a>
                    </div>
                    @if(!in_array($order->order_status, ['cancelled', 'completed', 'delivered']))
                    <div class="pull-right" style="margin-right: 10px;">
                        <form action="{{ route('warehouse.orders.update-status', $order) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="order_status" value="cancelled">
                            <button type="submit" class="btn btn-xs btn-danger" title="Batalkan Pesanan">
                                <i class="fa fa-times"></i> Batalkan Pesanan
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
                        @if($order->purchase_order_number || $order->purchase_order_document)
                        <tr>
                            <th>Purchase Order</th>
                            <td>
                                @if($order->purchase_order_number)
                                    <strong>{{ $order->purchase_order_number }}</strong>
                                @endif
                                @if($order->purchase_order_document)
                                    <a href="{{ Storage::url($order->purchase_order_document) }}" target="_blank" class="btn btn-xs btn-default" style="margin-left: 8px;">
                                        <i class="fa fa-file-pdf-o"></i> Lihat PDF
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th>Pembeli</th>
                            <td>
                                <strong>{{ $order->user->name }}</strong> ({{ $order->user->email }})
                                @if($order->address && $order->address->store_name)
                                    <br><span class="text-primary" style="font-weight: 600;"><i class="fa fa-shopping-bag"></i> {{ $order->address->store_name }}</span>
                                @endif
                                @if($order->user->phone)
                                    <br><small class="text-muted"><i class="fa fa-phone"></i> {{ $order->user->phone }}</small>
                                @endif
                            </td>
                        </tr>
                        @if($order->order_type === 'distributor' && $order->user)
                        <tr style="background-color: #fafbfc;">
                            <th><i class="fa fa-credit-card text-muted"></i> Credit Limit</th>
                            <td>
                                <strong class="text-primary" style="font-size: 14px;">
                                    Rp {{ number_format($order->user->credit_limit ?? 0, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                        <tr style="background-color: #fafbfc;">
                            <th><i class="fa fa-money text-muted"></i> AR Outstanding</th>
                            <td>
                                <strong class="{{ ($order->user->ar_outstanding ?? 0) > 0 ? 'text-danger' : 'text-success' }}" style="font-size: 14px;">
                                    Rp {{ number_format($order->user->ar_outstanding ?? 0, 0, ',', '.') }}
                                </strong>
                                @php
                                    $creditLimit = (float) ($order->user->credit_limit ?? 0);
                                    $arOutstanding = (float) ($order->user->ar_outstanding ?? 0);
                                    $sisaLimit = $creditLimit - $arOutstanding;
                                @endphp
                                <span style="margin-left: 8px;" class="text-muted">
                                    (Sisa Limit: <strong class="{{ $sisaLimit >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($sisaLimit, 0, ',', '.') }}</strong>@if($sisaLimit < 0) <span class="label label-danger" style="margin-left: 3px;">Over Limit</span>@endif)
                                </span>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th>Tanggal Pesanan</th>
                            <td>{{ \App\Support\Wib::format($order->created_at) }} WIB</td>
                        </tr>
                        @php
                            $isSelfPickupTop = $order->expedition && $order->expedition->code === 'self_pickup';
                        @endphp
                        @if($order->pickup_ready_at)
                        <tr class="bg-success">
                            <th><i class="fa fa-calendar-check-o"></i> Siap Diambil</th>
                            <td><strong>{{ \App\Support\Wib::format($order->pickup_ready_at) }} WIB</strong></td>
                        </tr>
                        @endif
                        @if($order->shipped_at)
                        <tr class="bg-info">
                            <th><i class="fa {{ $isSelfPickupTop ? 'fa-shopping-bag' : 'fa-truck' }}"></i> {{ $isSelfPickupTop ? 'Waktu Diambil Pembeli' : 'Waktu Dikirim' }}</th>
                            <td><strong>{{ \App\Support\Wib::format($order->shipped_at) }} WIB</strong></td>
                        </tr>
                        @endif
                        @if($order->received_at)
                        <tr class="bg-success">
                            <th><i class="fa fa-check-circle"></i> Waktu Diterima</th>
                            <td><strong>{{ \App\Support\Wib::format($order->received_at) }} WIB</strong></td>
                        </tr>
                        @endif
                        @if($order->preferred_shipping_date)
                        <tr class="bg-yellow">
                            <th><i class="fa fa-calendar"></i> Rencana Pengiriman</th>
                            <td><strong>{{ $order->preferred_shipping_date->format('d-m-Y') }}</strong></td>
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
                            <td>{{ $order->formatted_payment_method }}</td>
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
                                    <p class="text-muted mb-0"><small><i class="fa fa-clock-o"></i> Dikirim: {{ \App\Support\Wib::format($order->payment_submitted_at, 'd-m-Y H:i') }} WIB</small></p>
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

            <!-- Informasi Finansial Distributor -->
            @if($order->order_type === 'distributor' && $order->user)
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-id-card-o text-yellow"></i> Informasi Finansial Distributor</h3>
                    @if(auth()->user()->role === 'super_admin' && Route::has('admin.distributors.edit'))
                    <div class="box-tools pull-right">
                        <a href="{{ route('admin.distributors.edit', $order->user) }}" target="_blank" class="btn btn-xs btn-default">
                            <i class="fa fa-external-link"></i> Profil Distributor
                        </a>
                    </div>
                    @endif
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="info-box bg-aqua" style="margin-bottom: 0;">
                                <span class="info-box-icon"><i class="fa fa-credit-card"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Credit Limit</span>
                                    <span class="info-box-number" style="font-size: 15px;">
                                        Rp {{ number_format($order->user->credit_limit ?? 0, 0, ',', '.') }}
                                    </span>
                                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                                    <span class="progress-description" style="font-size: 11px;">
                                        Limit kredit terdaftar
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            @php
                                $arOutstanding = (float) ($order->user->ar_outstanding ?? 0);
                            @endphp
                            <div class="info-box {{ $arOutstanding > 0 ? 'bg-red' : 'bg-green' }}" style="margin-bottom: 0;">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">AR Outstanding</span>
                                    <span class="info-box-number" style="font-size: 15px;">
                                        Rp {{ number_format($arOutstanding, 0, ',', '.') }}
                                    </span>
                                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                                    <span class="progress-description" style="font-size: 11px;">
                                        Total tagihan berjalan
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            @php
                                $creditLimit = (float) ($order->user->credit_limit ?? 0);
                                $sisaLimit = $creditLimit - $arOutstanding;
                            @endphp
                            <div class="info-box {{ $sisaLimit >= 0 ? 'bg-green' : 'bg-yellow' }}" style="margin-bottom: 0;">
                                <span class="info-box-icon"><i class="fa {{ $sisaLimit >= 0 ? 'fa-check' : 'fa-exclamation-triangle' }}"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sisa Limit Kredit</span>
                                    <span class="info-box-number" style="font-size: 15px;">
                                        Rp {{ number_format($sisaLimit, 0, ',', '.') }}
                                    </span>
                                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                                    <span class="progress-description" style="font-size: 11px;">
                                        {{ $sisaLimit >= 0 ? 'Limit masih mencukupi' : 'Melebihi Credit Limit' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

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
                                @php
                                    $qadCustCode = $order->resolveQadCustomerCode();
                                @endphp
                                @if($qadCustCode)
                                    <span class="label label-default" style="font-size: 14px;">{{ $qadCustCode }}</span>
                                    @if($qadCustCode === 'HUB00002')
                                        <small class="text-muted">(Hub Bekasi)</small>
                                    @endif
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

                    @if(!empty($order->qad_sync_history) && is_array($order->qad_sync_history))
                    <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0;">Riwayat Sinkronisasi QAD</h4>
                        <div>
                            <form action="{{ route('warehouse.orders.check-qad', $order) }}" method="POST" style="display:inline;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Memeriksa...';">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-primary" title="Tarik data terbaru dari API QAD"><i class="fa fa-refresh"></i> Cek Status Terkini</button>
                            </form>
                            <button type="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#modalCurlQad"><i class="fa fa-code"></i> Lihat cURL</button>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th style="width: 150px;">Waktu</th>
                                        <th style="width: 70px;">Attempt</th>
                                        <th style="width: 100px;">Status</th>
                                        <th>Respons / Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_reverse($order->qad_sync_history) as $history)
                                    <tr>
                                        <td>{{ \App\Support\Wib::format($history['timestamp'] ?? null, 'd-m-Y H:i') }}</td>
                                        <td>{{ $history['attempt'] ?? '-' }}</td>
                                        <td>
                                            @if(($history['status'] ?? '') === 'success')
                                                <span class="label label-success">Success</span>
                                            @else
                                                <span class="label label-danger">Failed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $responseStr = '-';
                                                if (isset($history['response'])) {
                                                    if (is_array($history['response'])) {
                                                        // Check for specific error message in array
                                                        if (isset($history['response']['error']['errorMessages'])) {
                                                            $errs = $history['response']['error']['errorMessages'];
                                                            $responseStr = is_array($errs) ? implode(', ', $errs) : $errs;
                                                        } elseif (isset($history['response']['error']) && is_string($history['response']['error'])) {
                                                            $responseStr = $history['response']['error'];
                                                        } else {
                                                            $responseStr = json_encode($history['response'], JSON_PRETTY_PRINT);
                                                        }
                                                    } else {
                                                        $responseStr = $history['response'];
                                                    }
                                                }
                                            @endphp
                                            @if(($history['status'] ?? '') !== 'success')
                                                <div class="text-danger" style="font-size: 13px; font-family: monospace;">{{ $responseStr }}</div>
                                            @else
                                                <div class="text-success" style="font-size: 13px; font-family: monospace;">Berhasil ({{ $responseStr }})</div>
                                            @endif

                                            <div class="text-muted" style="margin-top:5px; font-size:11px;">
                                                <a href="#" onclick="$(this).next('div').toggle(); return false;">[Lihat Detail JSON Payload & Response]</a>
                                                <div style="display:none; margin-top:5px;">
                                                    <strong>Payload:</strong>
                                                    <a href="#" onclick="var text = $(this).next('pre').text(); navigator.clipboard.writeText(text).then(function() { alert('Payload disalin!'); }); return false;" class="pull-right" style="margin-bottom: 2px;"><i class="fa fa-copy"></i> Copy Payload</a>
                                                    <pre style="font-size:10px; background-color:#f9f9f9; padding:5px; border:1px solid #ccc; max-height:200px; overflow-y:auto; margin-bottom: 10px; white-space: pre-wrap;">{{ json_encode($history['payload'] ?? [], JSON_PRETTY_PRINT) }}</pre>

                                                    <strong>Response:</strong>
                                                    <pre style="font-size:10px; background-color:#f9f9f9; padding:5px; border:1px solid #ccc; max-height:200px; overflow-y:auto; white-space: pre-wrap;">{{ json_encode($history['response'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

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
                    @if(\App\Support\QadIntegration::isConfigured())
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label class="checkbox-inline" style="font-weight: normal;">
                            <input type="checkbox" id="warehouseDebugQidExecute"> Jalankan POST create sales order ke QID
                        </label>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" id="warehouseBtnDebugQidSo">
                        <i class="fa fa-code"></i> Debug Sales Order (QID)
                    </button>
                    <hr style="margin: 12px 0;">
                    @endif
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label class="checkbox-inline" style="font-weight: normal;">
                            <input type="checkbox" id="warehouseDebugPickupExecute" @if(!$canWarehouseDebugPickup) disabled @endif> Jalankan POST request pickup
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
                            <td>
                                @php
                                    $storeName = $order->address?->store_name;
                                    $rawShipping = trim((string)$order->shipping_address);
                                    if ($rawShipping !== '') {
                                        $shippingLines = explode("\n", str_replace("\r\n", "\n", $rawShipping));
                                        $recipientLine = array_shift($shippingLines);
                                        if ($storeName) {
                                            $filteredLines = [];
                                            $foundStore = false;
                                            foreach ($shippingLines as $line) {
                                                if (!$foundStore && trim($line) === trim($storeName)) {
                                                    $foundStore = true;
                                                    continue;
                                                }
                                                $filteredLines[] = $line;
                                            }
                                            $shippingLines = $filteredLines;
                                        }
                                        $remainingAddress = implode("\n", $shippingLines);
                                    } elseif ($order->address) {
                                        $recipientLine = $order->address->recipient_name;
                                        $remainingAddress = trim($order->address->phone . "\n" . $order->address->address_detail . "\n" . ($order->address->district_name ? 'Kec. ' . $order->address->district_name . ', ' : '') . ($order->address->regency_name ? $order->address->regency_name . ', ' : '') . ($order->address->province_name ?? '') . ($order->address->postal_code ? ' ' . $order->address->postal_code : ''));
                                    } else {
                                        $recipientLine = '-';
                                        $remainingAddress = '';
                                    }
                                @endphp
                                @if($recipientLine)
                                    <strong>{{ $recipientLine }}</strong>
                                @endif
                                @if($storeName)
                                    <br><span class="text-primary" style="font-weight: 600;"><i class="fa fa-shopping-bag"></i> {{ $storeName }}</span>
                                @endif
                                @if($remainingAddress !== '')
                                    <br>{!! nl2br(e($remainingAddress)) !!}
                                @endif
                            </td>
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
                                    $isSelfPickup = $order->expedition && $order->expedition->code === 'self_pickup';
                                @endphp

                                @if($isSelfPickup)
                                    <span class="label label-success" style="font-size: 12px;"><i class="fa fa-shopping-bag"></i> Tidak Ada Resi (Pengambilan Ditempat)</span>
                                @elseif($order->tracking_number)
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
                                    <h4 style="margin-top:0; margin-bottom: 15px; font-size: 16px;"><i class="fa fa-paper-plane-o"></i> Proses Pengiriman</h4>
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
                                                        <form action="{{ route('warehouse.orders.ekspedisiku-reset-booking', $order) }}" method="POST" style="margin-top: 8px;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-default text-danger" onclick="return confirm('Reset booking/resi ini? Resi akan dikosongkan agar bisa booking ulang.')">
                                                                <i class="fa fa-trash"></i> Reset Booking
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('warehouse.orders.ekspedisiku-booking', $order) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Buat booking pengiriman?')">
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
                                                                <small style="display:block; margin-top:2px;">Requested: {{ $order->ekspedisiku_pickup_requested_at ? \App\Support\Wib::format($order->ekspedisiku_pickup_requested_at, 'd-m-Y H:i') : '' }} WIB</small>
                                                                <form action="{{ route('warehouse.orders.cancel-pickup', $order) }}" method="POST" style="margin-top: 8px;">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-xs btn-default text-danger" onclick="return confirm('Cancel request pickup?')">
                                                                        <i class="fa fa-ban"></i> Cancel Pickup
                                                                    </button>
                                                                </form>
                                                            @elseif($order->ekspedisiku_pickup_status === 'cancelled')
                                                                <span class="text-warning"><i class="fa fa-ban fa-2x"></i><br>Dibatalkan</span>
                                                                <form action="{{ route('warehouse.orders.request-pickup', $order) }}" method="POST" style="margin-top: 8px;">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-xs btn-warning">Request Ulang</button>
                                                                </form>
                                                            @else
                                                                <form action="{{ route('warehouse.orders.request-pickup', $order) }}" method="POST">
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
                                                            <br><small class="text-muted" style="display:block; margin-top:5px;">Dikirim: {{ \App\Support\Wib::format($order->shipped_at, 'd-m-Y H:i') }} WIB</small>
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
                            <td>{{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</td>
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
                                <th class="text-right">Harga jual</th>
                                <th class="text-right">Harga tanpa pajak</th>
                                <th class="text-right">Diskon</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $subtotalDpp = 0;
                                $orderUsesPpn = ($order->pakai_ppn ?? true) && (! $order->user || $order->user->usesPpn());
                                $itemTaxPercent = $orderUsesPpn ? \App\Models\Setting::taxPercent() : 0.0;
                            @endphp
                            @foreach($order->items as $item)
                                @php
                                    $priceBefore = $item->catalogUnitPrice() * $item->displayPriceMultiplier();
                                    $priceWithoutTax = \App\Support\TaxAwarePrice::excludingTax($priceBefore, $itemTaxPercent);
                                    $priceWithoutTaxAfter = $item->unitPriceAfterDiscount();
                                    $lineDiscount = max(0, $priceWithoutTax - $priceWithoutTaxAfter);
                                    $lineDiscountPercent = $item->unitDiscountPercent();
                                    if ($lineDiscountPercent <= 0 && $priceWithoutTax > 0) {
                                        $lineDiscountPercent = ($lineDiscount / $priceWithoutTax) * 100;
                                    }
                                    $qtyDisplay = max(1, $item->displayQuantity());
                                    $lineSubtotal = max(0, $priceWithoutTax - $lineDiscount) * $qtyDisplay;
                                    $subtotalDpp += $lineSubtotal;
                                    $batches = is_array($item->allocated_batches) ? $item->allocated_batches : [];
                                @endphp
                                <tr>
                                    <td>
                                        @if($item->product)
                                            {{ $item->product->code }} - {{ $item->product->display_name ?? $item->product->name }}
                                        @else
                                            Produk tidak tersedia
                                        @endif
                                        @if(count($batches) > 0)
                                            <div style="margin-top: 5px;">
                                                @foreach($batches as $batchIndex => $batch)
                                                    <div>
                                                        <em>
                                                            Batch : {{ $batch['lot_serial'] ?? '-' }}
                                                            @if(!empty($batch['item_code']) && $batch['item_code'] !== ($item->product?->code ?? ''))
                                                                <span class="text-primary font-weight-bold">[{{ $batch['item_code'] }}]</span>
                                                            @endif
                                                            @if(!empty($batch['expired']))
                                                                - Expired : {{ \App\Support\Wib::format($batch['expired'], 'd-m-Y') }}
                                                            @endif
                                                            @if(count($batches) > 1 && !empty($batch['qty']))
                                                                ({{ (int)$batch['qty'] }} {{ $item->product?->unit ?? 'PCS' }})
                                                            @endif
                                                        </em>
                                                        <a href="javascript:void(0)" class="text-primary btn-edit-batch" style="margin-left: 6px;" 
                                                           data-item-id="{{ $item->id }}"
                                                           data-item-name="{{ $item->product ? ($item->product->code . ' - ' . ($item->product->display_name ?? $item->product->name)) : 'Item #' . $item->id }}"
                                                           data-item-qty="{{ (int) $item->quantity }}"
                                                           data-batch-index="{{ $batchIndex }}"
                                                           data-batch-lot="{{ $batch['lot_serial'] ?? '' }}"
                                                           data-batch-qty="{{ $batch['qty'] ?? (int)$item->quantity }}"
                                                           data-item-unit="{{ $item->product?->unit ?? 'PCS' }}"
                                                           title="Edit Batch">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div style="margin-top: 5px;">
                                                <em>Batch : -</em>
                                                <a href="javascript:void(0)" class="text-primary btn-edit-batch" style="margin-left: 6px;" 
                                                   data-item-id="{{ $item->id }}"
                                                   data-item-name="{{ $item->product ? ($item->product->code . ' - ' . ($item->product->display_name ?? $item->product->name)) : 'Item #' . $item->id }}"
                                                   data-item-qty="{{ (int) $item->quantity }}"
                                                   data-batch-index="0"
                                                   data-batch-lot=""
                                                   data-batch-qty="{{ (int) $item->quantity }}"
                                                   data-item-unit="{{ $item->product?->unit ?? 'PCS' }}"
                                                   title="Pilih Batch">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($priceBefore, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">
                                        <span title="{{ number_format($priceWithoutTax, 2, ',', '.') }}">{{ number_format($priceWithoutTax, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-right">
                                        @if($lineDiscount > 0)
                                            <span class="text-danger" title="{{ number_format($lineDiscount, 2, ',', '.') }}">{{ number_format($lineDiscount, 0, ',', '.') }}</span>
                                            @if($lineDiscountPercent > 0)
                                                <br><small class="text-muted">({{ rtrim(rtrim(number_format($lineDiscountPercent, 1, ',', '.'), '0'), ',') }}%)</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $item->orderedQuantityDescription() }}
                                        @if(! $item->displaysLargeUnit())
                                            <div class="text-muted small">Basis: {{ number_format($item->quantity) }}</div>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <span title="{{ number_format($lineSubtotal, 2, ',', '.') }}">{{ number_format($lineSubtotal, 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @php
                                $couponDiscount = (float) ($order->discount_amount ?? 0);
                                $taxableAmount = max(0, $subtotalDpp - $couponDiscount);
                                $taxAmount = $itemTaxPercent > 0 ? round($taxableAmount * ($itemTaxPercent / 100), 2) : 0.0;
                                $ppnLabel = \App\Support\TaxAwarePrice::ppnLabel($itemTaxPercent);
                            @endphp
                            <tr>
                                <th colspan="5" class="text-right">Subtotal:</th>
                                <td class="text-right">{{ number_format($subtotalDpp, 0, ',', '.') }}</td>
                            </tr>
                            @if($couponDiscount > 0)
                            <tr>
                                <th colspan="5" class="text-right">Diskon:</th>
                                <td class="text-right">-{{ number_format($couponDiscount, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if($orderUsesPpn && $taxAmount > 0)
                            <tr>
                                <th colspan="5" class="text-right">Pajak ({{ $ppnLabel }}):</th>
                                <td class="text-right">{{ number_format($taxAmount, 0, ',', '.') }}</td>
                            </tr>
                            @elseif(! $orderUsesPpn)
                            <tr>
                                <th colspan="5" class="text-right">Pajak:</th>
                                <td class="text-right"><span class="text-muted">Dibebaskan (0%)</span></td>
                            </tr>
                            @endif
                            <tr>
                                <th colspan="5" class="text-right">Ongkos Kirim:</th>
                                <td class="text-right">{{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr style="font-size: 16px;">
                                <th colspan="5" class="text-right">Total:</th>
                                <th class="text-right">{{ number_format($order->total_amount, 0, ',', '.') }}</th>
                            </tr>
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
                            <td><strong>{{ $warehouse->name ?? ($order->sourceWarehouse->name ?? '-') }}</strong></td>
                        </tr>
                        <tr>
                            <th>Lokasi</th>
                            <td>{{ $warehouse->full_location ?? ($order->sourceWarehouse->full_location ?? '-') }}</td>
                        </tr>
                        @if(!empty($warehouse->address ?? $order->sourceWarehouse?->address))
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $warehouse->address ?? $order->sourceWarehouse->address }}</td>
                        </tr>
                        @endif
                        @if(!empty($warehouse->phone ?? $order->sourceWarehouse?->phone))
                        <tr>
                            <th>Telepon</th>
                            <td>{{ $warehouse->phone ?? $order->sourceWarehouse->phone }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Status</th>
                            <td>
                                @if(($warehouse->is_active ?? $order->sourceWarehouse?->is_active))
                                    <span class="label label-success">Aktif</span>
                                @else
                                    <span class="label label-danger">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Update Order Information -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-edit"></i> Update Pesanan</h3>
                </div>
                <div class="box-body">
                    <form action="{{ route('warehouse.orders.update', $order) }}" method="POST" id="updateOrderForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Payment Status -->
                        <div class="form-group">
                            <label>Status Pembayaran Saat Ini</label>
                            @php
                                $paymentClass = [
                                    'pending' => 'warning',
                                    'paid' => 'success',
                                    'failed' => 'danger',
                                    'refunded' => 'info',
                                    'term_of_payment' => 'primary',
                                ][$order->payment_status] ?? 'default';
                            @endphp

                            @if($order->isAwaitingFinanceApproval())
                                <div class="alert alert-warning" style="margin-bottom: 12px;">
                                    <i class="fa fa-lock"></i>
                                    <strong>Term of Payment</strong> — Finance Approval: <strong>0</strong> (belum di-approve).
                                    @if(in_array(auth()->user()->role, ['super_admin', 'finance']))
                                        <div style="margin-top: 10px;">
                                            <button type="button" onclick="if(confirm('Setujui TOP ini (Finance Approval = 1) dan lepaskan ke hub?')) { document.getElementById('approveFinanceForm').submit(); }" class="btn btn-success btn-sm">
                                                <i class="fa fa-check"></i> Approve Finance (set ke 1)
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="alert {{ $order->finance_approved ? 'alert-success' : 'alert-default' }}" style="margin-bottom: 12px; padding: 10px;">
                                    <i class="fa fa-{{ $order->finance_approved ? 'check-circle' : 'clock-o' }}"></i>
                                    Finance Approval: <strong>{{ $order->finance_approved ? '1' : '0' }}</strong>
                                    @if($order->finance_approved && $order->finance_approved_at)
                                        @if($order->financeApprover)
                                            — oleh <strong>{{ $order->financeApprover->name }}</strong>
                                        @endif
                                        pada {{ \App\Support\Wib::format($order->finance_approved_at) }} WIB
                                    @endif
                                </div>
                            @endif

                            <select name="payment_status" id="payment_status" class="form-control">
                                <option value="">-- Pilih Status Pembayaran Baru (Opsional) --</option>
                                <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                                <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed (Gagal)</option>
                                <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded (Dikembalikan)</option>
                                <option value="term_of_payment" {{ $order->payment_status === 'term_of_payment' ? 'selected' : '' }}>Term Of Payment (Approved)</option>
                            </select>
                            @if($order->order_type === 'distributor' && $order->user)
                                <div class="well well-sm" style="margin-top: 10px; margin-bottom: 0; background: #fff; border-left: 3px solid #f39c12;">
                                    <small class="text-muted text-uppercase" style="font-weight: 600;">Status Kredit Distributor</small>
                                    <div style="margin-top: 4px; display: flex; justify-content: space-between;">
                                        <span>Credit Limit:</span>
                                        <strong>Rp {{ number_format($order->user->credit_limit ?? 0, 0, ',', '.') }}</strong>
                                    </div>
                                    <div style="display: flex; justify-content: space-between;">
                                        <span>AR Outstanding:</span>
                                        <strong class="{{ ($order->user->ar_outstanding ?? 0) > 0 ? 'text-danger' : 'text-success' }}">Rp {{ number_format($order->user->ar_outstanding ?? 0, 0, ',', '.') }}</strong>
                                    </div>
                                    @php
                                        $sisaLimit = (float)($order->user->credit_limit ?? 0) - (float)($order->user->ar_outstanding ?? 0);
                                    @endphp
                                    <div style="display: flex; justify-content: space-between; border-top: 1px dashed #ddd; padding-top: 3px; margin-top: 3px;">
                                        <span>Sisa Limit:</span>
                                        <strong class="{{ $sisaLimit >= 0 ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($sisaLimit, 0, ',', '.') }}
                                            @if($sisaLimit < 0)
                                                <span class="label label-danger" style="font-size: 9px; padding: 1px 3px;">Over</span>
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr>

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
                            @if($order->isReleasedToHub())
                                <select name="order_status" id="order_status" class="form-control">
                                    <option value="">-- Pilih Status Baru (Opsional) --</option>
                                    <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ $order->order_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="completed" {{ $order->order_status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            @else
                                <div class="form-control" style="background-color: #e9ecef; cursor: not-allowed; display: flex; align-items: center; height: auto;">
                                    <span class="label label-{{ $statusClass }}" style="margin-right: 10px;">{{ ucfirst($order->order_status) }}</span>
                                    <span class="text-muted"><i class="fa fa-lock"></i>
                                        @if($order->isAwaitingFinanceApproval())
                                            Terkunci (Menunggu Approval Finance)
                                        @else
                                            Terkunci (Menunggu Pembayaran)
                                        @endif
                                    </span>
                                </div>
                                <input type="hidden" name="order_status" value="{{ $order->order_status }}">
                                <div class="alert alert-warning" style="margin-top: 10px; margin-bottom: 0; padding: 10px; border-radius: 5px;">
                                    <i class="fa fa-warning"></i>
                                    @if($order->isAwaitingFinanceApproval())
                                        Status pesanan hanya dapat diubah setelah finance menyetujui Term of Payment.
                                    @else
                                        Status pesanan hanya dapat diubah setelah pembayaran lunas/terkonfirmasi.
                                    @endif
                                </div>
                            @endif
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
                        @if($isSelfPickup)
                        <div class="form-group">
                            <label>Jadwal & Info Pengambilan (Pengambilan Ditempat)</label>
                            <div class="callout callout-success" style="margin-bottom: 15px; padding: 10px;">
                                <i class="fa fa-shopping-bag"></i> <strong>Pengambilan Ditempat di Gudang</strong><br>
                                <small>Tentukan kapan pesanan siap diambil dan berikan catatan instruksi untuk pembeli.</small>
                            </div>
                                <label for="pickup_ready_at" style="font-weight: 600;"><i class="fa fa-calendar-check-o text-success"></i> 1. Tanggal & Waktu Siap Diambil (Ready)</label>
                                <input type="datetime-local" class="form-control" id="pickup_ready_at" name="pickup_ready_at" 
                                       value="{{ $order->pickup_ready_at ? $order->pickup_ready_at->format('Y-m-d\TH:i') : '' }}" style="margin-bottom: 12px; font-size: 14px; padding: 8px;">
                                
                                @if(!empty($order->pickup_ready_at))
                                    <label for="shipped_at" style="font-weight: 600;"><i class="fa fa-handshake-o text-primary"></i> 2. Tanggal & Waktu Diserahkan / Diambil Pembeli (Handover)</label>
                                    <input type="datetime-local" class="form-control" id="shipped_at" name="shipped_at" 
                                           value="{{ $order->shipped_at ? $order->shipped_at->format('Y-m-d\TH:i') : '' }}" style="margin-bottom: 12px; font-size: 14px; padding: 8px;">
                                @else
                                    <div class="text-muted" style="margin-bottom: 12px; font-style: italic; font-size: 12px;"><i class="fa fa-info-circle"></i> Tentukan dan simpan jadwal siap diambil (Ready) terlebih dahulu untuk memunculkan opsi serah terima.</div>
                                @endif

                                <label for="pickup_note" style="font-weight: 600;"><i class="fa fa-pencil text-success"></i> Catatan & Instruksi Pengambilan</label>
                                <textarea class="form-control" id="pickup_note" name="pickup_note" rows="3" 
                                          placeholder="Contoh: Barang siap diambil di Gudang Utama (Lantai 1), harap menemui petugas gudang dengan menunjukkan nomor pesanan ini.">{{ $order->pickup_note }}</textarea>
                                <small class="text-info" style="display: block; margin-top: 6px;">
                                    <i class="fa fa-info-circle"></i> Mengisi waktu diserahkan/diambil akan otomatis mengubah status pesanan menjadi Dikirim (Diserahkan).
                                </small>
                            </div>
                        @else
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
                        @endif



                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            <i class="fa fa-save"></i> Simpan Perubahan
                        </button>
                        <p class="text-muted text-center" style="margin-top: 10px; font-size: 12px;">
                            <i class="fa fa-info-circle"></i> Hanya field yang diubah yang akan diperbarui
                        </p>
                    </form>

                    <!-- Hidden form for Approve Finance -->
                    <form id="approveFinanceForm" action="{{ route('warehouse.orders.approve-finance', $order) }}" method="POST" style="display: none;">
                        @csrf
                        @method('PUT')
                    </form>
                </div>
            </div>

            <!-- Integration Logs Box -->
            <div class="box box-info shadow-sm" style="border-top-color: #00c0ef;">
                <div class="box-header with-border" style="background-color: #f8f9fa;">
                    <h3 class="box-title" style="font-weight: 600;"><i class="fa fa-history text-info"></i> Riwayat Integrasi Sistem</h3>
                </div>
                <div class="box-body" style="font-size: 13px;">
                    <ul class="timeline timeline-inverse" style="margin-bottom: 0;">
                        <!-- Finance Approval Log -->
                        <li>
                            <i class="fa {{ $order->finance_approved ? 'fa-check bg-green' : ($order->payment_method === 'term_of_payment' ? 'fa-clock-o bg-yellow' : 'fa-minus bg-gray') }}"></i>
                            <div class="timeline-item border-0">
                                <span class="time"><i class="fa fa-calendar"></i> {{ $order->finance_approved_at ? \App\Support\Wib::format($order->finance_approved_at, 'd-m-Y H:i') . ' WIB' : 'Menunggu' }}</span>
                                <h3 class="timeline-header" style="border-bottom: none; font-size: 13px;">
                                    <strong>Approval Finance</strong>
                                </h3>
                                <div class="timeline-body" style="padding-top: 0; padding-bottom: 5px;">
                                    @if($order->finance_approved)
                                        <span class="label label-success">Disetujui</span>
                                        @if($order->financeApprover)
                                            oleh {{ $order->financeApprover->name }}
                                        @else
                                            (Auto-Approve System)
                                        @endif
                                    @else
                                        @if($order->payment_method === 'term_of_payment')
                                            <span class="label label-warning">Pending Approval</span>
                                        @else
                                            <span class="text-muted">Tidak Berlaku (Bukan TOP)</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </li>

                        <!-- Notifikasi EDMS Log (Section Terpisah) -->
                        @if($order->payment_method === 'term_of_payment')
                        <li>
                            @php
                                $edmsColor = 'bg-yellow';
                                $edmsIcon = 'fa-clock-o';
                                if($order->edms_notification_status === 'SUCCESS') {
                                    $edmsColor = 'bg-green';
                                    $edmsIcon = 'fa-paper-plane';
                                } elseif($order->edms_notification_status === 'FAILED') {
                                    $edmsColor = 'bg-red';
                                    $edmsIcon = 'fa-exclamation-triangle';
                                } elseif($order->edms_notification_status === 'SKIPPED') {
                                    $edmsColor = 'bg-gray';
                                    $edmsIcon = 'fa-ban';
                                }
                            @endphp
                            <i class="fa {{ $edmsIcon }} {{ $edmsColor }}"></i>
                            <div class="timeline-item border-0">
                                <span class="time"><i class="fa fa-clock-o"></i> {{ $order->edms_notified_at ? \App\Support\Wib::format($order->edms_notified_at, 'd-m-Y H:i') . ' WIB' : '-' }}</span>
                                <h3 class="timeline-header" style="border-bottom: none; font-size: 13px;">
                                    <strong>Notifikasi EDMS</strong>
                                </h3>
                                <div class="timeline-body" style="padding-top: 0; padding-bottom: 5px;">
                                    @if($order->edms_notification_status === 'SUCCESS')
                                        <span class="label label-success" title="Response: {{ is_array($order->edms_notification_response) ? json_encode($order->edms_notification_response) : $order->edms_notification_response }}">
                                            <i class="fa fa-check"></i> Terkirim ke EDMS
                                        </span>
                                        @if($order->edms_notified_at)
                                            <div class="text-muted" style="font-size: 11px; margin-top: 4px;">
                                                Terkirim pada {{ \App\Support\Wib::format($order->edms_notified_at, 'd-m-Y H:i') }} WIB
                                            </div>
                                        @endif
                                    @elseif($order->edms_notification_status === 'FAILED')
                                        <span class="label label-danger" title="{{ $order->edms_notification_error }}">
                                            <i class="fa fa-times"></i> Gagal Terkirim
                                        </span>
                                    @elseif($order->edms_notification_status === 'SKIPPED')
                                        <span class="label label-default" title="{{ $order->edms_notification_error }}">
                                            <i class="fa fa-ban"></i> Dilewati (Config Kosong)
                                        </span>
                                    @else
                                        <span class="label label-warning">
                                            <i class="fa fa-clock-o"></i> Belum Dikirim ke EDMS
                                        </span>
                                    @endif

                                    @if($order->edms_notification_error)
                                        <div class="text-danger" style="font-size: 11px; margin-top: 5px; background: #fff3f3; padding: 4px 6px; border-radius: 3px; border-left: 3px solid #dd4b39;">
                                            <i class="fa fa-exclamation-triangle"></i> {{ \Illuminate\Support\Str::limit($order->edms_notification_error, 140) }}
                                        </div>
                                    @endif

                                    <div style="margin-top: 8px;">
                                        <form action="{{ route('warehouse.orders.notify-edms', $order) }}" method="POST" style="display:inline;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Mengirim...';">
                                            @csrf
                                            <button type="submit" class="btn btn-xs {{ $order->edms_notification_status === 'SUCCESS' ? 'btn-default' : 'btn-success' }}" title="Kirim notifikasi approval finance ke EDMS">
                                                <i class="fa fa-paper-plane"></i> {{ $order->edms_notification_status === 'SUCCESS' ? 'Kirim Ulang ke EDMS' : 'Kirim ke EDMS' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endif

                        <!-- WMS Sync Log -->
                        <li>
                            @php
                                $wmsColor = 'bg-gray';
                                $wmsIcon = 'fa-cloud-upload';
                                if(in_array($order->wms_so_status, ['SUCCESS', 'PROCESSING', 'QUEUED'])) {
                                    $wmsColor = 'bg-blue';
                                    $wmsIcon = 'fa-check';
                                } elseif($order->wms_so_status === 'FAILED') {
                                    $wmsColor = 'bg-red';
                                    $wmsIcon = 'fa-times';
                                }
                            @endphp
                            <i class="fa {{ $wmsIcon }} {{ $wmsColor }}"></i>
                            <div class="timeline-item border-0">
                                <span class="time"><i class="fa fa-clock-o"></i> {{ $order->wms_so_synced_at ? \App\Support\Wib::format($order->wms_so_synced_at, 'd-m-Y H:i') . ' WIB' : '-' }}</span>
                                <h3 class="timeline-header" style="border-bottom: none; font-size: 13px;">
                                    <strong>WMS Sales Order</strong>
                                </h3>
                                <div class="timeline-body" style="padding-top: 0; padding-bottom: 5px;" id="wms-so-body">
                                    @if($order->wms_so_status)
                                        Status WMS: <strong id="wms-so-status">{{ $order->wms_so_status }}</strong>
                                    @else
                                        <span class="text-muted" id="wms-so-empty">Belum dikirim ke WMS</span>
                                    @endif
                                    <div style="margin-top: 8px;">
                                        <button type="button" class="btn btn-xs btn-success" id="btn-send-wms">
                                            <i class="fa fa-paper-plane"></i> Kirim ke WMS
                                        </button>
                                        @if($order->wms_so_status)
                                            <form action="{{ route('warehouse.orders.check-wms', $order) }}" method="POST" style="display:inline;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Memeriksa...';">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-primary" title="Cek status terbaru ke WMS"><i class="fa fa-refresh"></i> Cek Status Terkini</button>
                                            </form>
                                            <button type="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#modalCurlWms"><i class="fa fa-code"></i> Lihat cURL</button>
                                        @endif
                                    </div>
                                    @if($order->wms_so_failure_reason)
                                        <div id="wms-so-error" style="margin-top: 5px; padding: 5px; background: #fff3f3; border: 1px solid #ffcccc; border-radius: 4px; color: #cc0000; font-size: 12px; word-wrap: break-word;">
                                            <strong>Error:</strong> {{ $order->wms_so_failure_reason }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>

                        <!-- Jubelio Sync Log -->
                        @if($order->shouldSyncToJubelio() || $order->jubelio_salesorder_id || $order->jubelio_salesorder_no)
                        <li>
                            @php
                                $isJubelioSynced = !empty($order->jubelio_salesorder_id) || !empty($order->jubelio_salesorder_no);
                            @endphp
                            <i class="fa {{ $isJubelioSynced ? 'fa-check bg-green' : 'fa-clock-o bg-yellow' }}"></i>
                            <div class="timeline-item border-0">
                                <span class="time"><i class="fa {{ $isJubelioSynced ? 'fa-check-circle text-success' : 'fa-clock-o text-yellow' }}"></i> {{ $isJubelioSynced ? 'Terkirim' : 'Belum Terkirim' }}</span>
                                <h3 class="timeline-header" style="border-bottom: none; font-size: 13px;">
                                    <strong>Jubelio Sales Order</strong>
                                </h3>
                                <div class="timeline-body" style="padding-top: 0; padding-bottom: 5px;">
                                    @if($isJubelioSynced)
                                        <span class="label label-success"><i class="fa fa-check"></i> Berhasil Terkirim ke Jubelio</span>
                                        <div style="margin-top: 6px;">
                                            @if($order->jubelio_salesorder_no)
                                                No. SO: <strong>{{ $order->jubelio_salesorder_no }}</strong>
                                            @endif
                                            @if($order->jubelio_salesorder_id)
                                                <small class="text-muted">(ID: {{ $order->jubelio_salesorder_id }})</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="label label-warning"><i class="fa fa-clock-o"></i> Belum Terkirim ke Jubelio</span>
                                        <div style="margin-top: 8px;">
                                            <form action="{{ route('warehouse.orders.sync-jubelio', $order) }}" method="POST" style="display:inline;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Mengirim...';">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-success" title="Kirim sales order ke Jubelio sekarang">
                                                    <i class="fa fa-paper-plane"></i> Kirim ke Jubelio
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                        @endif

                        <!-- QAD Sync Log (untuk pesanan QAD/Distributor) -->
                        @if(($order->shouldSyncToQad() || $order->qad_so_number) && \App\Support\QadIntegration::isConfigured())
                        <li>
                            @php
                                $isQadSynced = !empty($order->qad_so_number);
                            @endphp
                            <i class="fa {{ $isQadSynced ? 'fa-check bg-green' : 'fa-clock-o bg-yellow' }}"></i>
                            <div class="timeline-item border-0">
                                <span class="time"><i class="fa {{ $isQadSynced ? 'fa-check-circle text-success' : 'fa-clock-o text-yellow' }}"></i> {{ $isQadSynced ? 'Tersinkron' : 'Belum Terkirim' }}</span>
                                <h3 class="timeline-header" style="border-bottom: none; font-size: 13px;">
                                    <strong>QAD Sales Order</strong>
                                </h3>
                                <div class="timeline-body" style="padding-top: 0; padding-bottom: 5px;">
                                    @if($isQadSynced)
                                        <span class="label label-success"><i class="fa fa-check"></i> Berhasil Terkirim ke QAD</span>
                                        <div style="margin-top: 6px;">
                                            No. SO: <strong>{{ $order->qad_so_number }}</strong>
                                        </div>
                                    @else
                                        <span class="label label-warning"><i class="fa fa-clock-o"></i> Belum Terkirim ke QAD</span>
                                        <div style="margin-top: 8px;">
                                            <form action="{{ route('warehouse.orders.sync-qad', $order) }}" method="POST" style="display:inline;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Mengirim...';">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-success" title="Kirim sales order ke QAD sekarang">
                                                    <i class="fa fa-paper-plane"></i> Kirim ke QAD
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                        @endif

                        <!-- Zoho Sync Log -->
                        <li>
                            @php
                                $isZohoSynced = !empty($order->zoho_salesorder_id) || !empty($order->zoho_invoice_id) || !empty($order->zoho_payment_id);
                                $isZohoComplete = !empty($order->zoho_salesorder_id) && !empty($order->zoho_invoice_id) && !empty($order->zoho_payment_id);
                            @endphp
                            <i class="fa {{ $isZohoComplete ? 'fa-check bg-green' : ($isZohoSynced ? 'fa-check bg-blue' : 'fa-clock-o bg-yellow') }}"></i>
                            <div class="timeline-item border-0">
                                <span class="time"><i class="fa {{ $isZohoSynced ? 'fa-check-circle text-success' : 'fa-clock-o text-yellow' }}"></i> {{ $isZohoComplete ? 'Terkirim' : ($isZohoSynced ? 'Tersinkron Sebagian' : 'Belum Terkirim') }}</span>
                                <h3 class="timeline-header" style="border-bottom: none; font-size: 13px;">
                                    <strong>Status Pengiriman ke Zoho</strong>
                                </h3>
                                <div class="timeline-body" style="padding-top: 0; padding-bottom: 5px;">
                                    @if($isZohoSynced)
                                        <span class="label {{ $isZohoComplete ? 'label-success' : 'label-info' }}">
                                            <i class="fa fa-check"></i> {{ $isZohoComplete ? 'Berhasil Terkirim ke Zoho' : 'Tersinkron Sebagian' }}
                                        </span>
                                        <div style="margin-top: 6px; font-size: 12px; line-height: 1.6;">
                                            <div>Sales Order ID: <strong>{{ $order->zoho_salesorder_id ?: '-' }}</strong></div>
                                            <div>Invoice ID: <strong>{{ $order->zoho_invoice_id ?: '-' }}</strong></div>
                                            <div>Payment ID: <strong>{{ $order->zoho_payment_id ?: '-' }}</strong></div>
                                        </div>
                                    @else
                                        <span class="label label-warning"><i class="fa fa-clock-o"></i> Belum Terkirim ke Zoho</span>
                                    @endif

                                    <div style="margin-top: 8px;">
                                        <form action="{{ route('warehouse.orders.sync-zoho', $order) }}" method="POST" style="display:inline;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Mengirim...';">
                                            @csrf
                                            <button type="submit" class="btn btn-xs {{ $isZohoComplete ? 'btn-default' : 'btn-success' }}" title="Kirim pesanan ke Zoho sekarang">
                                                <i class="fa fa-paper-plane"></i> {{ $isZohoComplete ? 'Kirim Ulang ke Zoho' : 'Kirim ke Zoho' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <i class="fa fa-clock-o bg-gray"></i>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('warehouse.orders.index') }}" class="btn btn-default">Kembali</a>
        </div>
    </div>

    @php
        $qadSyncHistory = $order->qad_sync_history;
        $lastQadSync = is_array($qadSyncHistory) ? end($qadSyncHistory) : null;
        $qadPayload = $lastQadSync['payload'] ?? null;
        $qadResponse = $lastQadSync['response'] ?? null;

        // Build WMS Payload representation based on current items
        $wmsItems = [];
        foreach ($order->items as $item) {
            $defaultCode = $item->product ? $item->product->code : '';
            $batchesByCode = [];

            if (!empty($item->allocated_batches) && is_array($item->allocated_batches)) {
                foreach ($item->allocated_batches as $batch) {
                    $code = $batch['item_code'] ?? $defaultCode;
                    if (!isset($batchesByCode[$code])) {
                        $batchesByCode[$code] = [];
                    }
                    $batchesByCode[$code][] = [
                        'batch_number' => $batch['lot_serial'] ?? '',
                        'location_code' => $order->source_qad_location_code ?? '',
                        'quantity' => (float) ($batch['qty'] ?? 0),
                    ];
                }
            }

            foreach ($batchesByCode as $code => $batchesPayload) {
                $totalBatchQty = array_sum(array_column($batchesPayload, 'quantity'));

                $wmsItems[] = [
                    'item_code' => $code,
                    'quantity' => (float) $totalBatchQty,
                    'batches' => $batchesPayload,
                ];
            }

            if (empty($batchesByCode)) {
                $wmsItems[] = [
                    'item_code' => $defaultCode,
                    'quantity' => (float) $item->quantity,
                    'batches' => [],
                ];
            }
        }
        $wmsPayload = [
            'so_number' => $order->order_number,
            'items' => $wmsItems,
            'is_update' => false,
        ];
    @endphp

    <!-- Modal cURL QAD -->
    <div class="modal fade" id="modalCurlQad" tabindex="-1" role="dialog" aria-labelledby="modalCurlQadLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalCurlQadLabel"><i class="fa fa-code"></i> Format cURL QAD</h4>
                </div>
                <div class="modal-body">
                    <p><strong>1. Cek Status (GET)</strong></p>
                    <div style="position: relative; margin-bottom: 20px;">
                        <button type="button" class="btn btn-xs btn-default" style="position: absolute; right: 10px; top: 10px;" onclick="var text = document.getElementById('curlQadText').innerText; navigator.clipboard.writeText(text).then(function() { alert('Disalin!'); });"><i class="fa fa-copy"></i> Copy</button>
                        <pre id="curlQadText" style="background: #2b2b2b; color: #a9b7c6; border: none; padding: 15px; border-radius: 4px; white-space: pre-wrap; font-family: monospace;">curl --location --request GET "{{ rtrim((string) config('services.qad.base_url'), '/') }}/api/transaction/sales-orders/get?SalesOrderCode={{ $order->qid_sales_order_number ?? $order->order_number }}" \
--header "Content-Type: application/json"</pre>
                    </div>

                    <p><strong>2. Kirim Data (POST)</strong></p>
                    <div style="position: relative; margin-bottom: 20px;">
                        <button type="button" class="btn btn-xs btn-default" style="position: absolute; right: 10px; top: 10px;" onclick="var text = document.getElementById('curlQadPostText').innerText; navigator.clipboard.writeText(text).then(function() { alert('Disalin!'); });"><i class="fa fa-copy"></i> Copy</button>
                        <pre id="curlQadPostText" style="background: #2b2b2b; color: #a9b7c6; border: none; padding: 15px; border-radius: 4px; white-space: pre-wrap; font-family: monospace;">curl --location --request POST "{{ rtrim((string) config('services.qad.base_url'), '/') }}/api/transaction/sales-orders/create" \
--header "Content-Type: application/json" \
--data-raw '{!! $qadPayload ? json_encode($qadPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : "{}" !!}'</pre>
                    </div>

                    <p><strong>3. Respons Terakhir dari QAD (History)</strong></p>
                    <pre style="background: #2b2b2b; color: #a9b7c6; border: none; padding: 15px; border-radius: 4px; white-space: pre-wrap; font-family: monospace;">{!! $qadResponse ? json_encode($qadResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : "Belum ada respons" !!}</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal cURL WMS -->
    <div class="modal fade" id="modalCurlWms" tabindex="-1" role="dialog" aria-labelledby="modalCurlWmsLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalCurlWmsLabel"><i class="fa fa-code"></i> Format cURL WMS</h4>
                </div>
                <div class="modal-body">
                    <p><strong>1. Cek Status (GET)</strong></p>
                    <div style="position: relative; margin-bottom: 20px;">
                        <button type="button" class="btn btn-xs btn-default" style="position: absolute; right: 10px; top: 10px;" onclick="var text = document.getElementById('curlWmsText').innerText; navigator.clipboard.writeText(text).then(function() { alert('Disalin!'); });"><i class="fa fa-copy"></i> Copy</button>
                        <pre id="curlWmsText" style="background: #2b2b2b; color: #a9b7c6; border: none; padding: 15px; border-radius: 4px; white-space: pre-wrap; font-family: monospace;">curl --location --request GET "{{ rtrim((string) config('services.wms.api_url'), '/') }}/sales-orders/{{ $order->order_number }}/status" \
--header "x-api-key: {{ config('services.wms.api_key') }}" \
--header "Accept: application/json"</pre>
                    </div>

                    <p><strong>2. Kirim Data (POST)</strong></p>
                    <div style="position: relative; margin-bottom: 20px;">
                        <button type="button" class="btn btn-xs btn-default" style="position: absolute; right: 10px; top: 10px;" onclick="var text = document.getElementById('curlWmsPostText').innerText; navigator.clipboard.writeText(text).then(function() { alert('Disalin!'); });"><i class="fa fa-copy"></i> Copy</button>
                        <pre id="curlWmsPostText" style="background: #2b2b2b; color: #a9b7c6; border: none; padding: 15px; border-radius: 4px; white-space: pre-wrap; font-family: monospace;">curl --location --request POST "{{ rtrim((string) config('services.wms.api_url'), '/') }}/sales-orders" \
--header "x-api-key: {{ config('services.wms.api_key') }}" \
--header "Accept: application/json" \
--header "Content-Type: application/json" \
--data-raw '{!! json_encode($wmsPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}'</pre>
                    </div>

                    <p><strong>3. Respons Terakhir dari WMS (History)</strong></p>
                    <pre style="background: #2b2b2b; color: #a9b7c6; border: none; padding: 15px; border-radius: 4px; white-space: pre-wrap; font-family: monospace;">Status: {{ $order->wms_so_status ?? 'N/A' }}
Failure Reason: {{ $order->wms_so_failure_reason ?? '-' }}</pre>
                </div>
            </div>
        </div>
    </div>

    <div id="wms-send-overlay" style="display:none; position:fixed; inset:0; z-index:10000; background:rgba(0,0,0,.55);">
        <div style="position:absolute; top:40%; left:0; right:0; text-align:center; color:#fff;">
            <i class="fa fa-spinner fa-spin" style="font-size:42px;"></i>
            <p style="margin-top:12px; font-size:16px;">Mengirim sales order ke WMS...</p>
        </div>
    </div>

    <!-- Modal Edit Batch -->
    <div class="modal fade" id="modalEditBatch" tabindex="-1" role="dialog" aria-labelledby="modalEditBatchLabel">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalEditBatchLabel"><i class="fa fa-pencil"></i> Pilih Batch Produk</h4>
                </div>
                <div class="modal-body">
                    <!-- Loading Spinner -->
                    <div id="batchModalLoading" class="text-center" style="padding: 30px 0;">
                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted" style="margin-top: 10px; font-size: 13px;">Memuat data batch dari gudang...</p>
                    </div>

                    <!-- Error Alert -->
                    <div id="batchModalError" class="alert alert-danger" style="display: none;"></div>

                    <!-- Modal Main Content -->
                    <div id="batchModalContent" style="display: none;">
                        <div style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                            <div style="font-size: 15px; font-weight: 600; color: #333;" id="batchModalProdName">-</div>
                            <div style="margin-top: 4px; font-size: 13px; color: #666;">
                                Gudang: <strong id="batchModalWarehouse">-</strong> | 
                                Dipesan: <strong class="text-primary" id="batchModalRequiredQty">0</strong> <span id="batchModalUnit">PCS</span>
                                <span id="batchModalRowInfo" style="display: none; margin-left: 8px;">
                                    | Baris yang diedit: <strong class="text-warning" id="batchModalRowLot">-</strong> (<span id="batchModalRowQty">0</span> <span id="batchModalRowUnit">PCS</span>)
                                </span>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="batchListTable" style="margin-bottom: 0;">
                                <thead style="background: #f9f9f9;">
                                    <tr>
                                        <th style="width: 35px;" class="text-center">#</th>
                                        <th>Nomor Batch</th>
                                        <th style="width: 110px;" class="text-center">Expired</th>
                                        <th style="width: 80px;" class="text-right">Stok</th>
                                        <th style="width: 150px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
            </div>
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
    $('#btn-send-wms').on('click', function () {
        if (!confirm('Kirim sales order ini ke WMS sekarang?')) {
            return;
        }
        var $btn = $(this);
        $btn.prop('disabled', true);
        $('#wms-send-overlay').show();
        $.ajax({
            url: @json(route('warehouse.orders.sync-wms', $order)),
            type: 'POST',
            data: { _token: @json(csrf_token()) },
            timeout: 120000,
            success: function (res) {
                alert(res.message || 'Sales order terkirim ke WMS.');
                window.location.reload();
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengirim ke WMS.';
                alert(msg);
                window.location.reload();
            },
            complete: function () {
                $('#wms-send-overlay').hide();
                $btn.prop('disabled', false);
            }
        });
    });

    // Store original values
    var originalOrderStatus = $('#order_status').val();
    var originalTrackingNumber = $('#tracking_number').val();
    var originalPaymentStatus = $('#payment_status').val();
    var originalPickupReadyAt = $('#pickup_ready_at').val();
    var originalShippedAt = $('#shipped_at').val();
    var originalPickupNote = $('#pickup_note').val();
    
    $('#updateOrderForm').on('submit', function(e) {
        var orderStatus = $('#order_status').val();
        var trackingNumber = $('#tracking_number').val();
        var paymentStatus = $('#payment_status').val();
        var pickupReadyAt = $('#pickup_ready_at').val();
        var shippedAt = $('#shipped_at').val();
        var pickupNote = $('#pickup_note').val();
        
        // Check if at least one field has changed
        var hasChanges = false;
        
        if (orderStatus && orderStatus !== originalOrderStatus) {
            hasChanges = true;
        }
        
        if (trackingNumber !== undefined && trackingNumber !== originalTrackingNumber) {
            hasChanges = true;
        }
        
        if (paymentStatus && paymentStatus !== originalPaymentStatus) {
            hasChanges = true;
        }

        if (pickupReadyAt !== undefined && pickupReadyAt !== originalPickupReadyAt) {
            hasChanges = true;
        }

        if (shippedAt !== undefined && shippedAt !== originalShippedAt) {
            hasChanges = true;
        }

        if (pickupNote !== undefined && pickupNote !== originalPickupNote) {
            hasChanges = true;
        }
        
        if (!hasChanges) {
            e.preventDefault();
            alert('Tidak ada perubahan yang dilakukan. Silakan ubah setidaknya satu field sebelum menyimpan.');
            return false;
        }
    });

    // ==========================================
    // Fitur Edit / Pilih Batch Order Item
    // ==========================================
    var currentItem = null;
    var defaultProductCode = '';

    // Klik icon pensil edit batch
    $(document).on('click', '.btn-edit-batch', function() {
        var $btn = $(this);
        var itemId = $btn.data('item-id');
        var itemName = $btn.data('item-name');
        var itemQty = parseFloat($btn.data('item-qty')) || 0;
        var batchIndex = parseInt($btn.data('batch-index'), 10);
        if (isNaN(batchIndex)) batchIndex = 0;
        var batchLot = ($btn.data('batch-lot') || '').toString().trim();
        var batchQty = parseFloat($btn.data('batch-qty')) || itemQty;
        var itemUnit = $btn.data('item-unit') || 'PCS';

        currentItem = {
            id: itemId,
            name: itemName,
            totalQty: itemQty,
            batchIndex: batchIndex,
            batchLot: batchLot,
            batchQty: batchQty,
            unit: itemUnit,
            currentBatches: []
        };

        // Reset state modal
        $('#batchModalLoading').show();
        $('#batchModalError').hide().text('');
        $('#batchModalContent').hide();
        $('#batchModalRowInfo').hide();

        $('#modalEditBatch').modal('show');

        var url = "{{ route('warehouse.orders.items.batches', ['order' => $order->id, 'item' => '__ITEM_ID__']) }}".replace('__ITEM_ID__', itemId);

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                $('#batchModalLoading').hide();

                if (!res.success) {
                    $('#batchModalError').show().text(res.message || 'Gagal memuat data batch.');
                    return;
                }

                $('#batchModalContent').show();
                defaultProductCode = (res.product && res.product.code) ? res.product.code : '';

                $('#batchModalProdName').text(res.product.code + ' - ' + res.product.name);
                $('#batchModalWarehouse').text(res.warehouse.name + (res.warehouse.location_code ? ' (' + res.warehouse.location_code + ')' : ''));
                $('#batchModalRequiredQty').text(res.required_qty);
                $('#batchModalUnit').text(res.product.unit || 'PCS');

                currentItem.totalQty = parseFloat(res.required_qty);
                currentItem.unit = res.product.unit || 'PCS';
                currentItem.currentBatches = Array.isArray(res.current_batches) ? res.current_batches : [];

                if (currentItem.currentBatches.length > 1 && currentItem.batchLot) {
                    $('#batchModalRowLot').text(currentItem.batchLot);
                    $('#batchModalRowQty').text(currentItem.batchQty);
                    $('#batchModalRowUnit').text(currentItem.unit);
                    $('#batchModalRowInfo').show();
                } else {
                    $('#batchModalRowInfo').hide();
                }

                renderBatchTable(res.available_batches || [], currentItem.currentBatches);
            },
            error: function(xhr) {
                $('#batchModalLoading').hide();
                var msg = 'Terjadi kesalahan saat memuat batch.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#batchModalError').show().text(msg);
            }
        });
    });

    function renderBatchTable(available, currentAllocated) {
        var $tbody = $('#batchListTable tbody');
        $tbody.empty();

        if (!available || available.length === 0) {
            $tbody.append('<tr><td colspan="5" class="text-center text-muted" style="padding: 20px;">Tidak ada batch yang tersedia di gudang untuk SKU ini.</td></tr>');
            return;
        }

        var isMultiBatch = Array.isArray(currentAllocated) && currentAllocated.length > 1;

        available.forEach(function(b, idx) {
            var stock = parseFloat(b.qty) || 0;
            var lot = (b.lot_serial || '-').trim();
            var exp = b.expired || '-';
            var code = b.item_code || defaultProductCode;
            
            var isCurrentRow = Boolean(currentItem.batchLot && lot === currentItem.batchLot);

            var codeLabel = '';
            if (code && code !== defaultProductCode) {
                codeLabel = ' <span class="text-primary font-weight-bold">[' + code + ']</span>';
            }

            var actionHtml = '';

            if (isCurrentRow) {
                actionHtml = '<span class="label label-success" style="font-size: 11px; padding: 4px 8px;"><i class="fa fa-check"></i> Terpilih</span>';
            } else if (!isMultiBatch) {
                // Item hanya memiliki 1 batch
                var isSufficient = stock >= currentItem.totalQty;
                if (isSufficient) {
                    actionHtml = '<button type="button" class="btn btn-xs btn-primary btn-select-batch" data-apply="all" data-lot="' + lot + '" data-exp="' + (b.expired || '') + '" data-code="' + code + '" data-stock="' + stock + '" data-qty="' + currentItem.totalQty + '"><i class="fa fa-check"></i> Pilih</button>';
                } else {
                    actionHtml = '<button type="button" class="btn btn-xs btn-default disabled" disabled style="opacity: 0.6; cursor: not-allowed;" title="Stok (' + stock + ') tidak mencukupi untuk ' + currentItem.totalQty + ' ' + currentItem.unit + '">Stok Kurang</button>';
                }
            } else {
                // Item memiliki multiple batch row
                var canFulfillRow = stock >= currentItem.batchQty;
                var canFulfillAll = stock >= currentItem.totalQty;

                if (canFulfillRow) {
                    actionHtml = '<button type="button" class="btn btn-xs btn-primary btn-select-batch" data-apply="row" data-lot="' + lot + '" data-exp="' + (b.expired || '') + '" data-code="' + code + '" data-stock="' + stock + '" data-qty="' + currentItem.batchQty + '" title="Ganti baris ini (' + currentItem.batchQty + ' ' + currentItem.unit + ')"><i class="fa fa-check"></i> Pilih (' + currentItem.batchQty + ')</button>';
                    
                    if (canFulfillAll) {
                        actionHtml += ' <button type="button" class="btn btn-xs btn-info btn-select-batch" data-apply="all" data-lot="' + lot + '" data-exp="' + (b.expired || '') + '" data-code="' + code + '" data-stock="' + stock + '" data-qty="' + currentItem.totalQty + '" title="Gunakan batch ini untuk seluruh item (' + currentItem.totalQty + ' ' + currentItem.unit + ')">Semua (' + currentItem.totalQty + ')</button>';
                    }
                } else {
                    actionHtml = '<button type="button" class="btn btn-xs btn-default disabled" disabled style="opacity: 0.6; cursor: not-allowed;" title="Stok (' + stock + ') tidak mencukupi untuk baris ini (' + currentItem.batchQty + ' ' + currentItem.unit + ')">Stok Kurang</button>';
                }
            }

            var isStockSufficientForHighlight = isMultiBatch ? (stock >= currentItem.batchQty) : (stock >= currentItem.totalQty);
            var stockBadge = isStockSufficientForHighlight 
                ? '<strong class="text-success" style="font-size: 13px;">' + stock + '</strong>' 
                : '<span class="text-danger" style="font-size: 13px;">' + stock + '</span>';

            var tr = '<tr>'
                + '<td class="text-center text-muted" style="vertical-align: middle;">' + (idx + 1) + '</td>'
                + '<td style="vertical-align: middle;"><strong class="text-primary">' + lot + '</strong>' + codeLabel + '</td>'
                + '<td class="text-center" style="vertical-align: middle;">' + exp + '</td>'
                + '<td class="text-right" style="vertical-align: middle;">' + stockBadge + '</td>'
                + '<td class="text-center" style="vertical-align: middle; white-space: nowrap;">' + actionHtml + '</td>'
                + '</tr>';

            $tbody.append(tr);
        });
    }

    // Klik tombol Pilih pada baris batch
    $(document).on('click', '.btn-select-batch', function() {
        if (!currentItem) return;

        var $btn = $(this);
        var lot = $btn.data('lot');
        var exp = $btn.data('exp') || null;
        var code = $btn.data('code') || defaultProductCode;
        var stock = $btn.data('stock');
        var applyMode = $btn.data('apply') || 'all';
        var targetQty = parseFloat($btn.data('qty')) || currentItem.totalQty;

        var confirmMsg = '';
        if (applyMode === 'row') {
            confirmMsg = 'Ganti baris batch ini dengan "' + lot + '" (Stok: ' + stock + ') sebanyak ' + targetQty + ' ' + currentItem.unit + '?';
        } else {
            confirmMsg = 'Gunakan batch "' + lot + '" (Stok: ' + stock + ') untuk pesanan ini sebanyak ' + targetQty + ' ' + currentItem.unit + '?';
        }

        if (!confirm(confirmMsg)) {
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

        var batchesToSave = [];

        if (applyMode === 'row' && Array.isArray(currentItem.currentBatches) && currentItem.currentBatches.length > 0 && currentItem.batchIndex >= 0 && currentItem.batchIndex < currentItem.currentBatches.length) {
            batchesToSave = JSON.parse(JSON.stringify(currentItem.currentBatches));
            batchesToSave[currentItem.batchIndex] = {
                lot_serial: lot,
                qty: targetQty,
                expired: exp,
                item_code: code
            };
        } else {
            batchesToSave = [
                {
                    lot_serial: lot,
                    qty: targetQty,
                    expired: exp,
                    item_code: code
                }
            ];
        }

        var updateUrl = "{{ route('warehouse.orders.items.update-batches', ['order' => $order->id, 'item' => '__ITEM_ID__']) }}".replace('__ITEM_ID__', currentItem.id);

        $.ajax({
            url: updateUrl,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                _method: 'PUT',
                batches: batchesToSave
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#modalEditBatch').modal('hide');
                    alert(res.message || 'Batch berhasil disimpan.');
                    window.location.reload();
                } else {
                    alert(res.message || 'Gagal menyimpan batch.');
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Pilih');
                }
            },
            error: function(xhr) {
                var msg = 'Gagal menyimpan batch.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Pilih');
            }
        });
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
        url: "{{ route('warehouse.orders.track', $order->id) }}",
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
                    if (carrier.share_link) {
                        html += '<p style="margin-top: 10px;"><a href="' + carrier.share_link + '" target="_blank" class="btn btn-warning btn-sm"><i class="fa fa-external-link"></i> Lacak di Lalamove</a></p>';
                    }
                    if (carrier.lalamove && carrier.lalamove.data && carrier.lalamove.data.driverId) {
                        html += '<p>Driver ID: <strong>' + carrier.lalamove.data.driverId + '</strong></p>';
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

    $('#order_status, #payment_status, #pickup_ready_at, #shipped_at').on('change', function() {
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


