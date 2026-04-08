@extends('layouts.admin')

@section('title', 'Pengaturan Potongan Harga')
@section('page-title', 'Pengaturan Potongan Harga')

@section('breadcrumb')
    <li class="active">Potongan Harga</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Potongan Harga Berdasarkan Jumlah Item</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.discount-tiers.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Tambah Potongan Harga
                    </a>
                </div>
            </div>
            <div class="box-body">
                <table id="tiers-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Min. Item Belanja</th>
                            <th>Potongan (%)</th>
                            <th>Status</th>
                            <th width="150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#tiers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.discount-tiers.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'quantity_display', name: 'min_quantity' },
            { data: 'discount_display', name: 'discount_percent' },
            { data: 'status_badge', name: 'is_active', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            emptyTable: "Belum ada data potongan harga.",
            zeroRecords: "Tidak ada data yang cocok",
            paginate: { first: "Pertama", previous: "Sebelumnya", next: "Selanjutnya", last: "Terakhir" }
        }
    });

    $(document).on('submit', '.delete-form', function(e) {
        if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
