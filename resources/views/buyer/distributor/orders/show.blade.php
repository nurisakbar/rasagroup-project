@extends('layouts.shop')

@section('title', 'Detail Pesanan')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('distributor.orders.history') }}">Pesanan Saya</a>
            <span></span> Detail Pesanan
        </div>
    </div>
</div>

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4">
                    @include('buyer.partials.sidebar')
                </div>
                <div class="col-lg-8">
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="mb-4">
                                <a href="{{ route('distributor.orders.history') }}" class="text-brand font-sm fw-bold mb-10 d-inline-block">
                                    <i class="fi-rs-arrow-left mr-5"></i> Kembali ke Daftar Pesanan
                                </a>

                                @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fi-rs-check-circle mr-5"></i> {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                @endif
                                @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fi-rs-cross-circle mr-5"></i> {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                @endif

                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                    <h3 class="mb-0">Detail Pesanan <span class="text-brand">#{{ $order->order_number }}</span></h3>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('distributor.orders.invoice', $order->id) }}" class="btn btn-sm btn-brand rounded font-sm px-3 py-2" style="white-space: nowrap; min-width: max-content;">
                                            <i class="fi-rs-download mr-5"></i> Download Invoice
                                        </a>
                                        <div class="badge-group">
                                            @php
                                                $statusClass = match($order->order_status) {
                                                    'pending' => 'bg-warning',
                                                    'processing' => 'bg-info',
                                                    'shipped' => 'bg-primary',
                                                    'delivered' => 'bg-success',
                                                    'cancelled' => 'bg-danger',
                                                    default => 'bg-secondary',
                                                };
                                                $statusLabel = match($order->order_status) {
                                                    'pending' => $order->payment_status === 'paid' ? 'Menunggu Diproses' : ($order->payment_proof ? 'Menunggu Pembayaran Diverifikasi' : 'Menunggu Pembayaran'),
                                                    'processing' => 'Sedang Diproses',
                                                    'shipped' => 'Dalam Pengiriman',
                                                    'delivered' => 'Selesai',
                                                    'cancelled' => 'Dibatalkan',
                                                    default => ucfirst($order->order_status),
                                                };
                                            @endphp
                                            <span class="badge rounded-pill {{ $statusClass }} px-3 py-2 text-white font-sm" style="white-space: nowrap;">{{ $statusLabel }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(in_array($order->payment_method, ['manual_transfer', 'transfer']) && $order->order_status !== 'cancelled' && $order->payment_status !== 'paid')
                            <div class="card border-0 shadow-sm border-radius-15 overflow-hidden mb-4">
                                <div class="card-body p-4" style="background-color: #fcfcfc; border: 1px solid #ececec; border-radius: 15px;">
                                    <h6 class="mb-3 font-sm text-brand text-uppercase fw-bold"><i class="fi-rs-list-check mr-5"></i>Tahapan Pembayaran Manual</h6>
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center position-relative pt-2">
                                        <!-- Step 1: Upload -->
                                        @php
                                            $step1Completed = !empty($order->payment_proof) || $order->payment_status === 'paid';
                                            $step1Active = empty($order->payment_proof) && $order->payment_status === 'pending';
                                        @endphp
                                        <div class="d-flex align-items-center mb-3 mb-md-0 flex-fill">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-3" 
                                                 style="width: 38px; height: 38px; min-width: 38px; background-color: {{ $step1Completed ? '#3bb77e' : ($step1Active ? '#ff9900' : '#e2e2e2') }};">
                                                @if($step1Completed) <i class="fi-rs-check font-xs"></i> @else 1 @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold font-sm {{ $step1Active || $step1Completed ? 'text-dark' : 'text-muted' }}">1. Upload Bukti Pembayaran</div>
                                                <small class="font-xs {{ $step1Completed ? 'text-success' : ($step1Active ? 'text-warning fw-bold' : 'text-muted') }}">
                                                    {{ $step1Completed ? 'Selesai diunggah' : ($step1Active ? 'Belum diunggah' : '-') }}
                                                </small>
                                            </div>
                                        </div>

                                        <div class="d-none d-md-block border-top flex-fill mx-3" style="border-color: #e2e2e2 !important; height: 2px;"></div>

                                        <!-- Step 2: Verifikasi -->
                                        @php
                                            $step2Completed = $order->payment_status === 'paid';
                                            $step2Active = !empty($order->payment_proof) && $order->payment_status === 'pending';
                                        @endphp
                                        <div class="d-flex align-items-center mb-3 mb-md-0 flex-fill">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-3" 
                                                 style="width: 38px; height: 38px; min-width: 38px; background-color: {{ $step2Completed ? '#3bb77e' : ($step2Active ? '#ff9900' : '#e2e2e2') }};">
                                                @if($step2Completed) <i class="fi-rs-check font-xs"></i> @else 2 @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold font-sm {{ $step2Active || $step2Completed ? 'text-dark' : 'text-muted' }}">2. Menunggu Konfirmasi</div>
                                                <small class="font-xs {{ $step2Completed ? 'text-success' : ($step2Active ? 'text-warning fw-bold' : 'text-muted') }}">
                                                    {{ $step2Completed ? 'Diverifikasi admin' : ($step2Active ? 'Sedang diverifikasi admin' : 'Menunggu upload bukti') }}
                                                </small>
                                            </div>
                                        </div>

                                        <div class="d-none d-md-block border-top flex-fill mx-3" style="border-color: #e2e2e2 !important; height: 2px;"></div>

                                        <!-- Step 3: Selesai -->
                                        @php
                                            $step3Completed = $order->payment_status === 'paid';
                                        @endphp
                                        <div class="d-flex align-items-center flex-fill">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-3" 
                                                 style="width: 38px; height: 38px; min-width: 38px; background-color: {{ $step3Completed ? '#3bb77e' : '#e2e2e2' }};">
                                                @if($step3Completed) <i class="fi-rs-check font-xs"></i> @else 3 @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold font-sm {{ $step3Completed ? 'text-dark' : 'text-muted' }}">3. Pembayaran Lunas</div>
                                                <small class="font-xs {{ $step3Completed ? 'text-success fw-bold' : 'text-muted' }}">
                                                    {{ $step3Completed ? 'Pesanan siap diproses' : 'Menunggu konfirmasi' }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php
                                $isSelfPickup = $order->expedition && ($order->expedition->code === 'self_pickup' || str_contains(strtolower($order->expedition->name), 'pickup'));
                                    $isKurirToko = $order->expedition && str_contains(strtolower($order->expedition->name), 'kurir toko');
                            @endphp
                            @if($isSelfPickup && !in_array($order->order_status, ['cancelled', 'delivered', 'completed']) && $order->payment_status === 'paid')
                            <div class="card border-0 shadow-sm border-radius-15 overflow-hidden mb-4">
                                <div class="card-body p-4" style="background-color: #f0faf5; border: 1px solid #cceadd; border-radius: 15px;">
                                    <h6 class="mb-3 font-sm text-brand text-uppercase fw-bold"><i class="fi-rs-shopping-bag mr-5"></i>Tahapan Pengambilan Pesanan (Self Pickup)</h6>
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center position-relative pt-2">
                                        <!-- Step 1: Disiapkan -->
                                        @php
                                            $pickupStep1Completed = true; // Always active/completed once ordered
                                        @endphp
                                        <div class="d-flex align-items-center mb-3 mb-md-0 flex-fill">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-3" 
                                                 style="width: 38px; height: 38px; min-width: 38px; background-color: #3bb77e;">
                                                <i class="fi-rs-check font-xs"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold font-sm text-dark">1. Pesanan Disiapkan</div>
                                                <small class="font-xs text-success">
                                                    Gudang menyiapkan barang
                                                </small>
                                            </div>
                                        </div>

                                        <div class="d-none d-md-block border-top flex-fill mx-3" style="border-color: #cceadd !important; height: 2px;"></div>

                                        <!-- Step 2: Siap Diambil -->
                                        @php
                                            $pickupStep2Completed = !empty($order->pickup_ready_at) || in_array($order->order_status, ['shipped', 'delivered', 'completed']);
                                        @endphp
                                        <div class="d-flex align-items-center mb-3 mb-md-0 flex-fill">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-3" 
                                                 style="width: 38px; height: 38px; min-width: 38px; background-color: {{ $pickupStep2Completed ? '#3bb77e' : '#ff9900' }};">
                                                @if($pickupStep2Completed) <i class="fi-rs-check font-xs"></i> @else 2 @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold font-sm {{ $pickupStep2Completed ? 'text-dark' : 'text-dark' }}">2. Siap Diambil di Gudang</div>
                                                <small class="font-xs {{ $pickupStep2Completed ? 'text-success fw-bold' : 'text-warning fw-bold' }}">
                                                    @if($order->pickup_ready_at)
                                                        📅 Mulai: {{ $order->pickup_ready_at->format('d M Y, H:i') }} WIB
                                                    @elseif($pickupStep2Completed)
                                                        Barang siap diambil sekarang
                                                    @else
                                                        Menunggu jadwal dari admin gudang
                                                    @endif
                                                </small>
                                            </div>
                                        </div>

                                        <div class="d-none d-md-block border-top flex-fill mx-3" style="border-color: #cceadd !important; height: 2px;"></div>

                                        <!-- Step 3: Selesai -->
                                        @php
                                            $pickupStep3Completed = !empty($order->shipped_at) || in_array($order->order_status, ['shipped', 'delivered', 'completed']);
                                        @endphp
                                        <div class="d-flex align-items-center flex-fill">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-3" 
                                                 style="width: 38px; height: 38px; min-width: 38px; background-color: {{ $pickupStep3Completed ? '#3bb77e' : '#e2e2e2' }};">
                                                @if($pickupStep3Completed) <i class="fi-rs-check font-xs"></i> @else 3 @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold font-sm {{ $pickupStep3Completed ? 'text-dark' : 'text-muted' }}">3. Barang Diambil</div>
                                                <small class="font-xs {{ $pickupStep3Completed ? 'text-success fw-bold' : 'text-muted' }}">
                                                    @if($order->received_at)
                                                        🤝 Diterima: {{ $order->received_at->format('d M Y, H:i') }} WIB
                                                    @elseif($order->shipped_at)
                                                        🤝 Diserahkan: {{ $order->shipped_at->format('d M Y, H:i') }} WIB
                                                    @elseif($pickupStep3Completed)
                                                        🤝 Barang sudah diserahkan / diambil
                                                    @else
                                                        Menunggu pengambilan pembeli
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    @if($order->pickup_note)
                                    <div class="mt-3 p-3 rounded" style="background-color: #ffffff; border: 1px dashed #3bb77e;">
                                        <div class="d-flex align-items-start">
                                            <i class="fi-rs-info text-brand font-md me-2 mt-1"></i>
                                            <div>
                                                <span class="fw-bold text-dark font-sm d-block">Catatan / Instruksi Pengambilan dari Gudang:</span>
                                                <span class="text-muted font-sm">{{ $order->pickup_note }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if(!in_array($order->order_status, ['completed']) && (!empty($order->shipped_at) || in_array($order->order_status, ['shipped', 'delivered'])))
                            <div class="card border-0 shadow-sm border-radius-15 overflow-hidden mb-4">
                                <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center" style="background-color: #fff9e6; border: 2px solid #ffcc00; border-radius: 15px;">
                                    <div class="d-flex align-items-center mb-3 mb-md-0 me-md-3">
                                        <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px; min-width: 48px;">
                                            <i class="fi-rs-box font-lg"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-dark fw-bold font-md">Konfirmasi Pesanan Diterima</h6>
                                            <span class="text-muted font-sm">
                                                Barang sudah diserahkan/dikirim. Harap konfirmasi penerimaan agar pesanan selesai.
                                            </span>
                                        </div>
                                    </div>
                                    <form action="{{ route('distributor.orders.confirm-receipt', $order) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin pesanan ini sudah diterima?');">
                                        @csrf
                                        <button type="submit" class="btn fw-bold px-4 py-3 shadow-sm text-white" style="border-radius: 10px; background-color: #ff9900; border-color: #ff9900; white-space: nowrap;">
                                            <i class="fi-rs-check mr-5"></i> Konfirmasi Diterima
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endif

                            @php
                                $isConvertedToStock = \App\Models\WarehouseStockHistory::where('order_id', $order->id)->exists();
                            @endphp
                            @if($order->order_status === 'completed' && !$isConvertedToStock)
                            <div class="card border-0 shadow-sm border-radius-15 overflow-hidden mb-4">
                                <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center" style="background-color: #e8f8f0; border: 2px solid #3bb77e; border-radius: 15px;">
                                    <div class="d-flex align-items-center mb-3 mb-md-0 me-md-3">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px; min-width: 48px;">
                                            <i class="fi-rs-check font-lg"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-dark fw-bold font-md">Pesanan Tiba!</h6>
                                            <span class="text-muted font-sm">
                                                @if($isSelfPickup)
                                                    Pesanan Anda telah sampai. Jangan lupa untuk memasukkan item pesanan ini ke dalam stok warehouse Anda.
                                                @else
                                                    Pesanan Anda telah sampai. Jangan lupa untuk memasukkan item pesanan ini ke dalam stok warehouse Anda.
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <form action="{{ route('distributor.orders.convert-to-stock', $order) }}" method="POST" onsubmit="return confirm('Konversi item pesanan ini ke stok warehouse?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success fw-bold px-4 py-3 shadow-sm text-white" style="border-radius: 10px; background-color: #3bb77e; border-color: #3bb77e; white-space: nowrap;">
                                            <i class="fi-rs-check mr-5"></i> Masukkan ke Stock
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endif

                            <div class="row g-4">
                                <!-- Order Info Card -->
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 shadow-sm border-radius-15 overflow-hidden">
                                        <div class="card-header bg-brand-light border-0 p-3">
                                            <h5 class="mb-0 text-brand font-md"><i class="fi-rs-document mr-10"></i>Informasi Pesanan</h5>
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="info-list">
                                                <div class="info-item d-flex justify-content-between mb-3 pb-2 border-bottom">
                                                    <span class="text-dark font-sm">Tanggal Transaksi</span>
                                                    <span class="fw-bold font-sm text-dark">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                                </div>
                                                <div class="info-item d-flex justify-content-between mb-3 pb-2 border-bottom">
                                                    <span class="text-dark font-sm">Metode Pembayaran</span>
                                                    <span class="fw-bold font-sm text-dark">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                                                </div>
                                                <div class="info-item d-flex justify-content-between mb-3 pb-2 border-bottom">
                                                    <span class="text-dark font-sm">Status Pembayaran</span>
                                                    <span class="badge rounded-pill {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning' }} px-3">
                                                        {{ strtoupper($order->payment_status) }}
                                                    </span>
                                                </div>
                                                @if($order->affiliate)
                                                <div class="info-item d-flex justify-content-between mb-3 pb-2 border-bottom">
                                                    <span class="text-dark font-sm">Kode Referal</span>
                                                    <span class="fw-bold font-sm text-brand">{{ $order->affiliate->referral_code }}</span>
                                                </div>
                                                @endif
                                            </div>
                                            
                                            @if($order->payment_status === 'pending')
                                                <div class="mt-4 pt-3 border-top text-center">
                                                    <a href="{{ route('distributor.orders.success', $order) }}" class="btn btn-outline-brand rounded-pill w-100 mb-10">
                                                        <i class="fi-rs-info mr-5"></i> Lihat Instruksi & Cara Bayar
                                                    </a>
                                                </div>
                                            @endif

                                            @if($order->payment_status === 'pending' && in_array($order->payment_method, ['manual_transfer', 'transfer']))
                                                <div class="mt-4 pt-3 border-top text-center">
                                                    @if($order->payment_proof)
                                                        <div class="alert alert-info font-sm mb-0 rounded-pill">
                                                            <i class="fi-rs-time-fast mr-5"></i> Bukti Pembayaran Sedang Diverifikasi
                                                        </div>
                                                    @else
                                                        <a href="{{ route('distributor.orders.confirm-payment', $order) }}" class="btn btn-brand rounded-pill w-100">
                                                            <i class="fi-rs-upload mr-5"></i> Konfirmasi Pembayaran Manual
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Status Card -->
                                @php
                                    $isSelfPickup = $order->expedition && ($order->expedition->code === 'self_pickup' || str_contains(strtolower($order->expedition->name), 'pickup'));
                                    $isKurirToko = $order->expedition && str_contains(strtolower($order->expedition->name), 'kurir toko');
                                @endphp
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 shadow-sm border-radius-15 overflow-hidden">
                                        <div class="card-header bg-info-light border-0 p-3">
                                            <h5 class="mb-0 text-info font-md">
                                                <i class="{{ $isSelfPickup ? 'fi-rs-shopping-bag' : 'fi-rs-truck-side' }} mr-10"></i>
                                                {{ $isSelfPickup ? 'Status Pengambilan (Self Pickup)' : 'Status Pengiriman' }}
                                            </h5>
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="info-list">
                                                <div class="info-item d-flex justify-content-between mb-3 pb-2 border-bottom">
                                                    <span class="text-dark font-sm">{{ $isSelfPickup ? 'Metode Pengambilan' : 'Kurir & Layanan' }}</span>
                                                    <span class="fw-bold font-sm text-dark text-end">
                                                        {{ $order->expedition ? $order->expedition->name : '-' }} 
                                                        <br><small class="text-dark fw-bold">({{ $order->expedition_service ?? ($isSelfPickup ? 'Ambil Sendiri' : 'Standard') }})</small>
                                                    </span>
                                                </div>
                                                <div class="info-item d-flex justify-content-between mb-3 pb-2 border-bottom align-items-center">
                                                    <span class="text-dark font-sm">{{ $isSelfPickup ? 'Info Pengambilan' : 'Nomor Resi' }}</span>
                                                    @if($isSelfPickup)
                                                        <span class="badge rounded-pill bg-success px-3 py-2 text-white font-sm" style="white-space: nowrap;"><i class="fi-rs-check mr-5"></i> Ambil Sendiri di Gudang</span>
                                                    @elseif($order->tracking_number || ($isKurirToko && in_array($order->order_status, ['shipped', 'delivered'])))
                                                        <div class="text-end">
                                                            @if($order->tracking_number)
                                                                <span class="fw-bold font-sm text-brand d-block">{{ $order->tracking_number }}</span>
                                                            @endif
                                                            
                                                            @if(!$isKurirToko && $order->tracking_number)
                                                                <a href="javascript:void(0)" class="font-xs text-info fw-bold" id="btn-track-order">
                                                                    <i class="fi-rs-search mr-5"></i> Lacak Sekarang
                                                                </a>

                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-dark font-sm fw-bold">Belum tersedia</span>
                                                    @endif
                                                </div>
                                                @if($isSelfPickup)
                                                <div class="info-item d-flex justify-content-between mb-2 pb-2 border-bottom">
                                                    <span class="text-dark font-sm">1. Jadwal Siap Diambil</span>
                                                    <span class="fw-bold font-sm {{ $order->pickup_ready_at ? 'text-success' : 'text-dark' }}">{{ $order->pickup_ready_at ? $order->pickup_ready_at->format('d M Y, H:i') . ' WIB' : 'Menunggu Jadwal' }}</span>
                                                </div>
                                                <div class="info-item d-flex justify-content-between mb-2 pb-2 border-bottom">
                                                    <span class="text-dark font-sm">2. Waktu Diserahkan (Handover)</span>
                                                    <span class="fw-bold font-sm {{ $order->shipped_at ? 'text-primary' : 'text-dark' }}">{{ $order->shipped_at ? $order->shipped_at->format('d M Y, H:i') . ' WIB' : 'Belum Diambil' }}</span>
                                                </div>
                                                <div class="info-item d-flex justify-content-between">
                                                    <span class="text-dark font-sm">3. Waktu Diterima Pembeli</span>
                                                    <span class="fw-bold font-sm {{ $order->received_at ? 'text-success' : 'text-dark' }}">{{ $order->received_at ? $order->received_at->format('d M Y, H:i') . ' WIB' : '-' }}</span>
                                                </div>
                                                @else
                                                <div class="info-item d-flex justify-content-between mb-2 pb-2 border-bottom">
                                                    <span class="text-dark font-sm">Tanggal Pengiriman</span>
                                                    <span class="fw-bold font-sm text-dark">{{ $order->shipped_at ? $order->shipped_at->format('d M Y') : '-' }}</span>
                                                </div>
                                                <div class="info-item d-flex justify-content-between">
                                                    <span class="text-dark font-sm">Waktu Diterima</span>
                                                    <span class="fw-bold font-sm {{ $order->received_at ? 'text-success' : 'text-dark' }}">{{ $order->received_at ? $order->received_at->format('d M Y, H:i') . ' WIB' : '-' }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($isKurirToko) && $isKurirToko && in_array($order->order_status, ['shipped', 'delivered']) && $order->order_status !== 'completed')
                                                <div class="mt-4 pt-3 border-top">
                                                    <form action="{{ route('distributor.orders.confirm-receipt', $order) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin pesanan sudah diterima dengan baik dan selesai?');">
                                                        @csrf
                                                        <button type="submit" class="btn w-100 shadow-sm" style="background-color: #3bb77e; border: none; color: white; padding: 12px; border-radius: 8px; font-weight: 600;">
                                                            <i class="fi-rs-check mr-5"></i> Konfirmasi Pesanan Diterima
                                                        </button>
                                                    </form>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Warehouse & Shipping Address Card -->
                                <div class="col-12 mt-4">
                                    <div class="card border border-light-2 shadow-sm border-radius-15 p-4">
                                        <div class="row g-4">
                                            @if($order->sourceWarehouse)
                                            <div class="col-md-6 border-end-md">
                                                <h6 class="mb-3 text-dark text-uppercase fw-bold letter-spacing-1 font-xs">{{ $isSelfPickup ? 'Lokasi Gudang Pengambilan' : 'Dikirim Dari' }}</h6>
                                                <div class="d-flex">
                                                    <div class="icon-circle bg-brand-light text-brand shadow-sm mr-15">
                                                        <i class="fi-rs-shop"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 text-dark fw-bold">{{ $order->sourceWarehouse->name }}</h6>
                                                        <p class="font-sm text-dark mb-0">
                                                            {{ $order->sourceWarehouse->address }},<br>
                                                            {{ $order->sourceWarehouse->full_location }}<br>
                                                            @if($order->sourceWarehouse->phone) 
                                                                <span class="font-sm mt-2 d-block text-dark fw-bold"><i class="fi-rs-smartphone mr-5"></i> {{ $order->sourceWarehouse->phone }}</span> 
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-{{ $order->sourceWarehouse ? '6' : '12' }}">
                                                <h6 class="mb-3 text-dark text-uppercase fw-bold letter-spacing-1 font-xs">Alamat Penerima</h6>
                                                <div class="d-flex">
                                                    <div class="icon-circle bg-info-light text-info shadow-sm mr-15">
                                                        <i class="fi-rs-marker"></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-sm text-dark fw-bold mb-1">
                                                            {!! nl2br(e($order->shipping_address)) !!}
                                                        </p>
                                                        @if($order->notes)
                                                            <div class="mt-2 p-2 bg-light border-radius-5 border-start border-3 border-brand">
                                                                <p class="font-sm text-dark mb-0 italic">
                                                                    <i class="fi-rs-edit mr-5"></i> Catatan: {{ $order->notes }}
                                                                </p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Items List -->
                                <div class="col-12 mt-4">
                                    <div class="card border-0 shadow-sm border-radius-15 overflow-hidden">
                                        <div class="card-header bg-white p-4 border-bottom">
                                            <h5 class="mb-0">Daftar Produk</h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-borderless align-middle mb-0">
                                                    <thead class="bg-brand-light">
                                                        <tr>
                                                            <th class="ps-4 py-3 font-sm text-dark fw-bold">Produk</th>
                                                            <th class="py-3 font-sm text-dark fw-bold text-center">Harga Satuan</th>
                                                            <th class="py-3 font-sm text-dark fw-bold text-center">Jumlah</th>
                                                            <th class="pe-4 py-3 font-sm text-dark fw-bold text-end">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($order->items as $item)
                                                            <tr class="border-bottom">
                                                                <td class="ps-4 py-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="product-img-wrap mr-15">
                                                                            <img src="{{ $item->product->image_url ? $item->product->image_url : asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}" 
                                                                                 alt="{{ $item->product->display_name }}" 
                                                                                 class="rounded" 
                                                                                 style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #eee;"
                                                                                 onerror="this.src='{{ asset('themes/nest-frontend/assets/imgs/shop/product-1-1.jpg') }}'">
                                                                        </div>
                                                                        <div>
                                                                            <h6 class="font-sm mb-1 text-dark">{{ $item->product->display_name }}</h6>
                                                                            <span class="font-xs text-muted">SKU: {{ $item->sku ?? '-' }}</span>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="py-3 text-center font-sm" data-label="Harga">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                                                <td class="py-3 text-center font-sm" data-label="Jumlah">{{ $item->orderedQuantityDescription() }}</td>
                                                                <td class="pe-4 py-3 text-end fw-bold text-brand" data-label="Subtotal">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-white p-4 border-top">
                                            <div class="row justify-content-end">
                                                <div class="col-md-6 col-lg-5">
                                                    <div class="price-summary">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="text-muted font-sm">Subtotal Produk</span>
                                                            <span class="font-sm text-dark fw-bold">Rp {{ number_format($order->subtotal ?? 0, 0, ',', '.') }}</span>
                                                        </div>
                                                        @if($order->discount_amount > 0)
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="text-muted font-sm">Diskon ({{ $order->discount_percent }}%)</span>
                                                            <span class="font-sm text-danger fw-bold">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                                                        </div>
                                                        @endif
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <span class="text-muted font-sm">Ongkos Kirim</span>
                                                            <span class="font-sm text-dark fw-bold">Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</span>
                                                        </div>
                                                        @if($order->payment_fee > 0)
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <span class="text-muted font-sm">Biaya Layanan</span>
                                                            <span class="font-sm text-dark fw-bold">Rp {{ number_format($order->payment_fee, 0, ',', '.') }}</span>
                                                        </div>
                                                        @endif
                                                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                                            <h5 class="mb-0 text-dark fw-bold">Total Harga</h5>
                                                            <h5 class="mb-0 text-brand fw-bold text-nowrap">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tracking Modal -->
<div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-radius-10">
      <div class="modal-header">
        <h5 class="modal-title" id="trackingModalLabel">Status Pengiriman</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="tracking-content" style="min-height: 200px;">
        <div class="text-center py-5">
            <div class="spinner-border text-brand" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Sedang melacak status pengiriman...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm rounded" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<style>
    .border-radius-15 { border-radius: 15px; }
    .bg-brand-light { background-color: rgba(59, 183, 126, 0.08); }
    .bg-info-light { background-color: rgba(61, 144, 239, 0.08); }
    .bg-light-gray { background-color: #f8fafc; }
    
    .icon-circle {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 18px;
    }
    
    .letter-spacing-1 { letter-spacing: 1px; }
    .italic { font-style: italic; }
    
    .info-item:last-child { border-bottom: 0 !important; margin-bottom: 0 !important; }
    
    .product-img-wrap {
        flex-shrink: 0;
    }
    
    @media (min-width: 768px) {
        .border-end-md { border-right: 1px solid #edf2f7; }
        .dashboard-content { padding-left: 50px; }
    }
    
    @media (max-width: 767px) {
        .badge-group { margin-top: 10px; width: 100%; }
        .badge-group .badge { width: 100%; display: block; text-align: center; }
        .card-header h5 { font-size: 14px; }
        .info-list .info-item { flex-direction: row; align-items: center; }
        .table thead { display: none; }
        .table tbody tr { display: block; padding: 15px; }
        .table tbody td { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 8px 0 !important;
            text-align: right !important;
            border: 0;
        }
        .table tbody td:first-child { 
            display: block; 
            text-align: left !important; 
            padding-bottom: 15px !important;
            border-bottom: 1px dashed #eee !important;
            margin-bottom: 10px;
        }
        .table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #777;
            text-align: left;
        }
        .table tbody td:first-child::before { display: none; }
        .price-summary { width: 100%; }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trackBtn = document.getElementById('btn-track-order');
    if (trackBtn) {
        trackBtn.addEventListener('click', function() {
            const modalEl = document.getElementById('trackingModal');
            let modalInstance;
            if (typeof bootstrap !== 'undefined') {
                modalInstance = new bootstrap.Modal(modalEl);
                modalInstance.show();
            } else {
                $(modalEl).modal('show');
            }

            const contentDiv = document.getElementById('tracking-content');
            contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-brand" role="status"></div><p class="mt-2">Loading...</p></div>';

            fetch('{{ route("buyer.orders.track", $order->id) }}')
                .then(response => response.json())
                .then(result => {
                    let html = '';
                    if (result.success && result.data) {
                        const data = result.data;

                        // RajaOngkir-like format
                        if (data.delivery_status && data.manifest) {
                            const status = data.delivery_status;
                            html += '<div class="alert alert-' + (status.status == 'DELIVERED' ? 'success' : 'info') + ' border-0 bg-light mb-4">';
                            html += '<h6 class="mb-2">Status Terakhir: ' + status.status + '</h6>';
                            html += '<p class="font-sm mb-0">Penerima: ' + (status.pod_receiver || '-') + '<br>';
                            html += 'Waktu: ' + (status.pod_date || '-') + ' ' + (status.pod_time || '') + '</p>';
                            html += '</div>';

                            html += '<div class="timeline-container px-3">';
                            if (data.manifest && data.manifest.length > 0) {
                                data.manifest.forEach(item => {
                                    html += '<div class="mb-4 position-relative border-start ps-4 ml-10">';
                                    html += '<span class="position-absolute translate-middle-x bg-brand rounded-circle" style="left:0; top:5px; width:12px; height:12px;"></span>';
                                    html += '<h6 class="font-sm mb-1">' + item.manifest_description + '</h6>';
                                    html += '<p class="font-xs text-muted mb-1">' + item.manifest_date + ' ' + item.manifest_time + '</p>';
                                    if (item.city_name) {
                                        html += '<span class="font-xs"><i class="fi-rs-marker mr-5"></i>' + item.city_name + '</span>';
                                    }
                                    html += '</div>';
                                });
                            } else {
                                html += '<p class="text-center text-muted py-4">Tidak ada data manifest detail.</p>';
                            }
                            html += '</div>';
                        } else if (data.carriers && data.carriers.length > 0) {
                            // EkspedisiKu normalized format
                            const carrier = data.carriers[0];
                            const events = carrier.events || [];
                            const latest = events.length > 0 ? events[0] : null;

                            html += '<div class="alert alert-info border-0 bg-light mb-4">';
                            html += '<h6 class="mb-2">' + (carrier.label || carrier.id || 'Tracking') + '</h6>';
                            if (latest) {
                                html += '<p class="font-sm mb-0">';
                                html += 'Status: <strong>' + (latest.status || '-') + '</strong><br>';
                                html += 'Waktu: ' + (latest.time || '-') + '<br>';
                                html += 'Lokasi: ' + (latest.location || '-') + '<br>';
                                html += (latest.remarks ? ('Keterangan: ' + latest.remarks) : '');
                                html += '</p>';
                            } else {
                                html += '<p class="font-sm mb-0">Tidak ada event tracking.</p>';
                            }
                            html += '</div>';

                            html += '<div class="timeline-container px-3">';
                            if (events.length > 0) {
                                events.forEach(item => {
                                    html += '<div class="mb-4 position-relative border-start ps-4 ml-10">';
                                    html += '<span class="position-absolute translate-middle-x bg-brand rounded-circle" style="left:0; top:5px; width:12px; height:12px;"></span>';
                                    html += '<h6 class="font-sm mb-1">' + (item.status || '-') + '</h6>';
                                    html += '<p class="font-xs text-muted mb-1">' + (item.time || '-') + '</p>';
                                    if (item.location) {
                                        html += '<span class="font-xs"><i class="fi-rs-marker mr-5"></i>' + item.location + '</span>';
                                    }
                                    if (item.remarks) {
                                        html += '<div class="font-xs text-muted mt-1">' + item.remarks + '</div>';
                                    }
                                    html += '</div>';
                                });
                            } else {
                                html += '<p class="text-center text-muted py-4">Tidak ada data tracking.</p>';
                            }
                            html += '</div>';
                        } else {
                            html = '<div class="alert alert-warning border-0 bg-light">Format data tracking tidak dikenali.</div>';
                        }
                    } else {
                        html = '<div class="alert alert-warning border-0 bg-light">' + (result.message || 'Data pelacakan saat ini belum tersedia') + '</div>';
                    }
                    contentDiv.innerHTML = html;
                })
                .catch(error => {
                    contentDiv.innerHTML = '<div class="alert alert-danger border-0 bg-light">Terjadi kesalahan: ' + error.message + '</div>';
                });
        });
    }
});
</script>
@endpush
@endsection








