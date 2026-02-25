@extends('layouts.shop')

@section('title', 'Penarikan Poin')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Penarikan Poin
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
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-withdraw">
                                    @csrf
                                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-withdraw').submit();">
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
                                <div class="card-header bg-white border-bottom-0 p-4 d-flex justify-content-between align-items-center">
                                    <h3 class="mb-0">Penarikan Poin</h3>
                                    <a href="{{ route('buyer.point-withdrawals.create') }}" class="btn btn-sm btn-primary">
                                        <i class="fi-rs-plus mr-5"></i> Ajukan Penarikan
                                    </a>
                                </div>
                                <div class="card-body p-4 pt-0">
                                    <!-- Poin Info -->
                                    <div class="alert alert-info border-0 bg-info-light mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-brand text-white p-3 border-radius-10 mr-20">
                                                <i class="fi-rs-star fs-3"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-brand mb-1">Total Poin Saat Ini</h6>
                                                <h2 class="mb-0">{{ number_format($user->points, 0, ',', '.') }} <span class="font-sm text-muted">Poin</span></h2>
                                            </div>
                                        </div>
                                    </div>

                                    @if(session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="fi-rs-check mr-5"></i> {{ session('success') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif

                                    @if(session('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="fi-rs-cross-circle mr-5"></i> {{ session('error') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif

                                    <!-- History -->
                                    <div class="mt-4">
                                        <h5 class="mb-3">Riwayat Penarikan</h5>
                                        @if($withdrawals->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-clean font-sm">
                                                    <thead>
                                                        <tr class="main-heading">
                                                            <th>Tanggal</th>
                                                            <th>Poin</th>
                                                            <th>Bank / No. Rekening</th>
                                                            <th>Penerima</th>
                                                            <th>Status</th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($withdrawals as $withdrawal)
                                                            <tr>
                                                                <td>{{ $withdrawal->requested_at ? $withdrawal->requested_at->format('d M Y') : '-' }}</td>
                                                                <td class="product-price">
                                                                    <strong class="text-brand">{{ number_format($withdrawal->amount, 0, ',', '.') }}</strong>
                                                                </td>
                                                                <td>
                                                                    <span class="d-block fw-bold">{{ $withdrawal->bank_name }}</span>
                                                                    <span class="text-muted font-xs">{{ $withdrawal->account_number }}</span>
                                                                </td>
                                                                <td>{{ $withdrawal->account_name }}</td>
                                                                <td>
                                                                    @if($withdrawal->status === 'pending')
                                                                        <span class="badge rounded-pill bg-warning">Pengajuan Baru</span>
                                                                    @elseif($withdrawal->status === 'approved')
                                                                        <span class="badge rounded-pill bg-info">Diproses</span>
                                                                    @elseif($withdrawal->status === 'completed')
                                                                        <span class="badge rounded-pill bg-success">Selesai</span>
                                                                    @elseif($withdrawal->status === 'rejected')
                                                                        <span class="badge rounded-pill bg-danger">Ditolak</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <a href="{{ route('buyer.point-withdrawals.show', $withdrawal) }}" class="btn-small d-block">
                                                                        Detail
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="pagination-area mt-30 mb-50">
                                                {{ $withdrawals->links() }}
                                            </div>
                                        @else
                                            <div class="text-center py-5">
                                                <div class="mb-3">
                                                    <i class="fi-rs-edit text-muted" style="font-size: 50px; opacity: 0.3;"></i>
                                                </div>
                                                <p class="text-muted">Belum ada riwayat penarikan poin.</p>
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
</div>

@endsection

@push('styles')
<style>
    .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    .table-clean thead th { border-top: 0; border-bottom-width: 1px; color: #253D4E; font-weight: 700; }
    .table-clean tbody td { vertical-align: middle; }
</style>
@endpush

