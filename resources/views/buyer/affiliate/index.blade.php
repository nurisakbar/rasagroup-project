@extends('layouts.shop')

@section('title', 'Program Afiliasi')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Akun Saya
            <span></span> Afiliasi
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
                            <div class="card border-0 shadow-sm border-radius-10 mb-4">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <h3 class="mb-0">Program Afiliasi</h3>
                                    <p class="text-muted font-sm">Dapatkan poin dengan mengajak orang lain berbelanja di platform kami.</p>
                                </div>
                                <div class="card-body p-4 pt-0">
                                    <div class="row align-items-center bg-brand-light p-4 border-radius-10 mb-4">
                                        <div class="col-md-7">
                                            <h5 class="mb-2">Kode Afiliasi Anda</h5>
                                            <div class="d-flex align-items-center">
                                                <h3 class="text-brand mb-0 me-3" id="affiliate-code">{{ $user->referral_code }}</h3>
                                                <button class="btn btn-sm btn-brand btn-copy" data-clipboard-text="{{ $user->referral_code }}">
                                                    <i class="fi-rs-copy me-1"></i>Salin Kode
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-5 mt-3 mt-md-0 text-md-end">
                                            <p class="font-xs text-muted mb-1">Total Poin Afiliasi</p>
                                            <h3 class="mb-0">{{ number_format($totalReferralPoints, 0, ',', '.') }} <span class="font-xs">Poin</span></h3>
                                        </div>
                                    </div>

                                    <div class="card border-0 bg-light p-4 border-radius-10 mb-4">
                                        <h5 class="mb-3">Link Afiliasi Saya</h5>
                                        <p class="font-sm text-muted mb-3">Bagikan link ini kepada teman-teman Anda. Jika mereka berbelanja melalui link ini, Anda akan mendapatkan poin!</p>
                                        <div class="input-group">
                                            <input type="text" class="form-control font-sm" id="referral-link" value="{{ route('home', ['ref' => $user->referral_code]) }}" readonly>
                                            <button class="btn btn-brand btn-copy" data-clipboard-text="{{ route('home', ['ref' => $user->referral_code]) }}">
                                                <i class="fi-rs-copy"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="row g-4 mb-4">
                                        <div class="col-md-12">
                                            <div class="card border-radius-10 border-0 p-4" style="background: linear-gradient(135deg, #3BB77E 0%, #2ecc71 100%); color: white;">
                                                <h5 class="text-white mb-2">Bagaimana cara kerjanya?</h5>
                                                <div class="row mt-3">
                                                    <div class="col-md-4">
                                                        <div class="d-flex flex-column align-items-center text-center">
                                                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 40px; height: 40px;">
                                                                <span class="text-brand fw-bold">1</span>
                                                            </div>
                                                            <p class="font-xs mb-0">Bagikan kode atau link afiliasi Anda ke teman atau sosial media.</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="d-flex flex-column align-items-center text-center">
                                                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 40px; height: 40px;">
                                                                <span class="text-brand fw-bold">2</span>
                                                            </div>
                                                            <p class="font-xs mb-0">Teman Anda menggunakan kode tersebut saat checkout atau melalui link Anda.</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="d-flex flex-column align-items-center text-center">
                                                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 40px; height: 40px;">
                                                                <span class="text-brand fw-bold">3</span>
                                                            </div>
                                                            <p class="font-xs mb-0">Dapatkan 1 Poin untuk setiap barang yang dibeli oleh mereka!</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mb-3">Riwayat Referral</h5>
                                    <div class="table-responsive">
                                        <table class="table table-clean font-sm">
                                            <thead>
                                                <tr class="main-heading">
                                                    <th>Pembeli</th>
                                                    <th>No. Pesanan</th>
                                                    <th>Tanggal</th>
                                                    <th>Poin Didapat</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($referralOrders as $order)
                                                    <tr>
                                                        <td>{{ $order->user->name ?? 'User' }}</td>
                                                        <td>#{{ $order->order_number }}</td>
                                                        <td>{{ $order->created_at->format('d M Y') }}</td>
                                                        <td><strong class="text-brand">+{{ number_format($order->affiliate_points, 0, ',', '.') }}</strong></td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center py-4">Belum ada riwayat referral.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        {{ $referralOrders->links() }}
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
@endsection

@push('styles')
<style>
    .bg-brand-light { background-color: rgba(59, 183, 126, 0.08); }
    .border-radius-10 { border-radius: 10px !important; }
    .btn-copy { border-radius: 5px !important; }
    #referral-link { background-color: #fff; cursor: pointer; }
    .table-clean thead th { border-top: 0; border-bottom: 1px solid #f2f2f2; color: #253D4E; font-weight: 700; }
    .table-clean tbody td { vertical-align: middle; padding: 15px 0; border-top: 0; border-bottom: 1px solid #f2f2f2; }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>
<script>
    $(document).ready(function() {
        var clipboard = new ClipboardJS('.btn-copy');
        
        clipboard.on('success', function(e) {
            var originalText = $(e.trigger).html();
            $(e.trigger).html('<i class="fi-rs-check me-1"></i>Tersalin!');
            $(e.trigger).addClass('btn-success').removeClass('btn-brand');
            
            setTimeout(function() {
                $(e.trigger).html(originalText);
                $(e.trigger).removeClass('btn-success').addClass('btn-brand');
            }, 2000);
            
            e.clearSelection();
        });
    });
</script>
@endpush
