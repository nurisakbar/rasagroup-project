@extends('layouts.shop')

@section('title', 'Penarikan Poin')
@section('page-title', 'Penarikan Poin')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-wallet2"></i> Penarikan Poin</h5>
                    <a href="{{ route('buyer.point-withdrawals.create') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Ajukan Penarikan
                    </a>
                </div>
                <div class="card-body">
                    <!-- Poin Info -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="bi bi-star-fill"></i> Poin Saat Ini:</strong>
                                <h3 class="mb-0">{{ number_format($user->points, 0, ',', '.') }} poin</h3>
                            </div>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- History -->
                    <h6 class="mb-3">History Penarikan</h6>
                    @if($withdrawals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal Request</th>
                                        <th>Jumlah Poin</th>
                                        <th>Bank</th>
                                        <th>No. Rekening</th>
                                        <th>Atas Nama</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($withdrawals as $withdrawal)
                                        <tr>
                                            <td>{{ $withdrawal->requested_at ? $withdrawal->requested_at->format('d M Y H:i') : '-' }}</td>
                                            <td><strong>{{ number_format($withdrawal->amount, 0, ',', '.') }} poin</strong></td>
                                            <td>{{ $withdrawal->bank_name }}</td>
                                            <td>{{ $withdrawal->account_number }}</td>
                                            <td>{{ $withdrawal->account_name }}</td>
                                            <td>
                                                @if($withdrawal->status === 'pending')
                                                    <span class="badge bg-warning">Baru Pengajuan</span>
                                                @elseif($withdrawal->status === 'approved')
                                                    <span class="badge bg-info">Sedang Diproses</span>
                                                @elseif($withdrawal->status === 'completed')
                                                    <span class="badge bg-success">Sudah Diproses</span>
                                                @elseif($withdrawal->status === 'rejected')
                                                    <span class="badge bg-danger">Ditolak</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('buyer.point-withdrawals.show', $withdrawal) }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $withdrawals->links() }}
                        </div>
                    @else
                        <div class="alert alert-secondary text-center">
                            <i class="bi bi-inbox"></i> Belum ada history penarikan poin.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

