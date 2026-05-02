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

<div class="page-content pt-50 pb-80" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-lg-8">
                <div class="tab-content account dashboard-content">
                    <div class="tab-pane fade show active" role="tabpanel">
                        <div class="card border-0 shadow-sm border-radius-20 overflow-hidden mb-40" style="background-color: #ffffff;">
                            <div class="card-header bg-white border-bottom-0 p-30 pb-0">
                                <h3 class="mb-0" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Program Afiliasi</h3>
                                <p class="text-muted font-sm">Dapatkan poin dengan mengajak orang lain berbelanja di platform kami.</p>
                            </div>
                            <div class="card-body p-30 pt-10">
                                @if(session('success'))
                                    <div class="alert alert-success border-0 mb-30" style="border-radius: 12px;" role="alert">
                                        <i class="fi-rs-check mr-5"></i>{{ session('success') }}
                                    </div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger border-0 mb-30" style="border-radius: 12px;" role="alert">
                                        <i class="fi-rs-cross-circle mr-5"></i>{{ session('error') }}
                                    </div>
                                @endif
                                @if(session('warning'))
                                    <div class="alert border-0 mb-30" style="border-radius: 12px; background-color: #fff8e6; border: 1px solid #f6d365 !important; color: #744210;" role="alert">
                                        <i class="fi-rs-info mr-5"></i>{{ session('warning') }}
                                    </div>
                                @endif

                                <div class="row align-items-center p-30 border-radius-15 mb-40" style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;">
                                    <div class="col-md-7">
                                        <h5 class="mb-10" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;">Kode Afiliasi Anda</h5>
                                        <div class="d-flex align-items-center">
                                            <h3 class="text-maroon mb-0 me-3" id="affiliate-code" style="font-family: 'Fira Sans', sans-serif; font-weight: 700;">{{ $user->referral_code }}</h3>
                                            <button class="btn btn-sm btn-maroon px-20 btn-copy" data-clipboard-text="{{ $user->referral_code }}">
                                                <i class="fi-rs-copy me-1"></i>Salin Kode
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-5 mt-30 mt-md-0 text-md-end border-start-md ps-md-4">
                                        <p class="font-xs text-muted mb-5">Poin Tersedia (Bisa ditarik)</p>
                                        <h3 class="mb-10 text-maroon" style="font-family: 'Fira Sans', sans-serif; font-weight: 700;">{{ number_format($user->points, 0, ',', '.') }} <span class="font-xs text-muted fw-normal">/ {{ number_format($totalReferralPoints, 0, ',', '.') }} (Total)</span></h3>
                                        @if($user->isDriippreneurApproved())
                                        <a href="{{ route('buyer.point-withdrawals.index') }}" class="btn btn-sm btn-maroon w-100 py-10" style="border-radius: 10px;">
                                            <i class="fi-rs-money mt-1"></i> Tarik Poin
                                        </a>
                                        @else
                                        <a href="{{ route('buyer.driippreneur.status') }}" class="btn btn-sm btn-secondary w-100 py-10" style="border-radius: 10px;" title="Daftar jadi Affiliator untuk menarik poin">
                                            <i class="fi-rs-lock mt-1"></i> Verifikasi untuk Tarik
                                        </a>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-30 border-radius-15 mb-40" style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;">
                                    <h5 class="mb-10" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Link Afiliasi Saya</h5>
                                    <p class="font-sm text-muted mb-20">Bagikan link ini ke teman Anda. Jika mereka belanja via link ini, Anda dapat poin!</p>
                                    <div class="input-group">
                                        <input type="text" class="form-control font-sm custom-input-aff" id="referral-link" value="{{ route('home', ['ref' => $user->referral_code]) }}" readonly>
                                        <button class="btn btn-maroon px-25 btn-copy" data-clipboard-text="{{ route('home', ['ref' => $user->referral_code]) }}">
                                            <i class="fi-rs-copy"></i>
                                        </button>
                                    </div>
                                </div>

                                @unless($user->isDistributor())
                                <div id="affiliate-bank-section" class="p-30 border-radius-15 mb-40 buyer-affiliate-bank" style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;">
                                    <h5 class="mb-10" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;"><i class="fi-rs-bank mr-5"></i> Informasi Rekening Bank</h5>
                                    <p class="font-sm text-muted mb-20">Untuk transfer penarikan poin. Nomor rekening pertama kali Anda isi saat mendaftar Affiliator; perbarui di sini jika ada perubahan.</p>
                                    <form action="{{ route('buyer.affiliate.bank.update') }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        @include('buyer.partials.bank-account-fields')
                                        <button type="submit" class="btn btn-maroon mt-10">Simpan rekening</button>
                                    </form>
                                </div>
                                @endunless

                                <div class="row g-4 mb-40">
                                    <div class="col-md-12">
                                        <div class="card border-radius-20 border-0 p-30" style="background: linear-gradient(135deg, #6A1B1B 0%, #4D1313 100%); color: white;">
                                            <h5 class="text-white mb-20" style="font-family: 'Fira Sans', sans-serif; font-weight: 700;">Bagaimana cara kerjanya?</h5>
                                            <div class="row mt-10">
                                                <div class="col-md-4 mb-20 mb-md-0">
                                                    <div class="d-flex flex-column align-items-center text-center px-2">
                                                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mb-15 shadow-sm" style="width: 44px; height: 44px;">
                                                            <span class="text-maroon fw-bold" style="font-size: 18px;">1</span>
                                                        </div>
                                                        <p class="font-xs mb-0 text-white" style="opacity: 0.9;">Bagikan kode atau link afiliasi Anda ke teman atau sosial media.</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-20 mb-md-0">
                                                    <div class="d-flex flex-column align-items-center text-center px-2">
                                                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mb-15 shadow-sm" style="width: 44px; height: 44px;">
                                                            <span class="text-maroon fw-bold" style="font-size: 18px;">2</span>
                                                        </div>
                                                        <p class="font-xs mb-0 text-white" style="opacity: 0.9;">Teman Anda menggunakan kode tersebut saat checkout atau via link Anda.</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="d-flex flex-column align-items-center text-center px-2">
                                                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mb-15 shadow-sm" style="width: 44px; height: 44px;">
                                                            <span class="text-maroon fw-bold" style="font-size: 18px;">3</span>
                                                        </div>
                                                        <p class="font-xs mb-0 text-white" style="opacity: 0.9;">Dapatkan <strong>1 Poin</strong> untuk setiap barang yang dibeli oleh mereka!</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-20" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Riwayat Referral</h5>
                                <div class="table-responsive">
                                    <table class="table table-clean font-sm w-100">
                                        <thead>
                                            <tr class="main-heading">
                                                <th>Pembeli</th>
                                                <th>No. Pesanan</th>
                                                <th>Tanggal</th>
                                                <th>Total Belanja</th>
                                                <th>Poin Didapat</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($referralOrders as $order)
                                                <tr>
                                                    <td class="fw-bold" style="color: #253D4E;">{{ $order->user->name ?? 'User' }}</td>
                                                    <td class="text-muted">#{{ $order->order_number }}</td>
                                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                                    <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                                    <td><strong class="text-maroon">+{{ number_format($order->affiliate_points, 0, ',', '.') }}</strong></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-50 text-muted">Belum ada riwayat referral.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-20">
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

