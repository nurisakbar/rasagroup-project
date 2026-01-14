@extends('layouts.shop')

@section('title', 'Status Pengajuan DRiiPPreneur')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('buyer.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Status Pengajuan</li>
                </ol>
            </nav>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-check"></i> Status Pengajuan DRiiPPreneur</h5>
                </div>
                <div class="card-body text-center py-5">
                    @if($user->driippreneur_status === 'pending')
                        <!-- Pending Status -->
                        <div class="mb-4">
                            <i class="bi bi-hourglass-split text-warning" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="text-warning">Menunggu Verifikasi</h3>
                        <p class="text-muted mb-4">Pengajuan Anda sedang dalam proses verifikasi oleh tim kami.</p>
                        
                        <div class="card bg-light mx-auto" style="max-width: 500px;">
                            <div class="card-body text-start">
                                <p class="mb-2"><strong>Tanggal Pengajuan:</strong></p>
                                <p class="mb-3">{{ $user->driippreneur_applied_at->format('d F Y, H:i') }} WIB</p>
                                
                                <hr>
                                
                                <p class="mb-2"><strong>Data yang Diajukan:</strong></p>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted">No. KTP</td>
                                        <td>{{ $user->no_ktp }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">No. NPWP</td>
                                        <td>{{ $user->no_npwp }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4" style="max-width: 500px; margin: 0 auto;">
                            <i class="bi bi-info-circle"></i> 
                            Proses verifikasi biasanya memakan waktu 1-3 hari kerja. Anda akan mendapatkan notifikasi setelah pengajuan diproses.
                        </div>

                    @elseif($user->driippreneur_status === 'approved')
                        <!-- Approved Status -->
                        <div class="mb-4">
                            <i class="bi bi-check-circle text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="text-success">Pengajuan Disetujui!</h3>
                        <p class="text-muted mb-4">Selamat! Pengajuan DRiiPPreneur Anda telah disetujui. 
                            Sekarang Anda akan mendapatkan poin untuk setiap item yang dibeli.</p>
                        
                        <div class="card bg-success bg-opacity-10 border-success mx-auto" style="max-width: 400px;">
                            <div class="card-body">
                                <p class="mb-0"><strong>Status:</strong> Aktif sebagai DRiiPPreneur</p>
                                <p class="mb-0 mt-2"><small class="text-muted">Anda tetap dapat berbelanja seperti biasa dan poin akan otomatis ditambahkan setelah pesanan selesai.</small></p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-shop"></i> Mulai Belanja
                            </a>
                            <a href="{{ route('buyer.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                            </a>
                        </div>

                    @elseif($user->driippreneur_status === 'rejected')
                        <!-- Rejected Status -->
                        <div class="mb-4">
                            <i class="bi bi-x-circle text-danger" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="text-danger">Pengajuan Ditolak</h3>
                        <p class="text-muted mb-4">Maaf, pengajuan DRiiPPreneur Anda tidak dapat disetujui.</p>
                        
                        <div class="alert alert-secondary" style="max-width: 500px; margin: 0 auto;">
                            <i class="bi bi-info-circle"></i> 
                            Jika Anda merasa ada kesalahan atau ingin mengajukan ulang, silakan hubungi customer service kami.
                        </div>

                    @else
                        <!-- No Application -->
                        <div class="mb-4">
                            <i class="bi bi-file-earmark-x text-secondary" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="text-secondary">Belum Ada Pengajuan</h3>
                        <p class="text-muted mb-4">Anda belum mengajukan pendaftaran sebagai DRiiPPreneur.</p>
                        
                        <a href="{{ route('buyer.driippreneur.apply') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle"></i> Daftar Sekarang
                        </a>
                    @endif
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('buyer.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

