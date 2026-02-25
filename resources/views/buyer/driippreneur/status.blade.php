@extends('layouts.shop')

@section('title', 'Status DRiiPPreneur')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Status DRiiPPreneur
        </div>
    </div>
</div>

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4">
                    <div class="dashboard-menu">
                        <ul class="nav flex-column" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('buyer.dashboard') }}"><i class="fi-rs-settings-sliders mr-10"></i>Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('buyer.orders.index') }}"><i class="fi-rs-shopping-bag mr-10"></i>Pesanan Saya</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('buyer.addresses.index') }}"><i class="fi-rs-marker mr-10"></i>Alamat Saya</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('buyer.profile') }}"><i class="fi-rs-user mr-10"></i>Detail Akun</a>
                            </li>
                            <li class="nav-item">
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-status">
                                    @csrf
                                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-status').submit();">
                                        <i class="fi-rs-sign-out mr-10"></i>Keluar
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <h3 class="mb-0">Status Pengajuan DRiiPPreneur</h3>
                                </div>
                                <div class="card-body p-4 pt-0 text-center">
                                    @if($user->driippreneur_status === 'pending')
                                        <div class="py-5">
                                            <div class="mb-4">
                                                <i class="fi-rs-time-past text-warning" style="font-size: 80px;"></i>
                                            </div>
                                            <h3 class="text-warning mb-3">Menunggu Verifikasi</h3>
                                            <p class="font-md text-muted mb-4">Pengajuan Anda sedang dalam proses verifikasi oleh tim kami.</p>
                                            
                                            <div class="card bg-brand-light border-0 mx-auto" style="max-width: 500px;">
                                                <div class="card-body text-start p-4">
                                                    <div class="mb-3">
                                                        <label class="font-sm text-muted mb-1">Tanggal Pengajuan</label>
                                                        <p class="font-md fw-bold mb-0 text-brand">{{ $user->driippreneur_applied_at->format('d F Y, H:i') }} WIB</p>
                                                    </div>
                                                    
                                                    <div class="divider mb-3"></div>
                                                    
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <label class="font-sm text-muted mb-1">No. KTP</label>
                                                            <p class="font-md fw-bold mb-0">{{ $user->no_ktp }}</p>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="font-sm text-muted mb-1">No. NPWP</label>
                                                            <p class="font-md fw-bold mb-0">{{ $user->no_npwp }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="alert alert-info border-0 bg-info-light mt-4 mx-auto" style="max-width: 500px;">
                                                <p class="font-sm mb-0">
                                                    <i class="fi-rs-info mr-5"></i> 
                                                    Proses verifikasi biasanya memakan waktu 1-3 hari kerja. Anda akan mendapatkan notifikasi setelah pengajuan diproses.
                                                </p>
                                            </div>
                                        </div>

                                    @elseif($user->driippreneur_status === 'approved')
                                        <div class="py-5">
                                            <div class="mb-4">
                                                <i class="fi-rs-check text-success" style="font-size: 80px;"></i>
                                            </div>
                                            <h3 class="text-success mb-3">Pengajuan Disetujui!</h3>
                                            <p class="font-md text-muted mb-4">Selamat! Pengajuan DRiiPPreneur Anda telah disetujui. 
                                                Sekarang Anda akan mendapatkan poin untuk setiap item yang dibeli.</p>
                                            
                                            <div class="card bg-success-light border-0 mx-auto mb-4" style="max-width: 400px;">
                                                <div class="card-body p-4">
                                                    <h5 class="mb-2"><i class="fi-rs-star mr-10 text-success"></i>Aktif sebagai DRiiPPreneur</h5>
                                                    <p class="font-sm text-muted mb-0">Anda tetap dapat berbelanja seperti biasa dan poin akan otomatis ditambahkan setelah pesanan selesai.</p>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-center gap-3 mt-4">
                                                <a href="{{ route('products.index') }}" class="btn btn-fill-out btn-lg">
                                                    <i class="fi-rs-shopping-bag mr-5"></i> Mulai Belanja
                                                </a>
                                                <a href="{{ route('buyer.dashboard') }}" class="btn btn-outline-secondary rounded btn-lg font-sm">
                                                    <i class="fi-rs-arrow-left mr-5"></i> Dashboard
                                                </a>
                                            </div>
                                        </div>

                                    @elseif($user->driippreneur_status === 'rejected')
                                        <div class="py-5">
                                            <div class="mb-4">
                                                <i class="fi-rs-cross-circle text-danger" style="font-size: 80px;"></i>
                                            </div>
                                            <h3 class="text-danger mb-3">Pengajuan Ditolak</h3>
                                            <p class="font-md text-muted mb-4">Maaf, pengajuan DRiiPPreneur Anda tidak dapat disetujui saat ini.</p>
                                            
                                            <div class="alert alert-secondary border-0 bg-light mt-4 mx-auto" style="max-width: 500px;">
                                                <p class="font-sm mb-0">
                                                    <i class="fi-rs-info mr-5"></i> 
                                                    Jika Anda merasa ada kesalahan atau ingin mengajukan ulang, silakan hubungi layanan pelanggan kami.
                                                </p>
                                            </div>
                                            
                                            <div class="mt-4">
                                                <a href="{{ route('buyer.dashboard') }}" class="btn btn-outline-secondary rounded btn-lg font-sm">
                                                    <i class="fi-rs-arrow-left mr-5"></i> Kembali ke Dashboard
                                                </a>
                                            </div>
                                        </div>

                                    @else
                                        <div class="py-5">
                                            <div class="mb-4">
                                                <i class="fi-rs-document text-muted" style="font-size: 80px;"></i>
                                            </div>
                                            <h3 class="text-muted mb-3">Belum Ada Pengajuan</h3>
                                            <p class="font-md text-muted mb-4">Anda belum mengajukan pendaftaran sebagai DRiiPPreneur.</p>
                                            
                                            <a href="{{ route('buyer.driippreneur.apply') }}" class="btn btn-fill-out btn-lg">
                                                <i class="fi-rs-add mr-5"></i> Daftar Sekarang
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
</div>

<style>
    .bg-brand-light { background-color: rgba(59, 183, 126, 0.1); }
    .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    .bg-success-light { background-color: rgba(40, 167, 69, 0.1); }
    .divider { height: 1px; background-color: #f2f2f2; width: 100%; }
</style>
@endsection