<style>
    .text-maroon { color: #6A1B1B !important; }
    .btn-maroon { background-color: #6A1B1B !important; color: #fff !important; border: none !important; border-radius: 12px !important; font-weight: 600; padding: 12px 20px; transition: all 0.3s; }
    .btn-maroon:hover { background-color: #4D1313 !important; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(106, 27, 27, 0.2); }
    
    .custom-input-aff { background-color: #fff !important; border: 1.5px solid #ECECEC !important; border-radius: 12px 0 0 12px !important; padding: 12px 15px !important; }
    .input-group .btn-maroon { border-radius: 0 12px 12px 0 !important; }

    .table-clean thead th { 
        border-top: 0; 
        border-bottom: 2px solid #f2f2f2; 
        color: #253D4E; 
        font-family: 'Fira Sans', sans-serif;
        font-weight: 700; 
        padding-bottom: 15px; 
    }
    .table-clean tbody td { vertical-align: middle; padding: 18px 0; border-top: 0; border-bottom: 1px solid #f2f2f2; }
    
    @media (min-width: 768px) {
        .border-start-md { border-left: 1.5px solid #ECECEC !important; }
    }

    /* Select2: container default inline-block — paksa full width kolom */
    .buyer-affiliate-bank .select2-container {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .buyer-affiliate-bank .form-label-affiliate-bank {
        font-weight: 600;
        color: #253D4E;
        margin-bottom: 8px;
        font-size: 14px;
        display: block;
    }
    .buyer-affiliate-bank .custom-input-affiliate-bank {
        background-color: #fff !important;
        border: 1.5px solid #ECECEC !important;
        border-radius: 12px !important;
        padding: 12px 16px !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .buyer-affiliate-bank .custom-input-affiliate-bank:focus {
        border-color: #6A1B1B !important;
        box-shadow: 0 0 0 3px rgba(106, 27, 27, 0.08) !important;
        outline: none;
    }
    .buyer-affiliate-bank .select2-container--default .select2-selection--single {
        height: auto !important;
        min-height: 48px;
        padding: 10px 14px !important;
        border: 1.5px solid #ECECEC !important;
        border-radius: 12px !important;
        background-color: #fff !important;
    }
    .buyer-affiliate-bank .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.4 !important;
        padding-left: 0 !important;
        color: #253D4E;
    }
    .buyer-affiliate-bank .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 10px !important;
    }

    /*
     * Select2 memakai input[type=search]. WebKit/Blink menambahkan kontrol hijau (ikon cari).
     * Hilangkan agar tampilan netral seperti input teks biasa.
     */
    .buyer-affiliate-bank .select2-dropdown .select2-search__field {
        -webkit-appearance: none !important;
        appearance: none !important;
        background-image: none !important;
    }
    .buyer-affiliate-bank .select2-dropdown .select2-search__field::-webkit-search-decoration,
    .buyer-affiliate-bank .select2-dropdown .select2-search__field::-webkit-search-cancel-button,
    .buyer-affiliate-bank .select2-dropdown .select2-search__field::-webkit-search-results-button,
    .buyer-affiliate-bank .select2-dropdown .select2-search__field::-webkit-search-results-decoration {
        -webkit-appearance: none !important;
        appearance: none !important;
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
</style>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        var $bankWrap = $('.buyer-affiliate-bank');
        if ($bankWrap.length && $.fn.select2) {
            $bankWrap.find('select.select2-bank-affiliate').each(function () {
                var $sel = $(this);
                $sel.select2({
                    placeholder: $sel.data('placeholder') || 'Pilih bank',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $bankWrap
                });
            });
        }

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
