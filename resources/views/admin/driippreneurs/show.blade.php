@extends('layouts.admin')

@section('title', 'Detail DRiiPPreneur')
@section('page-title', 'Detail DRiiPPreneur')
@section('page-description', 'Detail informasi aplikasi DRiiPPreneur')

@section('breadcrumb')
    <li><a href="{{ route('admin.driippreneurs.index') }}">DRiiPPreneur</a></li>
    <li class="active">Detail</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <!-- Informasi Aplikasi -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-user"></i> Informasi Aplikasi</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.driippreneurs.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Nama</th>
                            <td><strong>{{ $driippreneur->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $driippreneur->email }}</td>
                        </tr>
                        <tr>
                            <th>No. HP</th>
                            <td>{{ $driippreneur->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status Aplikasi</th>
                            <td>
                                @if($driippreneur->driippreneur_status === 'pending')
                                    <span class="label label-warning">Pending</span>
                                @elseif($driippreneur->driippreneur_status === 'approved')
                                    <span class="label label-success">Approved</span>
                                @elseif($driippreneur->driippreneur_status === 'rejected')
                                    <span class="label label-danger">Rejected</span>
                                @else
                                    <span class="label label-default">{{ $driippreneur->driippreneur_status }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pengajuan</th>
                            <td>{{ $driippreneur->driippreneur_applied_at ? $driippreneur->driippreneur_applied_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Dokumen Verifikasi -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-file-text-o"></i> Dokumen Verifikasi</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">No. KTP</th>
                            <td>{{ $driippreneur->no_ktp ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>No. NPWP</th>
                            <td>{{ $driippreneur->no_npwp ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Action -->
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cog"></i> Action</h3>
                </div>
                <div class="box-body">
                    @if($driippreneur->driippreneur_status === 'pending')
                        <form action="{{ route('admin.driippreneurs.approve', $driippreneur) }}" method="POST" style="margin-bottom: 10px;">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Setujui aplikasi DRiiPPreneur ini?');">
                                <i class="fa fa-check"></i> Setujui Aplikasi
                            </button>
                        </form>
                        
                        <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#rejectModal">
                            <i class="fa fa-times"></i> Tolak Aplikasi
                        </button>
                    @elseif($driippreneur->driippreneur_status === 'approved')
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> Aplikasi sudah disetujui. User akan mendapatkan poin untuk setiap pembelian.
                        </div>
                    @elseif($driippreneur->driippreneur_status === 'rejected')
                        <div class="alert alert-danger">
                            <i class="fa fa-times-circle"></i> Aplikasi ditolak.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.driippreneurs.reject', $driippreneur) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-red">
                        <button type="button" class="close" data-dismiss="modal" style="color: #fff;">&times;</button>
                        <h4 class="modal-title" style="color: #fff;"><i class="fa fa-times"></i> Tolak Aplikasi</h4>
                    </div>
                    <div class="modal-body">
                        <p>Tolak aplikasi DRiiPPreneur dari <strong>{{ $driippreneur->name }}</strong>?</p>
                        <div class="form-group">
                            <label for="rejection_reason">Alasan Penolakan (Opsional)</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
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
@endsection

@push('styles')
<style>
    .bg-red {
        background-color: #dd4b39 !important;
    }
</style>
@endpush
