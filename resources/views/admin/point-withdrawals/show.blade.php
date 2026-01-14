@extends('layouts.admin')

@section('title', 'Detail Penarikan Poin')
@section('page-title', 'Detail Penarikan Poin')
@section('page-description', 'Detail informasi request penarikan poin')

@section('breadcrumb')
    <li><a href="{{ route('admin.point-withdrawals.index') }}">Penarikan Poin</a></li>
    <li class="active">Detail</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <!-- Informasi Request -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi Request</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.point-withdrawals.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">User</th>
                            <td>
                                <strong>{{ $pointWithdrawal->user->name }}</strong><br>
                                <small class="text-muted">{{ $pointWithdrawal->user->email }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Poin User</th>
                            <td><strong>{{ number_format($pointWithdrawal->user->points, 0, ',', '.') }} poin</strong></td>
                        </tr>
                        <tr>
                            <th>Jumlah Poin Ditarik</th>
                            <td><strong>{{ number_format($pointWithdrawal->amount, 0, ',', '.') }} poin</strong></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($pointWithdrawal->status === 'pending')
                                    <span class="label label-warning">Baru Pengajuan</span>
                                @elseif($pointWithdrawal->status === 'approved')
                                    <span class="label label-info">Sedang Diproses</span>
                                @elseif($pointWithdrawal->status === 'completed')
                                    <span class="label label-success">Sudah Diproses</span>
                                @elseif($pointWithdrawal->status === 'rejected')
                                    <span class="label label-danger">Ditolak</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Request</th>
                            <td>{{ $pointWithdrawal->requested_at ? $pointWithdrawal->requested_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Informasi Bank -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bank"></i> Informasi Rekening</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Nama Bank</th>
                            <td>{{ $pointWithdrawal->bank_name }}</td>
                        </tr>
                        <tr>
                            <th>Nomor Rekening</th>
                            <td><strong>{{ $pointWithdrawal->account_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Nama Pemilik Rekening</th>
                            <td><strong>{{ $pointWithdrawal->account_name }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($pointWithdrawal->notes)
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-sticky-note"></i> Catatan</h3>
                    </div>
                    <div class="box-body">
                        <p>{{ $pointWithdrawal->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Update Status -->
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cog"></i> Update Status</h3>
                </div>
                <div class="box-body">
                    <form action="{{ route('admin.point-withdrawals.update-status', $pointWithdrawal) }}" method="POST" id="updateStatusForm">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="status">Ubah Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="pending" {{ $pointWithdrawal->status === 'pending' ? 'selected' : '' }}>Baru Pengajuan</option>
                                <option value="approved" {{ $pointWithdrawal->status === 'approved' ? 'selected' : '' }}>Sedang Diproses</option>
                                <option value="completed" {{ $pointWithdrawal->status === 'completed' ? 'selected' : '' }}>Sudah Diproses</option>
                                <option value="rejected" {{ $pointWithdrawal->status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning btn-block" id="updateStatusBtn">
                            <i class="fa fa-save"></i> Update Status
                        </button>
                    </form>

                    @if($pointWithdrawal->status === 'pending')
                        <hr>
                        <form action="{{ route('admin.point-withdrawals.approve', $pointWithdrawal) }}" method="POST" style="margin-bottom: 10px;">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Setujui request penarikan poin ini?');">
                                <i class="fa fa-check"></i> Setujui Request
                            </button>
                        </form>
                        
                        <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#rejectModal">
                            <i class="fa fa-times"></i> Tolak Request
                        </button>
                    @elseif($pointWithdrawal->status === 'approved')
                        <hr>
                        <form action="{{ route('admin.point-withdrawals.complete', $pointWithdrawal) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-primary btn-block" onclick="return confirm('Selesaikan penarikan ini? Poin akan dikurangi dari akun user.');">
                                <i class="fa fa-check-circle"></i> Selesaikan (Kurangi Poin)
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-clock-o"></i> Timeline</h3>
                </div>
                <div class="box-body">
                    <ul class="timeline">
                        <li>
                            <i class="fa fa-clock-o bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock-o"></i> {{ $pointWithdrawal->requested_at->format('d M Y H:i') }}</span>
                                <h3 class="timeline-header">Request dibuat</h3>
                            </div>
                        </li>
                        @if($pointWithdrawal->approved_at)
                            <li>
                                <i class="fa fa-check bg-green"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fa fa-clock-o"></i> {{ $pointWithdrawal->approved_at->format('d M Y H:i') }}</span>
                                    <h3 class="timeline-header">Request disetujui</h3>
                                </div>
                            </li>
                        @endif
                        @if($pointWithdrawal->completed_at)
                            <li>
                                <i class="fa fa-check-circle bg-green"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fa fa-clock-o"></i> {{ $pointWithdrawal->completed_at->format('d M Y H:i') }}</span>
                                    <h3 class="timeline-header">Penarikan diselesaikan</h3>
                                </div>
                            </li>
                        @endif
                        @if($pointWithdrawal->rejected_at)
                            <li>
                                <i class="fa fa-times bg-red"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fa fa-clock-o"></i> {{ $pointWithdrawal->rejected_at->format('d M Y H:i') }}</span>
                                    <h3 class="timeline-header">Request ditolak</h3>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    @if($pointWithdrawal->status === 'pending')
        <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('admin.point-withdrawals.reject', $pointWithdrawal) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-red">
                            <button type="button" class="close" data-dismiss="modal" style="color: #fff;">&times;</button>
                            <h4 class="modal-title" style="color: #fff;"><i class="fa fa-times"></i> Tolak Request</h4>
                        </div>
                        <div class="modal-body">
                            <p>Tolak request penarikan poin dari <strong>{{ $pointWithdrawal->user->name }}</strong>?</p>
                            <div class="form-group">
                                <label for="notes">Alasan Penolakan (Opsional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-times"></i> Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
<style>
    .bg-red {
        background-color: #dd4b39 !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    var currentStatus = '{{ $pointWithdrawal->status }}';
    
    $('#updateStatusForm').on('submit', function(e) {
        var newStatus = $('#status').val();
        var statusLabels = {
            'pending': 'Baru Pengajuan',
            'approved': 'Sedang Diproses',
            'completed': 'Sudah Diproses',
            'rejected': 'Ditolak'
        };
        
        var message = 'Ubah status dari "' + statusLabels[currentStatus] + '" menjadi "' + statusLabels[newStatus] + '"?';
        
        if (newStatus === 'completed' && currentStatus !== 'completed') {
            message += '\n\nPoin akan dikurangi dari akun user.';
        } else if (currentStatus === 'completed' && newStatus !== 'completed') {
            message += '\n\nPoin akan dikembalikan ke akun user.';
        }
        
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush

