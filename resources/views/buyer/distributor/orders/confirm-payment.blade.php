@extends('layouts.shop')

@section('title', 'Konfirmasi Pembayaran')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('distributor.orders.history') }}">Riwayat Order</a>
            <span></span> <a href="{{ route('distributor.orders.show', $order) }}">Detail</a>
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

                                    <form action="{{ route('distributor.orders.confirm-payment.store', $order) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-lg-8">
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
                                                    <a href="{{ route('distributor.orders.show', $order) }}" class="btn btn-secondary rounded-pill px-4">Batal</a>
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
                                                    <p class="font-xs text-muted">Konfirmasi Anda akan ditinjau oleh tim admin kami (pusat) dalam waktu maksimal 1x24 jam.</p>
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
