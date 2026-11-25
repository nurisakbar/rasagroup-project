@extends('layouts.admin')

@section('title', 'Hak Akses ' . $roleLabel)
@section('page-title', 'Hak Akses: ' . $roleLabel)
@section('page-description', $users . ' pengguna memakai role ini')

@section('breadcrumb')
    <li><a href="{{ route('admin.access.index') }}">Hak Akses</a></li>
    <li class="active">{{ $roleLabel }}</li>
@endsection

@push('styles')
<style>
    .perm-table th, .perm-table td { vertical-align: middle !important; }
    .perm-table .perm-cell { text-align: center; width: 90px; }
    .perm-table input[type=checkbox] { width: 18px; height: 18px; cursor: pointer; }
    .perm-table tr.group-row td { background: #f4f4f4; font-weight: 700; color: #6A1B1B; }
    .perm-table tr.row-off td { opacity: .78; }
    .perm-table .mod-sub { display: block; font-size: 11px; color: #999; font-weight: normal; }
    .perm-bar { position: sticky; bottom: 0; background: #fff; border-top: 1px solid #ddd; padding: 12px 15px; z-index: 5; }
</style>
@endpush

@section('content')
    <div class="callout callout-warning">
        <p>Mencentang <strong>Tambah</strong>, <strong>Ubah</strong>, atau <strong>Hapus</strong> otomatis mengaktifkan <strong>Lihat</strong>. Tanpa <strong>Lihat</strong>, modul tidak muncul di menu dan tidak bisa dibuka.</p>
    </div>

    <form action="{{ route('admin.access.roles.update', $role) }}" method="POST" id="permForm">
        @csrf
        @method('PUT')
        <div class="box">
            <div class="box-body table-responsive no-padding">
                <table class="table table-bordered perm-table">
                    <thead>
                        <tr>
                            <th>Modul</th>
                            <th class="perm-cell">Lihat<br><input type="checkbox" class="col-all" data-col="view"></th>
                            <th class="perm-cell">Tambah<br><input type="checkbox" class="col-all" data-col="create"></th>
                            <th class="perm-cell">Ubah<br><input type="checkbox" class="col-all" data-col="edit"></th>
                            <th class="perm-cell">Hapus<br><input type="checkbox" class="col-all" data-col="delete"></th>
                            <th class="perm-cell">Semua</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $lastGroup = '__none__'; @endphp
                        @foreach($modules as $m)
                            @php $p = $perms->get($m->id); @endphp
                            @if(($m->group ?? '') !== $lastGroup && $m->group)
                                <tr class="group-row"><td colspan="6">{{ $m->group }}</td></tr>
                            @endif
                            @php $lastGroup = $m->group ?? ''; @endphp
                            <tr class="{{ $m->is_active ? '' : 'row-off' }}" data-row>
                                <td>
                                    <i class="fa {{ $m->icon }}"></i> <strong>{{ $m->name }}</strong>
                                    @unless($m->is_active) <span class="label label-default">nonaktif</span> @endunless
                                    @unless($m->show_in_menu) <span class="label label-info">tanpa menu</span> @endunless
                                    <span class="mod-sub">{{ $m->key }}</span>
                                </td>
                                @foreach(['view', 'create', 'edit', 'delete'] as $a)
                                    <td class="perm-cell">
                                        <input type="checkbox" name="perm[{{ $m->id }}][{{ $a }}]" value="1" data-act="{{ $a }}"
                                            {{ $p && $p->{'can_'.$a} ? 'checked' : '' }} aria-label="{{ $a }} {{ $m->name }}">
                                    </td>
                                @endforeach
                                <td class="perm-cell"><input type="checkbox" class="row-all" aria-label="semua {{ $m->name }}"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="perm-bar clearfix">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan hak akses</button>
                <a href="{{ route('admin.access.index') }}" class="btn btn-default">Kembali</a>
                <button type="submit" form="resetForm" class="btn btn-warning pull-right" onclick="return confirm('Kembalikan hak akses {{ $roleLabel }} ke pengaturan bawaan? Perubahan yang belum disimpan akan hilang.');"><i class="fa fa-undo"></i> Kembalikan ke bawaan</button>
            </div>
        </div>
    </form>
    <form id="resetForm" action="{{ route('admin.access.roles.reset', $role) }}" method="POST" style="display:none;">@csrf</form>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('permForm');
    function rowBoxes(tr) { return tr.querySelectorAll('input[data-act]'); }
    function syncRow(tr) {
        var boxes = rowBoxes(tr), all = tr.querySelector('.row-all');
        all.checked = Array.prototype.every.call(boxes, function (b) { return b.checked; });
    }
    function syncCols() {
        form.querySelectorAll('.col-all').forEach(function (c) {
            var boxes = form.querySelectorAll('input[data-act="' + c.dataset.col + '"]');
            c.checked = boxes.length && Array.prototype.every.call(boxes, function (b) { return b.checked; });
        });
    }
    form.addEventListener('change', function (e) {
        var t = e.target, tr = t.closest('tr[data-row]');
        if (t.classList.contains('col-all')) {
            form.querySelectorAll('input[data-act="' + t.dataset.col + '"]').forEach(function (b) { b.checked = t.checked; });
            if (t.checked && t.dataset.col !== 'view') { form.querySelectorAll('input[data-act="view"]').forEach(function (b) { b.checked = true; }); }
            if (!t.checked && t.dataset.col === 'view') { form.querySelectorAll('input[data-act]').forEach(function (b) { b.checked = false; }); }
            form.querySelectorAll('tr[data-row]').forEach(syncRow); syncCols(); return;
        }
        if (!tr) return;
        if (t.classList.contains('row-all')) { rowBoxes(tr).forEach(function (b) { b.checked = t.checked; }); }
        else if (t.dataset.act === 'view' && !t.checked) { rowBoxes(tr).forEach(function (b) { b.checked = false; }); }
        else if (t.checked && t.dataset.act !== 'view') { tr.querySelector('input[data-act="view"]').checked = true; }
        syncRow(tr); syncCols();
    });
    form.querySelectorAll('tr[data-row]').forEach(syncRow); syncCols();
    var dirty = false, submitting = false;
    form.addEventListener('change', function () { dirty = true; });
    form.addEventListener('submit', function () { submitting = true; });
    window.addEventListener('beforeunload', function (e) { if (dirty && !submitting) { e.preventDefault(); e.returnValue = ''; } });
})();
</script>
@endpush
