@extends('layouts.shop')

@section('title', 'Status Pengajuan Distributor')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Status Distributor
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
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-distributor-status">
                                    @csrf
                                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-distributor-status').submit();">
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
                                    <h3 class="mb-0">Status Pengajuan Distributor</h3>
                                </div>
                                <div class="card-body p-4 pt-0 text-center">
                                    @if($user->distributor_status === 'pending')
                                        <!-- Pending Status -->
                                        <div class="py-5">
                                            <div class="mb-4">
                                                <i class="fi-rs-time-past text-warning" style="font-size: 5rem;"></i>
                                            </div>
                                            <h3 class="text-warning mb-2">Menunggu Verifikasi</h3>
                                            <p class="font-md text-muted mb-30">Pengajuan Anda sedang dalam proses verifikasi oleh tim kami. Mohon tunggu proses ini selesai.</p>
                                            
                                            <div class="bg-light p-4 border-radius-10 mx-auto" style="max-width: 500px;">
                                                <h6 class="mb-3 text-brand text-start border-bottom pb-2">Informasi Pengajuan</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-borderless mb-0 text-start font-sm">
                                                        <tr>
                                                            <td class="text-muted fw-bold" width="150">Tgl Pengajuan</td>
                                                            <td>{{ $user->distributor_applied_at->format('d M Y, H:i') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">No. KTP</td>
                                                            <td>{{ $user->no_ktp }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">No. NPWP</td>
                                                            <td>{{ $user->no_npwp }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Lokasi</td>
                                                            <td>{{ $user->distributorProvince->name ?? '-' }}, {{ $user->distributorRegency->name ?? '-' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold" style="vertical-align: top;">Alamat Usaha</td>
                                                            <td>{{ $user->distributor_address ?? '-' }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="alert alert-info border-0 bg-info-light mt-4 font-xs mx-auto" style="max-width: 500px;">
                                                <i class="fi-rs-info mr-5 fs-6"></i> 
                                                Proses verifikasi biasanya memakan waktu 1-3 hari kerja. Kami akan memberikan notifikasi melalui email setelah pengajuan Anda diproses.
                                            </div>
                                        </div>

                                    @elseif($user->distributor_status === 'approved')
                                        <!-- Approved Status -->
                                        <div class="py-5">
                                            <div class="mb-4">
                                                <i class="fi-rs-check-circle text-success" style="font-size: 5rem;"></i>
                                            </div>
                                            <h3 class="text-success mb-2">Selamat! Pengajuan Disetujui</h3>
                                            <p class="font-md text-muted mb-4">Pengajuan Anda telah diverifikasi dan disetujui. Anda sekarang adalah distributor resmi kami.</p>
                                            
                                            <div class="bg-success-light p-3 border-radius-10 mx-auto mb-30" style="max-width: 400px;">
                                                <p class="font-sm mb-0">Disetujui pada: <strong>{{ $user->distributor_approved_at->format('d M Y, H:i') }}</strong></p>
                                            </div>

                                            <div class="mt-4">
                                                <a href="{{ route('distributor.login') }}" class="btn btn-brand rounded">
                                                    <i class="fi-rs-sign-in mr-5"></i> Login ke Portal Distributor
                                                </a>
                                            </div>
                                        </div>

                                    @elseif($user->distributor_status === 'rejected')
                                        <!-- Rejected Status -->
                                        <div class="py-5">
                                            <div class="mb-4">
                                                <i class="fi-rs-cross-circle text-danger" style="font-size: 5rem;"></i>
                                            </div>
                                            <h3 class="text-danger mb-2">Pengajuan Ditolak</h3>
                                            <p class="font-md text-muted mb-4">Maaf, tim kami belum dapat menyetujui pengajuan distributor Anda saat ini.</p>
                                            
                                            @if($user->distributor_rejection_reason)
                                            <div class="bg-danger-light p-4 border-radius-10 mx-auto mb-4" style="max-width: 500px;">
                                                <h6 class="text-danger mb-2 text-start">Alasan Penolakan:</h6>
                                                <p class="font-sm text-start mb-0">{{ $user->distributor_rejection_reason }}</p>
                                            </div>
                                            @endif

                                            <div class="alert alert-secondary border-0 bg-light mx-auto" style="max-width: 500px;">
                                                <i class="fi-rs-info mr-5"></i> 
                                                Silakan perbaiki data Anda atau hubungi Customer Service kami untuk bantuan lebih lanjut.
                                            </div>
                                            
                                            <div class="mt-4">
                                                <a href="{{ route('buyer.distributor.apply') }}" class="btn btn-brand rounded">
                                                    Ajukan Ulang
                                                </a>
                                            </div>
                                        </div>

                                    @else
                                        <!-- No Application -->
                                        <div class="py-5">
                                            <div class="mb-4">
                                                <i class="fi-rs-file-edit text-muted" style="font-size: 5rem;"></i>
                                            </div>
                                            <h3 class="text-muted mb-2">Belum Ada Pengajuan</h3>
                                            <p class="font-md text-muted mb-30">Anda belum mengajukan pendaftaran sebagai distributor kami.</p>
                                            
                                            <a href="{{ route('buyer.distributor.apply') }}" class="btn btn-brand rounded">
                                                <i class="fi-rs-plus mr-5"></i> Daftar Sekarang
                                            </a>
                                        </div>
                                    @endif

                                    <div class="pt-4 border-top mt-4 text-start">
                                         <a href="{{ route('buyer.dashboard') }}" class="btn btn-outline-secondary rounded font-sm"><i class="fi-rs-arrow-left mr-5"></i>Kembali ke Dashboard</a>
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

<style>
    .bg-info-light { background-color: rgba(13, 202, 240, 0.08); }
    .bg-success-light { background-color: rgba(59, 183, 126, 0.08); }
    .bg-danger-light { background-color: rgba(253, 61, 87, 0.08); }
</style>
@endsection

