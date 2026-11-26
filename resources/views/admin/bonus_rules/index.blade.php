@extends('layouts.admin')

@section('title', 'Bonus Pembelian')
@section('page-title', 'Bonus Pembelian')

@section('breadcrumb')
    <li class="active">Bonus Pembelian</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Aturan Bonus (beli X dapat hadiah)</h3>
                <div class="box-tools">
                    @adminCan('bonus_rules', 'create')
                    <a href="{{ route('admin.bonus-rules.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah Aturan</a>
                    @endadminCan
                </div>
            </div>
            <div class="box-body table-responsive">
                <table id="bonus-table" class="table table-bordered table-striped table-hover" style="width:100%;">
                    <thead>
                        <tr><th>No</th><th>Nama</th><th>Tipe</th><th>Aturan</th><th>Produk</th><th>Berlaku untuk</th><th>Periode</th><th>Status</th><th width="130">Aksi</th></tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#bonus-table').DataTable({
        processing: true, serverSide: true, ordering: false,
        ajax: "{{ route('admin.bonus-rules.index') }}",
        columns: [
            {data: 'DT_RowIndex', searchable: false}, {data: 'name'}, {data: 'type_label', searchable: false},
            {data: 'rule_text', searchable: false}, {data: 'products_text', searchable: false}, {data: 'customers', searchable: false},
            {data: 'period', searchable: false}, {data: 'status_badge', searchable: false}, {data: 'action', searchable: false}
        ],
        language: {processing: "Memproses...", search: "Cari:", lengthMenu: "Tampilkan _MENU_ data", info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Belum ada aturan bonus", zeroRecords: "Data tidak ditemukan", emptyTable: "Belum ada aturan bonus",
            paginate: {first: "Pertama", previous: "Sebelumnya", next: "Selanjutnya", last: "Terakhir"}}
    });
});
</script>
@endpush
