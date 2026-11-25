@extends('layouts.admin')

@php $isNew = ! $module->exists; $locked = (bool) $module->is_system; @endphp
@section('title', $isNew ? 'Tambah Modul' : 'Ubah Modul')
@section('page-title', $isNew ? 'Tambah Modul' : 'Ubah Modul: ' . $module->name)
@section('page-description', 'Modul baru belum bisa diakses role mana pun sampai diatur di menu Hak Akses')

@section('breadcrumb')
    <li><a href="{{ route('admin.access.index') }}">Hak Akses</a></li>
    <li><a href="{{ route('admin.access.modules.index') }}">Modul</a></li>
    <li class="active">{{ $isNew ? 'Tambah' : 'Ubah' }}</li>
@endsection

@section('content')
    <div class="row"><div class="col-md-8">
        <div class="box box-primary">
            <form action="{{ $isNew ? route('admin.access.modules.store') : route('admin.access.modules.update', $module) }}" method="POST">
                @csrf
                @unless($isNew) @method('PUT') @endunless
                <div class="box-body">
                    @if($locked)
                        <div class="callout callout-info"><p>Modul bawaan: kunci dan cakupan rutenya dikunci. Nama, ikon, urutan, dan tampilan menu boleh diubah.</p></div>
                    @endif

                    <div class="form-group @error('key') has-error @enderror">
                        <label for="key">Kunci <span class="text-danger">*</span></label>
                        <input type="text" id="key" name="key" class="form-control" value="{{ old('key', $module->key) }}" {{ $isNew ? 'required' : 'disabled' }} placeholder="contoh: laporan_stok">
                        @error('key')<span class="help-block">{{ $message }}</span>@enderror
                        @if($isNew)<span class="help-block">Huruf kecil, angka, garis bawah. Tidak bisa diubah setelah dibuat.</span>@endif
                    </div>
                    <div class="form-group @error('name') has-error @enderror">
                        <label for="name">Nama modul (label menu) <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $module->name) }}" required maxlength="120">
                        @error('name')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        <div class="col-sm-6"><div class="form-group @error('group') has-error @enderror">
                            <label for="group">Grup menu (opsional)</label>
                            <input type="text" id="group" name="group" class="form-control" list="groups" value="{{ old('group', $module->group) }}" maxlength="60" placeholder="kosong = menu tersendiri">
                            <datalist id="groups">@foreach($groups as $g)<option value="{{ $g }}">@endforeach</datalist>
                            @error('group')<span class="help-block">{{ $message }}</span>@enderror
                        </div></div>
                        <div class="col-sm-6"><div class="form-group @error('icon') has-error @enderror">
                            <label for="icon">Ikon (Font Awesome) <span class="text-danger">*</span></label>
                            <input type="text" id="icon" name="icon" class="form-control" value="{{ old('icon', $module->icon) }}" required placeholder="fa-cube">
                            @error('icon')<span class="help-block">{{ $message }}</span>@enderror
                        </div></div>
                    </div>
                    <div class="form-group @error('menu_route') has-error @enderror">
                        <label for="menu_route">Rute tujuan menu</label>
                        <input type="text" id="menu_route" name="menu_route" class="form-control" value="{{ old('menu_route', $module->menu_route) }}" placeholder="admin.orders.index">
                        @error('menu_route')<span class="help-block">{{ $message }}</span>@enderror
                        <span class="help-block">Nama rute halaman yang dibuka saat menu diklik. Kosongkan bila modul tidak punya menu.</span>
                    </div>
                    <div class="form-group @error('route_patterns') has-error @enderror">
                        <label for="route_patterns">Cakupan rute @unless($locked)<span class="text-danger">*</span>@endunless</label>
                        <textarea id="route_patterns" name="route_patterns" rows="4" class="form-control" {{ $locked ? 'disabled' : 'required' }} placeholder="admin.laporan-stok.*">{{ old('route_patterns', implode("\n", $module->route_patterns ?? [])) }}</textarea>
                        @error('route_patterns')<span class="help-block">{{ $message }}</span>@enderror
                        <span class="help-block">Satu pola per baris. Diawali <code>admin.</code>, boleh berakhiran <code>.*</code> (semua rute turunannya). Pola yang lebih spesifik menang. Aksi dikenali dari nama rute dan metode: GET = lihat, <code>create/store/import</code> = tambah, PUT/POST lain = ubah, DELETE/<code>destroy</code> = hapus.</span>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><div class="form-group @error('sort_order') has-error @enderror">
                            <label for="sort_order">Urutan menu <span class="text-danger">*</span></label>
                            <input type="number" id="sort_order" name="sort_order" class="form-control" min="0" max="9999" value="{{ old('sort_order', $module->sort_order) }}" required>
                            @error('sort_order')<span class="help-block">{{ $message }}</span>@enderror
                        </div></div>
                        <div class="col-sm-4"><div class="checkbox" style="margin-top:28px;"><label><input type="checkbox" name="show_in_menu" value="1" {{ old('show_in_menu', $module->show_in_menu) ? 'checked' : '' }}> Tampil di menu samping</label></div></div>
                        <div class="col-sm-4"><div class="checkbox" style="margin-top:28px;"><label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $module->is_active) ? 'checked' : '' }}> Aktif</label></div></div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                    <a href="{{ route('admin.access.modules.index') }}" class="btn btn-default">Batal</a>
                </div>
            </form>
        </div>
    </div></div>
@endsection
