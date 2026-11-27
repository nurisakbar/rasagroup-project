@extends('layouts.shop')

@section('title', 'Konfirmasi Pembayaran')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('buyer.orders.index') }}">Pesanan Saya</a>
            <span></span> <a href="{{ route('buyer.orders.show', $order) }}">Detail</a>
            <span></span> Konfirmasi Pembayaran
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
                            <div class="card border-0 shadow-sm border-radius-10 overflow-hidden">
                                <div class="card-header bg-white border-bottom p-4">
                                    <h3 class="mb-0">Konfirmasi Pembayaran</h3>
                                    <p class="text-muted font-sm">Kirim bukti transfer untuk pesanan #{{ $order->order_number }}</p>
                                </div>
                                <div class="card-body p-4">
                                    <div class="alert alert-info font-sm border-radius-10 mb-4">
                                        <i class="fi-rs-info mr-5"></i> Harap pastikan bukti transfer terlihat jelas dengan nominal yang sesuai yaitu <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                                    </div>

                                    <form action="{{ route('buyer.orders.confirm-payment.store', $order) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-lg-8">
                                                @if($order->isPaidToDistributor())
                                                    <div class="form-group mb-4">
                                                        <label class="form-label fw-bold">Saya membayar ke rekening <span class="text-danger">*</span></label>
                                                        @if($destinationAccounts->isEmpty())
                                                            <div class="alert alert-warning font-sm mb-0">Distributor belum mengatur rekening aktif. Hubungi distributor Anda.</div>
                                                        @else
                                                            <div class="dest-accounts">
                                                                @foreach($destinationAccounts as $account)
                                                                    <label class="dest-account">
                                                                        <input type="radio" name="destination_account_id" value="{{ $account->id }}" required
                                                                               @checked(old('destination_account_id', $destinationAccounts->count() === 1 ? $account->id : null) === $account->id)>
                                                                        <span class="dest-box">
                                                                            <span class="dest-check"></span>
                                                                            <span class="dest-text">
                                                                                <span class="dest-bank">{{ $account->bank_name }}</span>
                                                                                <span class="dest-number">{{ trim(chunk_split($account->account_number, 4, ' ')) }}</span>
                                                                                <span class="dest-name">a.n. {{ $account->account_name }}</span>
                                                                            </span>
                                                                        </span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @error('destination_account_id')
                                                            <div class="text-danger small mt-2">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <style>
                                                        .dest-accounts { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px; }
                                                        .dest-account { margin: 0; cursor: pointer; }
                                                        .dest-account input { position: absolute; opacity: 0; pointer-events: none; }
                                                        .dest-box { display: flex; gap: 12px; align-items: flex-start; padding: 14px; border: 1.5px solid #EADFD4; border-radius: 14px; background: #fff; transition: all .2s; height: 100%; }
                                                        .dest-check { flex: 0 0 20px; height: 20px; border-radius: 50%; border: 2px solid #D9C3B0; margin-top: 2px; position: relative; }
                                                        .dest-account input:checked + .dest-box { border-color: #6A1B1B; box-shadow: 0 6px 16px rgba(106,27,27,.12); background: #FFFBF8; }
                                                        .dest-account input:checked + .dest-box .dest-check { border-color: #6A1B1B; }
                                                        .dest-account input:checked + .dest-box .dest-check::after { content: ''; position: absolute; inset: 3px; border-radius: 50%; background: #6A1B1B; }
                                                        .dest-text { display: flex; flex-direction: column; min-width: 0; }
                                                        .dest-bank { font-size: 12px; font-weight: 700; color: #8A7B70; text-transform: uppercase; letter-spacing: .5px; }
                                                        .dest-number { font-family: 'Fira Sans', sans-serif; font-size: 18px; font-weight: 700; color: #253D4E; letter-spacing: .5px; word-break: break-all; }
                                                        .dest-name { font-size: 13px; color: #6B7280; }
                                                    </style>
                                                @endif

                                                <div class="form-group mb-4">
                                                    <label class="form-label fw-bold">Bukti Transfer (Image) <span class="text-danger">*</span></label>
                                                    <input type="file" name="payment_proof" class="form-control" accept="image/*" required>
                                                    <p class="font-xs text-muted mt-2">Format: JPG, PNG, WEBP. Maks: 2MB</p>
                                                </div>

                                                <div class="form-group mb-4">
                                                    <label class="form-label fw-bold">Catatan Tambahan (Opsional)</label>
                                                    <textarea name="payment_submit_note" class="form-control" rows="4" placeholder="Misal: Transfer via Bank BCA an..."></textarea>
                                                </div>

                                                <div class="d-flex align-items-center">
                                                    <button type="submit" class="btn btn-brand rounded-pill px-5 py-3 me-3">Kirim Konfirmasi</button>
                                                    <a href="{{ route('buyer.orders.show', $order) }}" class="btn btn-secondary rounded-pill px-4">Batal</a>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-4 mt-lg-0 mt-4">
                                                <div class="p-4 border border-radius-15 bg-light h-100">
                                                    <h6 class="mb-3">Informasi Pesanan</h6>
                                                    <div class="mb-3">
                                                        <span class="text-muted font-xs d-block">Nomor Pesanan:</span>
                                                        <span class="fw-bold">{{ $order->order_number }}</span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <span class="text-muted font-xs d-block">Total Tagihan:</span>
                                                        <span class="text-brand fw-bold fs-5">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                                    </div>
                                                    <hr>
                                                    @if($order->isPaidToDistributor() && $order->fulfilled_by_distributor)
                                                        <p class="font-xs text-muted">Konfirmasi Anda akan ditinjau oleh distributor <strong>{{ $order->paymentDistributor?->name }}</strong>.</p>
                                                    @else
                                                        <p class="font-xs text-muted">Konfirmasi Anda akan ditinjau oleh tim admin kami (pusat) dalam waktu maksimal 1x24 jam.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
