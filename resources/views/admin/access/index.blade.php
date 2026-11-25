@extends('layouts.admin')

@section('title', 'Hak Akses')
@section('page-title', 'Hak Akses Modul')
@section('page-description', 'Atur modul yang boleh diakses tiap role, lengkap dengan izin lihat, tambah, ubah, dan hapus')

@section('breadcrumb')
    <li class="active">Hak Akses</li>
@endsection

@section('content')
    <div class="callout callout-info">
        <p><i class="fa fa-info-circle"></i> <strong>Super Admin</strong> selalu punya akses penuh dan tidak bisa dibatasi. Perubahan berlaku langsung pada pengguna yang sedang masuk, tanpa perlu login ulang.
            Role pengguna tetap diatur di menu <a href="{{ route('admin.users.index') }}">Data Pengguna</a>.</p>
    </div>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Role panel admin</h3>
            <div class="box-tools pull-right">
                <a href="{{ route('admin.access.modules.index') }}" class="btn btn-default btn-sm"><i class="fa fa-cubes"></i> Kelola Modul ({{ $modules }})</a>
            </div>
        </div>
        <div class="box-body table-responsive no-padding">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th class="text-center">Pengguna</th>
                        <th class="text-center">Modul dapat dilihat</th>
                        <th class="text-center">Akses penuh</th>
                        <th width="160px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $r)
                        <tr>
                            <td><strong>{{ $r['label'] }}</strong><br><small class="text-muted">{{ $r['role'] }}</small></td>
                            <td class="text-center">{{ $r['users'] }}</td>
                            <td class="text-center">
                                <span class="label {{ $r['view'] > 0 ? 'label-primary' : 'label-default' }}">{{ $r['view'] }} dari {{ $modules - 1 }}</span>
                            </td>
                            <td class="text-center">{{ $r['full'] }} modul</td>
                            <td class="text-right">
                                <a href="{{ route('admin.access.roles.edit', $r['role']) }}" class="btn btn-primary btn-sm"><i class="fa fa-key"></i> Atur hak akses</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
