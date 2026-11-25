@extends('layouts.admin')

@section('title', 'Kelola Modul')
@section('page-title', 'Kelola Modul')
@section('page-description', 'Modul adalah satuan menu dan rute panel admin yang hak aksesnya diatur per role')

@section('breadcrumb')
    <li><a href="{{ route('admin.access.index') }}">Hak Akses</a></li>
    <li class="active">Modul</li>
@endsection

@section('content')
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Daftar modul ({{ $modules->count() }})</h3>
            <div class="box-tools pull-right">
                <a href="{{ route('admin.access.modules.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah Modul</a>
            </div>
        </div>
        <div class="box-body table-responsive no-padding">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th width="60px">Urut</th>
                        <th>Modul</th>
                        <th>Grup menu</th>
                        <th>Cakupan rute</th>
                        <th class="text-center">Role</th>
                        <th>Status</th>
                        <th width="130px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modules as $m)
                        <tr>
                            <td>{{ $m->sort_order }}</td>
                            <td><i class="fa {{ $m->icon }}"></i> <strong>{{ $m->name }}</strong><br><small class="text-muted">{{ $m->key }}</small></td>
                            <td>{{ $m->group ?: '-' }}</td>
                            <td><small>@foreach(($m->route_patterns ?? []) as $p)<code>{{ $p }}</code> @endforeach</small></td>
                            <td class="text-center">{{ $m->key === 'access_control' ? 'Super Admin' : $m->roles_count }}</td>
                            <td>
                                @if($m->is_system)<span class="label label-default">bawaan</span>@endif
                                <span class="label {{ $m->is_active ? 'label-success' : 'label-danger' }}">{{ $m->is_active ? 'aktif' : 'nonaktif' }}</span>
                                @unless($m->show_in_menu)<span class="label label-info">tanpa menu</span>@endunless
                            </td>
                            <td class="text-right" style="white-space:nowrap;">
                                <a href="{{ route('admin.access.modules.edit', $m) }}" class="btn btn-xs btn-info"><i class="fa fa-edit"></i> Ubah</a>
                                @unless($m->is_system)
                                    <form action="{{ route('admin.access.modules.destroy', $m) }}" method="POST" class="inline" style="display:inline;" onsubmit="return confirm('Hapus modul {{ $m->name }}? Izin semua role untuk modul ini ikut terhapus.');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Hapus</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
