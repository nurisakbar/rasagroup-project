@extends('layouts.admin')

@section('title', 'Pengajuan Distributor')
@section('page-title', 'Pengajuan Distributor')
@section('page-description', 'Verifikasi pendaftaran distributor baru')

@section('breadcrumb')
    <li><a href="{{ route('admin.distributors.index') }}">Distributor</a></li>
    <li class="active">Pengajuan</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-warning">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-clock-o"></i> Pengajuan Menunggu Verifikasi</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.distributors.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <!-- Filter -->
                <div class="box-body" style="border-bottom: 1px solid #f4f4f4;">
                    <form action="{{ route('admin.distributors.applications') }}" method="GET" class="form-inline">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama/email/KTP/NPWP..." value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-default">
                            <i class="fa fa-search"></i> Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.distributors.applications') }}" class="btn btn-default">
                                <i class="fa fa-times"></i> Reset
                            </a>
                        @endif
                    </form>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email / HP</th>
                                <th>No. KTP</th>
                                <th>No. NPWP</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $app)
                                <tr>
                                    <td><strong>{{ $app->name }}</strong></td>
                                    <td>
                                        {{ $app->email }}<br>
                                        <small class="text-muted">{{ $app->phone ?? '-' }}</small>
                                    </td>
                                    <td><code>{{ $app->no_ktp }}</code></td>
                                    <td><code>{{ $app->no_npwp }}</code></td>
                                    <td>{{ $app->distributor_applied_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.distributors.application-detail', $app) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-eye"></i> Review
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <p class="text-muted" style="padding: 40px 0;">
                                            <i class="fa fa-check-circle fa-3x text-success"></i><br><br>
                                            Tidak ada pengajuan yang menunggu verifikasi.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($applications->hasPages())
                <div class="box-footer clearfix">
                    <div class="pull-right">
                        {{ $applications->appends(request()->query())->links('pagination::simple-bootstrap-3') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

