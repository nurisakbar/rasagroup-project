@extends('layouts.admin')

@section('title', 'Master Harga')
@section('page-title', 'Master Harga per Warehouse')
@section('page-description', 'Harga katalog per warehouse yang berlaku per tanggal')

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">Master Harga</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
@endpush

@section('content')
    <div class="callout callout-info">
        <p>Produk tanpa harga master di sebuah warehouse memakai harga global produk. Harga yang sudah berlaku tidak bisa diubah atau dihapus: buat harga baru untuk menggantinya, supaya riwayat dan penjualan lama tetap sesuai.</p>
        <p><strong>Catatan transisi:</strong> master harga belum dipakai untuk menghitung harga di keranjang dan checkout sampai tahap penerapan transaksi dirilis.</p>
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
                        <p class="help-block">Unduh Excel sebuah warehouse, isi kolom <code>harga_baru</code> (opsional <code>berlaku_mulai</code>, <code>berlaku_sampai</code> dalam WIB), lalu unggah. File boleh berisi beberapa warehouse.</p>
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

    <div class="modal fade" id="price-modal" tabindex="-1" role="dialog" aria-labelledby="price-modal-title">
        <div class="modal-dialog" role="document">
            <form class="modal-content" id="price-form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="price-modal-title">Harga Baru</h4>
                </div>
                <div class="modal-body">
                    <p id="price-product" class="text-muted"></p>
                    <input type="hidden" name="product_id" id="price-product-id">
                    <div class="form-group">
                        <label for="price-value">Harga (Rp)</label>
                        <input type="number" class="form-control" id="price-value" name="price" min="1" step="any" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label for="price-from">Berlaku mulai (WIB)</label>
                            <input type="datetime-local" class="form-control" id="price-from" name="effective_from">
                            <p class="help-block">Kosongkan untuk berlaku sekarang.</p>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label for="price-to">Berlaku sampai (WIB)</label>
                            <input type="datetime-local" class="form-control" id="price-to" name="effective_to">
                            <p class="help-block">Kosongkan bila tanpa batas.</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="price-note">Catatan</label>
                        <input type="text" class="form-control" id="price-note" name="note" maxlength="255">
                    </div>
                    <div class="alert alert-danger" id="price-error" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="history-modal" tabindex="-1" role="dialog" aria-labelledby="history-modal-title">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="history-modal-title">Riwayat Harga</h4>
                </div>
                <div class="modal-body">
                    <p id="history-product" class="text-muted"></p>
                    <table class="table table-condensed">
                        <thead><tr><th>Harga</th><th>Berlaku</th><th>Status</th><th>Sumber</th><th>Catatan</th><th>Dibuat</th><th></th></tr></thead>
                        <tbody id="history-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@if($warehouse)
@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script>
    $(function () {
        var csrf = @json(csrf_token());
        var storeUrl = @json(route('admin.warehouse-prices.store', $warehouse));
        var historyUrl = @json(route('admin.warehouse-prices.history', [$warehouse, '__PRODUCT__']));
        var destroyUrl = @json(route('admin.warehouse-prices.destroy', [$warehouse, '__PRICE__']));

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

        function text(value) { return $('<div>').text(value === null || value === undefined ? '' : value).html(); }

        $('#prices-table').on('click', '.btn-set-price', function () {
            $('#price-form')[0].reset();
            $('#price-error').hide();
            $('#price-product-id').val($(this).data('product-id'));
            $('#price-product').text($(this).data('product-name'));
            $('#price-modal').modal('show');
        });

        $('#price-form').on('submit', function (event) {
            event.preventDefault();
            $.ajax({url: storeUrl, method: 'POST', data: $(this).serialize() + '&_token=' + encodeURIComponent(csrf)})
                .done(function () { $('#price-modal').modal('hide'); table.ajax.reload(null, false); })
                .fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan harga.';
                    $('#price-error').text(message).show();
                });
        });

        function loadHistory(productId, productName) {
            $('#history-product').text(productName);
            $('#history-body').html('<tr><td colspan="7">Memuat...</td></tr>');
            $.getJSON(historyUrl.replace('__PRODUCT__', productId)).done(function (data) {
                if (!data.rows.length) {
                    $('#history-body').html('<tr><td colspan="7" class="text-muted">Belum ada harga master. Harga global: ' + text(data.global_price) + '</td></tr>');
                    return;
                }
                var html = data.rows.map(function (row) {
                    var period = text(row.effective_from) + (row.effective_to ? ' s.d. ' + text(row.effective_to) : '');
                    var cancel = row.can_cancel ? '<button type="button" class="btn btn-danger btn-xs btn-cancel" data-price-id="' + text(row.id) + '">Batalkan</button>' : '';
                    return '<tr><td>' + text(row.price) + '</td><td>' + period + '</td><td>' + text(row.status) + '</td><td>' + text(row.source) + '</td><td>' + text(row.note) + '</td><td>' + text(row.created_by) + '<br><small class="text-muted">' + text(row.created_at) + '</small></td><td>' + cancel + '</td></tr>';
                }).join('');
                $('#history-body').html(html);
            });
            $('#history-modal').data('product-id', productId).data('product-name', productName);
        }

        $('#prices-table').on('click', '.btn-history', function () {
            loadHistory($(this).data('product-id'), $(this).data('product-name'));
            $('#history-modal').modal('show');
        });

        $('#history-body').on('click', '.btn-cancel', function () {
            if (!confirm('Batalkan harga terjadwal ini?')) { return; }
            $.ajax({url: destroyUrl.replace('__PRICE__', $(this).data('price-id')), method: 'POST', data: {_method: 'DELETE', _token: csrf}})
                .done(function () {
                    var modal = $('#history-modal');
                    loadHistory(modal.data('product-id'), modal.data('product-name'));
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal membatalkan.'); });
        });
    });
</script>
@endpush
@endif
