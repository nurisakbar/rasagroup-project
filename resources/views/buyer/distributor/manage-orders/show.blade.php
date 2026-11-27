@extends('layouts.shop')

@section('title', 'Detail Pesanan Masuk')

@section('content')
@php
    $mode = $order->fulfillmentMode();
    $wh = $order->sourceWarehouse;
    $statusOptions = $order->statusOptions();
    $statusTone = [
        'pending' => 'warn', 'processing' => 'info', 'shipped' => 'primary',
        'delivered' => 'ok', 'completed' => 'ok', 'cancelled' => 'bad',
    ][$order->order_status] ?? 'muted';
    $payTone = $order->payment_status === 'paid' ? 'ok' : (in_array($order->payment_status, ['failed']) ? 'bad' : 'warn');
    $isCancelled = $order->order_status === 'cancelled';
    // Tahapan progres (tanpa "dibatalkan"); status "delivered" dianggap tahap yang sama dengan "completed" untuk pickup.
    $steps = collect($statusOptions)->except('cancelled');
    $stepKeys = $steps->keys()->all();
    $currentIdx = array_search($order->order_status, $stepKeys, true);
    if ($currentIdx === false) { $currentIdx = $order->order_status === 'delivered' ? count($stepKeys) - 1 : 0; }
    $subtotal = $order->subtotal ?? ($order->total_amount - ($order->shipping_cost ?? 0));
@endphp

<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('distributor.manage-orders.index') }}">Kelola Pesanan Masuk</a>
            <span></span> Detail
        </div>
    </div>
