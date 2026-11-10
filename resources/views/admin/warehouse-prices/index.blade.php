@extends('layouts.admin')

@section('title', 'Master Harga')
@section('page-title', 'Master Harga per Warehouse')
@section('page-description', 'Harga katalog per warehouse')

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">Master Harga</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
@endpush

@section('content')
    <div class="callout callout-info">
        <p>Produk tanpa harga master di sebuah warehouse memakai harga global produk. Mengubah harga membuat harga baru yang langsung berlaku; harga lama tetap tersimpan di riwayat supaya penjualan lama tetap sesuai.</p>
        <p>Harga ini dipakai di toko (katalog, detail produk, keranjang, checkout) sesuai hub pembeli, dan di restock distributor sesuai hub default distributor.</p>
    </div>

    @if(session('import_errors'))
        <div class="alert alert-danger">
            <ul style="margin: 0;">
                @foreach(session('import_errors') as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="box box-default">
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('admin.warehouse-prices.index') }}">
                        <label for="warehouse_id">Warehouse</label>
                        <select class="form-control" id="warehouse_id" name="warehouse_id" onchange="this.form.submit()">
                            <option value="">Pilih warehouse</option>
                            @foreach($warehouses as $item)
                                <option value="{{ $item->id }}" {{ $warehouse && $warehouse->id === $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}{{ $item->kode_hub ? ' (' . $item->kode_hub . ')' : '' }}{{ $item->is_active ? '' : ' - nonaktif' }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-md-6">
                    <form method="POST" action="{{ route('admin.warehouse-prices.import') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="warehouse_id" value="{{ $warehouse?->id }}">
                        <label for="import_file">Import Excel</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="import_file" name="file" accept=".xlsx,.xls,.csv" required>
                            <span class="input-group-btn"><button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Import</button></span>
                        </div>
                        <p class="help-block">Unduh Excel sebuah warehouse, isi kolom <code>harga_baru</code>, lalu unggah. Harga baru langsung berlaku. File boleh berisi beberapa warehouse.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($warehouse)
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">{{ $warehouse->name }}</h3>
            <div class="box-tools">
                <label style="font-weight: normal; margin-right: 12px;"><input type="checkbox" id="only_master"> Hanya yang punya harga master</label>
                <a class="btn btn-success btn-sm" href="{{ route('admin.warehouse-prices.export', $warehouse) }}"><i class="fa fa-file-excel-o"></i> Unduh Excel</a>
            </div>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-hover" id="prices-table" style="width: 100%;">
                <thead><tr><th>Kode</th><th>Produk</th><th>Harga Global</th><th>Harga Berlaku di Warehouse</th><th></th></tr></thead>
            </table>
        </div>
    </div>

    @include('admin.warehouse-prices.partials.editor', ['warehouse' => $warehouse])
    @endif
@endsection

@if($warehouse)
@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script>
    $(function () {
        var table = $('#prices-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: @json(route('admin.warehouse-prices.datatable', $warehouse)),
                data: function (d) { d.only_master = $('#only_master').is(':checked') ? '1' : ''; }
            },
            columns: [
                {data: 'code', name: 'code'},
                {data: 'name', name: 'name'},
                {data: 'price', name: 'price', searchable: false},
                {data: 'effective_price', name: 'effective_price', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[1, 'asc']]
        });

        $('#only_master').on('change', function () { table.ajax.reload(); });

        $(document).on('warehouse-price:changed', function () { table.ajax.reload(null, false); });
    });
</script>
@endpush
@endif
