@extends('layouts.shop')

@section('title', 'Status Affiliator')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Status Affiliator
        </div>
    </div>
</div>

<div class="page-content pt-50 pb-80 buyer-driip-status-page">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-lg-8">
                <div class="tab-content account dashboard-content">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="card border-0 shadow-sm border-radius-20 overflow-hidden">
                                <div class="card-header bg-white border-bottom-0 p-30 pb-0">
                                    <h3 class="mb-0 buyer-driip-status-title">Status Pengajuan Affiliator</h3>
                                    <p class="text-muted font-sm mb-0 mt-2">Pantau proses verifikasi pengajuan Anda di sini.</p>
                                </div>
                                <div class="card-body p-30 pt-20">
                                    @if($user->driippreneur_status === 'pending')
                                        <div class="buyer-driip-status-hero buyer-driip-status-hero--pending">
                                            <div class="buyer-driip-status-hero-icon">
                                                <i class="fi-rs-time-past"></i>
                                            </div>
                                            <div class="buyer-driip-status-hero-body">
                                                <div class="buyer-driip-status-hero-top">
                                                    <h4 class="buyer-driip-status-hero-title mb-0">Menunggu verifikasi</h4>
                                                    <span class="badge rounded-pill buyer-driip-badge buyer-driip-badge--pending">Dalam proses</span>
                                                </div>
                                                <p class="buyer-driip-status-hero-desc mb-0">Pengajuan Anda sudah kami terima. Tim kami sedang melakukan pengecekan dokumen.</p>
                                            </div>
                                            <div class="buyer-driip-status-hero-aside">
                                                <div class="buyer-driip-meta-label">Diajukan pada</div>
                                                <div class="buyer-driip-meta-value">{{ $user->driippreneur_applied_at->format('d F Y, H:i') }} WIB</div>
                                            </div>
                                        </div>

                                        <div class="row g-3 mt-1">
                                            <div class="col-12 col-md-6">
                                                <div class="buyer-driip-info-card">
                                                    <div class="buyer-driip-meta-label">No. KTP</div>
                                                    <div class="buyer-driip-meta-strong">{{ $user->no_ktp }}</div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="buyer-driip-info-card">
                                                    <div class="buyer-driip-meta-label">No. NPWP</div>
                                                    <div class="buyer-driip-meta-strong">{{ $user->no_npwp }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="buyer-driip-callout mt-3">
                                            <div class="buyer-driip-callout-icon">
                                                <i class="fi-rs-info"></i>
                                            </div>
                                            <div class="buyer-driip-callout-body">
                                                <div class="buyer-driip-callout-title">Estimasi verifikasi 1–3 hari kerja</div>
                                                <ul class="buyer-driip-callout-list mb-0">
                                                    <li>Kami memeriksa kecocokan KTP, NPWP, dan selfie.</li>
                                                    <li>Jika ada kekurangan, kami akan menghubungi Anda untuk perbaikan.</li>
                                                    <li>Setelah disetujui, fitur penarikan poin akan aktif otomatis.</li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-end mt-4">
                                            <a href="{{ route('products.index') }}" class="btn btn-standar-utama">
                                                <i class="fi-rs-shopping-bag mr-5"></i> Lanjut belanja
                                            </a>
                                            <a href="{{ route('contact') }}" class="btn btn-standar-outline">
                                                <i class="fi-rs-headphones mr-5"></i> Hubungi kami
                                            </a>
                                        </div>

                                    @elseif($user->driippreneur_status === 'approved')
                                        <div class="buyer-driip-status-hero buyer-driip-status-hero--approved">
                                            <div class="buyer-driip-status-hero-icon">
                                                <i class="fi-rs-check"></i>
                                            </div>
                                            <div class="buyer-driip-status-hero-body">
                                                <div class="buyer-driip-status-hero-top">
                                                    <h4 class="buyer-driip-status-hero-title mb-0">Pengajuan disetujui</h4>
                                                    <span class="badge rounded-pill buyer-driip-badge buyer-driip-badge--approved">Aktif</span>
                                                </div>
                                                <p class="buyer-driip-status-hero-desc mb-0">Selamat! Anda sekarang berstatus Affiliator dan akan mendapatkan poin setelah pesanan selesai.</p>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-end mt-4">
                                            <a href="{{ route('products.index') }}" class="btn btn-standar-utama">
                                                <i class="fi-rs-shopping-bag mr-5"></i> Mulai belanja
                                            </a>
                                            <a href="{{ route('buyer.dashboard') }}" class="btn btn-standar-outline">
                                                <i class="fi-rs-arrow-left mr-5"></i> Dashboard
                                            </a>
                                        </div>

                                    @elseif($user->driippreneur_status === 'rejected')
                                        <div class="buyer-driip-status-hero buyer-driip-status-hero--rejected">
                                            <div class="buyer-driip-status-hero-icon">
                                                <i class="fi-rs-cross-circle"></i>
                                            </div>
                                            <div class="buyer-driip-status-hero-body">
                                                <div class="buyer-driip-status-hero-top">
                                                    <h4 class="buyer-driip-status-hero-title mb-0">Pengajuan ditolak</h4>
                                                    <span class="badge rounded-pill buyer-driip-badge buyer-driip-badge--rejected">Perlu tindakan</span>
                                                </div>
                                                <p class="buyer-driip-status-hero-desc mb-0">Anda bisa menghubungi layanan pelanggan untuk bantuan atau arahan pengajuan ulang.</p>
                                            </div>
                                        </div>
                                        <div class="buyer-driip-callout mt-3">
                                            <div class="buyer-driip-callout-icon">
                                                <i class="fi-rs-info"></i>
                                            </div>
                                            <div class="buyer-driip-callout-body">
                                                <div class="buyer-driip-callout-title">Butuh bantuan?</div>
                                                <div class="text-muted font-sm">Jika Anda merasa ada kesalahan atau ingin mengajukan ulang, silakan hubungi layanan pelanggan.</div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-end mt-4">
                                            <a href="{{ route('contact') }}" class="btn btn-standar-utama">
                                                <i class="fi-rs-headphones mr-5"></i> Hubungi kami
                                            </a>
                                            <a href="{{ route('buyer.dashboard') }}" class="btn btn-standar-outline">
                                                <i class="fi-rs-arrow-left mr-5"></i> Dashboard
                                            </a>
                                        </div>

                                    @else
                                        <div class="buyer-driip-status-hero buyer-driip-status-hero--empty">
                                            <div class="buyer-driip-status-hero-icon">
                                                <i class="fi-rs-document"></i>
                                            </div>
                                            <div class="buyer-driip-status-hero-body">
                                                <div class="buyer-driip-status-hero-top">
                                                    <h4 class="buyer-driip-status-hero-title mb-0">Belum ada pengajuan</h4>
                                                    <span class="badge rounded-pill buyer-driip-badge buyer-driip-badge--empty">Belum daftar</span>
                                                </div>
                                                <p class="buyer-driip-status-hero-desc mb-0">Ajukan pendaftaran untuk mengaktifkan fitur Affiliator dan penarikan poin.</p>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-end mt-4">
                                            <a href="{{ route('buyer.driippreneur.apply') }}" class="btn btn-standar-utama">
                                                <i class="fi-rs-add mr-5"></i> Daftar sekarang
                                            </a>
                                            <a href="{{ route('buyer.dashboard') }}" class="btn btn-standar-outline">
                                                <i class="fi-rs-arrow-left mr-5"></i> Dashboard
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .buyer-driip-status-page {
        background-color: #F2EAE1;
    }
    .buyer-driip-status-title {
        font-family: 'Fira Sans', sans-serif;
        font-weight: 800;
        color: #253D4E;
        letter-spacing: -0.01em;
    }
    .buyer-driip-status-hero {
        display: grid;
        grid-template-columns: 56px 1fr;
        gap: 14px 16px;
        padding: 18px 18px;
        border-radius: 16px;
        border: 1.5px solid #ECECEC;
        background: #fff;
        align-items: start;
    }
    @media (min-width: 768px) {
        .buyer-driip-status-hero {
            grid-template-columns: 56px 1fr auto;
            align-items: center;
        }
    }
    .buyer-driip-status-hero-icon {
        width: 56px;
        height: 56px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .buyer-driip-status-hero-title {
        font-weight: 800;
        color: #253D4E;
        letter-spacing: -0.01em;
    }
    .buyer-driip-status-hero-desc {
        color: #6B7280;
        font-size: 0.95rem;
        line-height: 1.45;
    }
    .buyer-driip-status-hero-top {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }
    .buyer-driip-status-hero-aside {
        text-align: left;
    }
    @media (min-width: 768px) {
        .buyer-driip-status-hero-aside {
            text-align: right;
        }
    }
    .buyer-driip-meta-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 700;
        color: #8B7355;
    }
    .buyer-driip-meta-value {
        font-weight: 800;
        color: #6A1B1B;
    }
    .buyer-driip-meta-strong {
        font-weight: 800;
        color: #253D4E;
        letter-spacing: 0.01em;
    }
    .buyer-driip-info-card {
        padding: 14px 16px;
        border-radius: 14px;
        background: #fff;
        border: 1.5px solid #ECECEC;
    }
    .buyer-driip-callout {
        display: flex;
        gap: 12px;
        padding: 16px 16px;
        border-radius: 16px;
        border: 1.5px solid #ECECEC;
        background: #F8F9FA;
        align-items: flex-start;
    }
    .buyer-driip-callout-icon {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(106, 27, 27, 0.12);
        color: #6A1B1B;
        flex-shrink: 0;
    }
    .buyer-driip-callout-title {
        font-weight: 800;
        color: #253D4E;
        margin-bottom: 4px;
    }
    .buyer-driip-callout-list {
        color: #6B7280;
        font-size: 0.9rem;
        padding-left: 1.1rem;
    }

    .buyer-driip-badge {
        font-weight: 700;
        padding: 8px 12px;
        border: 1px solid transparent;
    }
    .buyer-driip-badge--pending {
        background: rgba(255, 193, 7, 0.16);
        color: #7a5a00;
        border-color: rgba(255, 193, 7, 0.35);
    }
    .buyer-driip-badge--approved {
        background: rgba(40, 167, 69, 0.12);
        color: #1f7a35;
        border-color: rgba(40, 167, 69, 0.28);
    }
    .buyer-driip-badge--rejected {
        background: rgba(220, 53, 69, 0.12);
        color: #a61b2d;
        border-color: rgba(220, 53, 69, 0.25);
    }
    .buyer-driip-badge--empty {
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        border-color: rgba(37, 99, 235, 0.18);
    }

    .buyer-driip-status-hero--pending .buyer-driip-status-hero-icon {
        background: rgba(255, 193, 7, 0.12);
        color: #b88300;
    }
    .buyer-driip-status-hero--approved .buyer-driip-status-hero-icon {
        background: rgba(40, 167, 69, 0.12);
        color: #1f7a35;
    }
    .buyer-driip-status-hero--rejected .buyer-driip-status-hero-icon {
        background: rgba(220, 53, 69, 0.12);
        color: #a61b2d;
    }
    .buyer-driip-status-hero--empty .buyer-driip-status-hero-icon {
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
    }
</style>
@endsection