</div>

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-4">
            @include('buyer.partials.sidebar')
        </div>
        <div class="col-lg-8">
            <div class="od-wrap">

                {{-- Kepala pesanan --}}
                <div class="od-card od-head">
                    <div class="od-head-top">
                        <div>
                            <div class="od-eyebrow">Pesanan masuk</div>
                            <h1 class="od-title">#{{ $order->order_number }}</h1>
                            <div class="od-sub">{{ $order->created_at->format('d M Y, H:i') }}</div>
                        </div>
                        <div class="od-head-actions">
                            <a href="{{ route('distributor.manage-orders.surat-jalan-pdf', $order) }}" target="_blank" class="od-btn od-btn-out"><i class="fi-rs-print"></i> Surat Jalan</a>
                            <a href="{{ route('distributor.manage-orders.index') }}" class="od-btn od-btn-ghost"><i class="fi-rs-arrow-left"></i> Kembali</a>
                        </div>
                    </div>
                    <div class="od-badges">
                        @if($order->order_type === 'pos')
                            <span class="od-badge od-tone-info">Offline (POS)</span>
                        @elseif($order->order_type === 'distributor')
                            <span class="od-badge od-tone-warn">Distributor</span>
                        @elseif($order->fulfilled_by_distributor)
                            <span class="od-badge od-tone-primary">Subdist</span>
                        @else
                            <span class="od-badge od-tone-primary">Online</span>
                        @endif
                        <span class="od-badge od-tone-{{ $statusTone }}"><i class="od-dot"></i>{{ $order->statusLabel() }}</span>
                        <span class="od-badge od-tone-{{ $payTone }}">Pembayaran: {{ \App\Models\Order::paymentStatusLabel($order->payment_status) }}</span>
                    </div>
                </div>

                {{-- Pembeli & pengambilan/pengiriman --}}
                <div class="od-grid">
                    <div class="od-card">
                        <h2 class="od-h"><i class="fi-rs-user"></i> Pembeli</h2>
                        <dl class="od-dl">
                            <dt>Nama</dt><dd>{{ $order->user->name }}</dd>
                            <dt>Email</dt><dd class="od-break">{{ $order->user->email }}</dd>
                            <dt>Telepon</dt><dd>{{ $order->user->phone ?? '-' }}</dd>
                        </dl>
                    </div>

                    <div class="od-card">
                        @if($mode === 'pickup')
                            <h2 class="od-h"><i class="fi-rs-shop"></i> Ambil di tempat</h2>
                            <dl class="od-dl">
                                <dt>Lokasi</dt><dd><strong>{{ $wh->name ?? '-' }}</strong>@if(filled($wh?->address))<br>{{ $wh->address }}@endif</dd>
                                @if(filled($wh?->phone))<dt>Telepon gudang</dt><dd>{{ $wh->phone }}</dd>@endif
                                <dt>Diambil oleh</dt><dd>{{ $order->user->name }}@if(filled($order->user->phone)) ({{ $order->user->phone }})@endif</dd>
                            </dl>
                            <p class="od-hint">Tanpa pengiriman dan nomor resi.</p>
                        @elseif($mode === 'store_delivery')
                            <h2 class="od-h"><i class="fi-rs-package"></i> Diantar ketempat</h2>
                            <dl class="od-dl">
                                <dt>Alamat tujuan</dt><dd>{!! nl2br(e($order->shipping_address)) !!}</dd>
                                <dt>Penerima</dt><dd>{{ $order->user->name }}@if(filled($order->user->phone)) ({{ $order->user->phone }})@endif</dd>
                                <dt>Pengantar</dt><dd>Kurir distributor</dd>
                            </dl>
                            <p class="od-hint">Diantar langsung oleh distributor, tanpa nomor resi.</p>
                        @else
                            <h2 class="od-h"><i class="fi-rs-package"></i> Pengiriman</h2>
                            <dl class="od-dl">
                                <dt>Alamat</dt><dd>{!! nl2br(e($order->shipping_address)) !!}</dd>
                                <dt>Ekspedisi</dt><dd>{{ $order->expedition->name ?? '-' }} ({{ $order->expedition_service ?? '-' }})</dd>
                                <dt>No. Resi</dt><dd><strong class="od-brand">{{ $order->tracking_number ?? 'Belum tersedia' }}</strong></dd>
                            </dl>
                        @endif
                    </div>
                </div>

                {{-- Item pesanan --}}
                <div class="od-card">
                    <h2 class="od-h"><i class="fi-rs-shopping-bag"></i> Item pesanan <span class="od-count">{{ $order->items->count() }} produk</span></h2>
                    <div class="od-items">
                        @foreach($order->items as $item)
                            <div class="od-item">
                                <div class="od-item-main">
                                    <div class="od-item-name">
                                        @if($item->product){{ $item->product->display_name ?? $item->product->name }}@else Produk dihapus @endif
                                    </div>
                                    <div class="od-item-meta">
                                        @if($item->product)<span class="od-code">{{ $item->product->code }}</span>@endif
                                        {{ $item->quantity }} × Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="od-item-sub">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="od-total">
                        <div class="od-total-row"><span>Subtotal</span><span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
                        @if($mode === 'courier' || ($order->shipping_cost ?? 0) > 0)
                            <div class="od-total-row"><span>Ongkos kirim</span><span>Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</span></div>
                        @endif
                        @if($order->payment_fee > 0)
                            <div class="od-total-row"><span>Biaya layanan</span><span>Rp {{ number_format($order->payment_fee, 0, ',', '.') }}</span></div>
                        @endif
                        <div class="od-total-row od-total-grand"><span>Total</span><span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span></div>
                    </div>
                </div>

                @if($order->isPaidToDistributor())
                    {{-- Pembayaran ke distributor (transfer / tempo): dikonfirmasi distributor --}}
                    <div class="od-card">
                        <h2 class="od-h"><i class="fi-rs-bank"></i> Pembayaran</h2>
                        @if($order->isDistributorTop())
                            <p class="od-hint mb-2"><strong>Tempo {{ $order->payment_term_days }} hari</strong>, jatuh tempo <strong>{{ $order->payment_due_at?->locale('id')->translatedFormat('d F Y') }}</strong>{{ $order->payment_status !== 'paid' && $order->payment_due_at?->isPast() ? ' (sudah lewat jatuh tempo)' : '' }}. Pesanan boleh diproses sebelum dibayar.</p>
                        @else
                            <p class="od-hint mb-2">Pembeli mentransfer ke rekening Anda. Periksa mutasi rekening sebelum mengonfirmasi.</p>
                        @endif
                        @if($order->payment_destination_account)
                            <p class="od-hint mb-2">Ditransfer ke: <strong>{{ $order->payment_destination_account }}</strong></p>
                        @endif
                        @if($order->payment_proof)
                            <p class="mb-2"><a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" rel="noopener" class="od-btn od-btn-block" style="border: 1.5px solid #6A1B1B; color: #6A1B1B;"><i class="fi-rs-picture mr-5"></i>Lihat bukti transfer</a></p>
                            @if($order->payment_submit_note)
                                <p class="od-hint">Catatan pembeli: {{ $order->payment_submit_note }}</p>
                            @endif
                        @else
                            <p class="od-hint">Pembeli belum mengunggah bukti transfer.</p>
                        @endif
                        @if(in_array($order->payment_status, ['pending', 'term_of_payment'], true))
                            <form action="{{ route('distributor.manage-orders.update', $order) }}" method="POST" onsubmit="return confirm('Konfirmasi pembayaran Rp {{ number_format($order->total_amount, 0, ',', '.') }} sudah diterima?');">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="payment_status" value="paid">
                                <button type="submit" class="od-btn od-btn-main od-btn-block">Konfirmasi pembayaran diterima</button>
                            </form>
                        @elseif($order->payment_status === 'paid')
                            <p class="od-hint od-hint-box mb-0">Pembayaran sudah dikonfirmasi{{ $order->paid_at ? ' pada ' . $order->paid_at->format('d M Y H:i') : '' }}.</p>
                        @endif
                    </div>
                @endif

                {{-- Kelola status --}}
                <div class="od-card">
                    <h2 class="od-h"><i class="fi-rs-settings-sliders"></i> Kelola status</h2>

                    @if($isCancelled)
                        <div class="od-cancelled">Pesanan ini dibatalkan.</div>
                    @else
                        <ol class="od-steps">
                            @foreach($steps as $key => $label)
                                @php $i = $loop->index; @endphp
                                <li class="{{ $i < $currentIdx ? 'is-done' : ($i === $currentIdx ? 'is-current' : '') }}">
                                    <span class="od-step-dot">{{ $i < $currentIdx ? '✓' : $i + 1 }}</span>
                                    <span class="od-step-label">{{ $label }}</span>
                                </li>
                            @endforeach
                        </ol>
                    @endif

                    <form action="{{ route('distributor.manage-orders.update', $order) }}" method="POST" class="od-form">
                        @csrf
                        @method('PUT')
                        <div class="od-field">
                            <label for="order_status">Ubah status pesanan</label>
                            <select name="order_status" id="order_status" class="form-select">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $order->order_status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($mode === 'courier')
                            <div class="od-field">
                                <label for="tracking_number">Nomor resi</label>
                                <input type="text" name="tracking_number" id="tracking_number" class="form-control" value="{{ $order->tracking_number }}" placeholder="Contoh: JNE1234567890">
                            </div>
                        @endif
                        @if($order->fulfilled_by_distributor)
                            <p class="od-hint od-hint-box">Stok gudang dipotong saat pesanan dibuat. Mengubah status tidak memotong dua kali, dan membatalkan pesanan mengembalikan stok.</p>
                        @endif
                        <button type="submit" class="od-btn od-btn-main od-btn-block">Simpan perubahan</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .od-wrap { display: flex; flex-direction: column; gap: 18px; }
    .od-card { background: #fff; border: 1px solid #E3D6C8; border-radius: 16px; padding: 22px; box-shadow: 0 6px 20px rgba(106, 27, 27, 0.08); }
    .od-h { font-size: 17px; font-weight: 700; color: #253D4E; margin: 0 0 14px; display: flex; align-items: center; gap: 10px; }
    .od-h i { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: rgba(106, 27, 27, 0.1); color: #6A1B1B; font-size: 15px; }
    .od-count { margin-left: auto; font-size: 12px; font-weight: 600; color: #8A7B70; }
    .od-head-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; flex-wrap: wrap; }
    .od-eyebrow { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #8A7B70; }
    .od-title { font-size: clamp(22px, 5.5vw, 30px); font-weight: 800; color: #253D4E; margin: 2px 0; line-height: 1.2; word-break: break-all; }
    .od-sub { color: #7E7E7E; font-size: 14px; }
    .od-head-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .od-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; padding-top: 16px; border-top: 1px dashed #E3D6C8; }
    .od-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; padding: 6px 14px; border-radius: 999px; }
    .od-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; display: inline-block; }
    .od-tone-warn { background: #FFF4DB; color: #9A6200; }
    .od-tone-info { background: #E4F2FB; color: #1B6A9C; }
    .od-tone-primary { background: #EEE6FB; color: #5B33A8; }
    .od-tone-ok { background: #E3F4E9; color: #1E7B45; }
    .od-tone-bad { background: #FBE6E6; color: #B42318; }
    .od-tone-muted { background: #ECECEC; color: #6B6B6B; }
    .od-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    @media (max-width: 767px) { .od-grid { grid-template-columns: 1fr; } .od-card { padding: 16px; } }
    .od-dl { margin: 0; display: grid; grid-template-columns: 110px 1fr; gap: 8px 12px; font-size: 14px; }
    .od-dl dt { color: #8A7B70; font-weight: 600; }
    .od-dl dd { margin: 0; color: #253D4E; }
    @media (max-width: 575px) { .od-dl { grid-template-columns: 1fr; gap: 2px; } .od-dl dd { margin-bottom: 10px; } }
    .od-break { word-break: break-all; }
    .od-brand { color: #6A1B1B; }
    .od-hint { font-size: 12px; color: #8A7B70; margin: 12px 0 0; }
    .od-hint-box { background: #FAF6F1; border: 1px solid #E3D6C8; border-radius: 10px; padding: 10px 12px; margin: 0 0 14px; }
    .od-items { display: flex; flex-direction: column; }
    .od-item { display: flex; justify-content: space-between; gap: 14px; padding: 12px 0; border-bottom: 1px solid #F0E6DB; }
    .od-item:last-child { border-bottom: 0; }
    .od-item-name { font-weight: 700; color: #253D4E; font-size: 15px; line-height: 1.35; }
    .od-item-meta { font-size: 13px; color: #7E7E7E; margin-top: 3px; }
    .od-code { display: inline-block; background: #F4EEE8; color: #6A1B1B; font-weight: 700; font-size: 11px; padding: 1px 8px; border-radius: 6px; margin-right: 8px; }
    .od-item-sub { font-weight: 700; color: #253D4E; white-space: nowrap; }
    .od-total { margin-top: 8px; background: #FAF6F1; border-radius: 12px; padding: 12px 16px; }
    .od-total-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 14px; color: #5B6B7B; }
    .od-total-grand { border-top: 1px dashed #D9CFC4; margin-top: 6px; padding-top: 10px; font-size: 17px; font-weight: 800; color: #6A1B1B; }
    .od-steps { list-style: none; display: flex; margin: 0 0 20px; padding: 0; counter-reset: s; }
    .od-steps li { flex: 1; text-align: center; position: relative; font-size: 12px; color: #9A9A9A; }
    .od-steps li::before { content: ''; position: absolute; top: 15px; left: -50%; width: 100%; height: 3px; background: #E8E1DA; z-index: 0; }
    .od-steps li:first-child::before { display: none; }
    .od-steps li.is-done::before, .od-steps li.is-current::before { background: #6A1B1B; }
    .od-step-dot { position: relative; z-index: 1; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: #fff; border: 3px solid #E8E1DA; font-weight: 700; font-size: 13px; color: #9A9A9A; }
    .od-steps li.is-done .od-step-dot { background: #6A1B1B; border-color: #6A1B1B; color: #fff; }
    .od-steps li.is-current .od-step-dot { border-color: #6A1B1B; color: #6A1B1B; box-shadow: 0 0 0 4px rgba(106, 27, 27, 0.12); }
    .od-step-label { display: block; margin-top: 6px; line-height: 1.25; padding: 0 2px; }
    .od-steps li.is-current .od-step-label, .od-steps li.is-done .od-step-label { color: #253D4E; font-weight: 700; }
    .od-cancelled { background: #FBE6E6; color: #B42318; font-weight: 700; padding: 12px 16px; border-radius: 10px; margin-bottom: 18px; }
    .od-field { margin-bottom: 14px; }
    .od-field label { display: block; font-size: 13px; font-weight: 700; color: #253D4E; margin-bottom: 6px; }
    .od-field .form-select, .od-field .form-control { height: 48px; border-radius: 12px; border: 1.5px solid #E3D6C8; background-color: #FAF6F1; }
    .od-field .form-select:focus, .od-field .form-control:focus { border-color: #6A1B1B; box-shadow: 0 0 0 4px rgba(106, 27, 27, 0.08); }
    .od-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 42px; padding: 0 18px; border-radius: 12px; font-weight: 700; font-size: 14px; text-decoration: none; border: 2px solid transparent; cursor: pointer; }
    .od-btn-main { background: #6A1B1B !important; color: #fff !important; border-color: #6A1B1B !important; box-shadow: 0 8px 18px rgba(106, 27, 27, 0.22) !important; }
    .od-btn-main:hover { background: #531414 !important; border-color: #531414 !important; }
    .od-btn-out { background: #fff !important; color: #6A1B1B !important; border-color: #6A1B1B !important; }
    .od-btn-out:hover { background: #FAF6F1 !important; }
    .od-btn-ghost { background: #FAF6F1 !important; color: #6A1B1B !important; border-color: #D9CFC4 !important; }
    .od-btn-block { width: 100%; height: 52px; font-size: 16px; }
    @media (max-width: 575px) { .od-head-actions { width: 100%; } .od-head-actions .od-btn { flex: 1; } .od-step-label { font-size: 11px; } }
</style>
@endpush
