@extends('layouts.shop')

@section('title', 'Detail Penarikan Poin')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('buyer.point-withdrawals.index') }}">Penarikan Poin</a>
            <span></span> Detail Penarikan
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
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-withdraw-show">
                                    @csrf
                                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-withdraw-show').submit();">
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
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3 class="mb-0">Detail Penarikan Poin</h3>
                                        <a href="{{ route('buyer.point-withdrawals.index') }}" class="btn btn-sm btn-outline-secondary rounded font-sm">
                                            <i class="fi-rs-arrow-left mr-5"></i> Kembali
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body p-4 pt-0">
                                    <div class="table-responsive">
                                        <table class="table table-clean font-sm">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold" width="35%">Status</td>
                                                    <td>
                                                        @if($pointWithdrawal->status === 'pending')
                                                            <span class="badge rounded-pill bg-warning">Pengajuan Baru</span>
                                                        @elseif($pointWithdrawal->status === 'approved')
                                                            <span class="badge rounded-pill bg-info">Diproses</span>
                                                        @elseif($pointWithdrawal->status === 'completed')
                                                            <span class="badge rounded-pill bg-success">Selesai</span>
                                                        @elseif($pointWithdrawal->status === 'rejected')
                                                            <span class="badge rounded-pill bg-danger">Ditolak</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Jumlah Poin</td>
                                                    <td><h4 class="text-brand mb-0">{{ number_format($pointWithdrawal->amount, 0, ',', '.') }}</h4></td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Bank</td>
                                                    <td>{{ $pointWithdrawal->bank_name }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Nomor Rekening</td>
                                                    <td><code>{{ $pointWithdrawal->account_number }}</code></td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Atas Nama</td>
                                                    <td>{{ $pointWithdrawal->account_name }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Tanggal Pengajuan</td>
                                                    <td>{{ $pointWithdrawal->requested_at ? $pointWithdrawal->requested_at->format('d M Y H:i') : '-' }}</td>
                                                </tr>
                                                @if($pointWithdrawal->approved_at)
                                                    <tr>
                                                        <td class="fw-bold">Tanggal Disetujui</td>
                                                        <td>{{ $pointWithdrawal->approved_at->format('d M Y H:i') }}</td>
                                                    </tr>
                                                @endif
                                                @if($pointWithdrawal->completed_at)
                                                    <tr>
                                                        <td class="fw-bold">Tanggal Selesai</td>
                                                        <td>{{ $pointWithdrawal->completed_at->format('d M Y H:i') }}</td>
                                                    </tr>
                                                @endif
                                                @if($pointWithdrawal->rejected_at)
                                                    <tr>
                                                        <td class="fw-bold">Tanggal Ditolak</td>
                                                        <td>{{ $pointWithdrawal->rejected_at->format('d M Y H:i') }}</td>
                                                    </tr>
                                                @endif
                                                @if($pointWithdrawal->notes)
                                                    <tr>
                                                        <td class="fw-bold">Catatan Admin</td>
                                                        <td class="text-muted italic">{{ $pointWithdrawal->notes }}</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
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
    .table-clean tbody td { padding: 15px 0; border-top: 1px solid #f2f2f2; }
    .table-clean tbody tr:first-child td { border-top: 0; }
</style>
@endsection

