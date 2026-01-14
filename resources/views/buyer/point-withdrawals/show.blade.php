@extends('layouts.shop')

@section('title', 'Detail Penarikan Poin')
@section('page-title', 'Detail Penarikan Poin')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-wallet2"></i> Detail Penarikan Poin</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Status</th>
                            <td>
                                @if($pointWithdrawal->status === 'pending')
                                    <span class="badge bg-warning">Baru Pengajuan</span>
                                @elseif($pointWithdrawal->status === 'approved')
                                    <span class="badge bg-info">Sedang Diproses</span>
                                @elseif($pointWithdrawal->status === 'completed')
                                    <span class="badge bg-success">Sudah Diproses</span>
                                @elseif($pointWithdrawal->status === 'rejected')
                                    <span class="badge bg-danger">Ditolak</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Jumlah Poin</th>
                            <td><strong>{{ number_format($pointWithdrawal->amount, 0, ',', '.') }} poin</strong></td>
                        </tr>
                        <tr>
                            <th>Bank</th>
                            <td>{{ $pointWithdrawal->bank_name }}</td>
                        </tr>
                        <tr>
                            <th>Nomor Rekening</th>
                            <td>{{ $pointWithdrawal->account_number }}</td>
                        </tr>
                        <tr>
                            <th>Nama Pemilik Rekening</th>
                            <td>{{ $pointWithdrawal->account_name }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Request</th>
                            <td>{{ $pointWithdrawal->requested_at ? $pointWithdrawal->requested_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        @if($pointWithdrawal->approved_at)
                            <tr>
                                <th>Tanggal Disetujui</th>
                                <td>{{ $pointWithdrawal->approved_at->format('d M Y H:i') }}</td>
                            </tr>
                        @endif
                        @if($pointWithdrawal->completed_at)
                            <tr>
                                <th>Tanggal Diselesaikan</th>
                                <td>{{ $pointWithdrawal->completed_at->format('d M Y H:i') }}</td>
                            </tr>
                        @endif
                        @if($pointWithdrawal->rejected_at)
                            <tr>
                                <th>Tanggal Ditolak</th>
                                <td>{{ $pointWithdrawal->rejected_at->format('d M Y H:i') }}</td>
                            </tr>
                        @endif
                        @if($pointWithdrawal->notes)
                            <tr>
                                <th>Catatan</th>
                                <td>{{ $pointWithdrawal->notes }}</td>
                            </tr>
                        @endif
                    </table>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="{{ route('buyer.point-withdrawals.index') }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

