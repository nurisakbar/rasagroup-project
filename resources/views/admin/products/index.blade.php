@extends('layouts.admin')

@section('title', 'Produk')
@section('page-title', 'Master Data Produk')
@section('page-description', 'Kelola master data produk')

@section('breadcrumb')
    <li class="active">Produk</li>
@endsection

@push('styles')
<style>
    .filter-box { margin-bottom: 0; }
    #products-table_length { margin-bottom: 15px; }
</style>
@endpush

@section('content')
    <!-- Filter Box -->
    <div class="box box-default filter-box">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter & Aksi</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <select id="filter-status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Brand</label>
                        <select id="filter-brand" class="form-control select2" style="width: 100%;">
                            <option value="">Semua Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select id="filter-category" class="form-control select2" style="width: 100%;">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Sumber</label>
                        <select id="filter-sync-source" class="form-control">
                            <option value="">Semua Sumber</option>
                            <option value="jubelio">Jubelio</option>
                            <option value="qad">QAD</option>
                            <option value="both">Keduanya</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <button type="button" id="btn-filter" class="btn btn-default">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <button type="button" id="btn-reset" class="btn btn-default">
                            <i class="fa fa-times"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-right">
                    <div class="form-group" style="margin-bottom: 0;">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah
                        </a>
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#importModal">
                            <i class="fa fa-file-excel-o"></i> Update dari Excel
                        </button>
                        @include('admin.partials.sync-qad-jubelio')
                        @if(app()->environment('local'))
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteAllProducts()" title="Hanya tersedia di APP_ENV=local">
                                <i class="fa fa-trash"></i> Hapus Semua
                            </button>
                            <form id="delete-all-products-form" action="{{ route('admin.products.destroy-all') }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="confirm" id="delete-all-confirm-input" value="">
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Produk</h3>
                </div>
                <div class="box-body">
                    <table id="products-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Gambar</th>
                                <th style="width: 50px;">QRCode</th>
                                <th style="width: 100px;">Kode</th>
                                <th>Nama Produk</th>
                                <th>Brand / Kategori</th>
                                <th>Ukuran</th>
                                <th>Harga</th>
                                <th>Poin</th>
                                <th>Sumber</th>
                                <th>Status</th>
                                <th style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="import-excel-form" action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="importModalLabel">Update Produk dari Excel</h4>
                    </div>
                    <div class="modal-body">
                        <div id="upload-form-group" class="form-group">
                            <label>File Excel</label>
                            <input type="file" name="file" id="import-file" class="form-control" accept=".xls,.xlsx,.csv" required>
                            <small class="text-muted">Data yang dapat diupdate: nama produk, kategori, dan brand (berdasarkan SKU / product_code).</small>
                        </div>
                        <div id="import-progress-container" style="display: none;">
                            <p id="import-status-text" class="text-center">Menyiapkan import...</p>
                            <div class="progress progress-sm active">
                                <div id="import-progress-bar" class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                    <span class="sr-only">0% Complete</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('admin.products.template') }}" class="btn btn-default pull-left"><i class="fa fa-download"></i> Download Template</a>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" id="btn-import-submit" class="btn btn-primary">Upload & Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        allowClear: true
    });

    var table = $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.products.index') }}",
            data: function(d) {
                d.status = $('#filter-status').val();
                d.brand_id = $('#filter-brand').val();
                d.category_id = $('#filter-category').val();
                d.sync_source = $('#filter-sync-source').val();
            }
        },
        columns: [
            { data: 'image_display', name: 'image', orderable: false, searchable: false },
            { data: 'qrcode_display', name: 'qrcode', orderable: false, searchable: false },
            { data: 'code_display', name: 'code' },
            { data: 'name_info', name: 'name' },
            { data: 'brand_info', name: 'brand', orderable: false, searchable: true },
            { data: 'size_unit', name: 'size', orderable: false, searchable: true },
            { data: 'price_formatted', name: 'price' },
            { data: 'reseller_point_display', name: 'reseller_point', orderable: true },
            { data: 'sync_sources_info', name: 'sync_sources', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'asc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Tidak ada data yang cocok",
            emptyTable: "Tidak ada data tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });

    // Filter handlers
    $('#filter-status, #filter-brand, #filter-category, #filter-sync-source').change(function() {
        table.draw();
    });

    $('#btn-filter').on('click', function() {
        table.draw();
    });

    $('#btn-reset').on('click', function() {
        $('#filter-status').val('');
        $('#filter-brand').val('').trigger('change');
        $('#filter-category').val('').trigger('change');
        $('#filter-sync-source').val('');
        table.draw();
    });

    // Import Excel AJAX
    $('#import-excel-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var fileInput = document.getElementById('import-file');
        if (fileInput.files.length === 0) return;

        var formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        var submitBtn = $('#btn-import-submit');
        submitBtn.prop('disabled', true);
        
        $('#upload-form-group').hide();
        $('#import-progress-container').show();
        $('#import-progress-bar').css('width', '0%');
        $('#import-status-text').text('Mengupload file...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if(response.success && response.batch_id) {
                    pollImportStatus(response.batch_id, submitBtn, table);
                } else {
                    alert('Gagal memulai import: ' + (response.message || 'Unknown error'));
                    resetImportModal(submitBtn);
                }
            },
            error: function(xhr) {
                alert('Terjadi kesalahan saat upload file.');
                resetImportModal(submitBtn);
            }
        });
    });

    function pollImportStatus(batchId, submitBtn, table) {
        var pollInterval = setInterval(function() {
            $.ajax({
                url: "{{ route('admin.products.import-status') }}",
                type: 'GET',
                data: { batch_id: batchId },
                success: function(res) {
                    if(res.status) {
                        $('#import-status-text').text(res.message);
                        if(res.total > 0) {
                            var pct = Math.round((res.processed / res.total) * 100);
                            $('#import-progress-bar').css('width', pct + '%');
                        }

                        if(res.status === 'completed' || res.status === 'completed_with_errors' || res.status === 'failed') {
                            clearInterval(pollInterval);
                            alert(res.message);
                            if(res.errors && res.errors.length > 0) {
                                console.log(res.errors);
                            }
                            table.draw();
                            $('#importModal').modal('hide');
                            resetImportModal(submitBtn);
                        }
                    }
                },
                error: function() {
                    clearInterval(pollInterval);
                    alert('Gagal mengecek status import.');
                    resetImportModal(submitBtn);
                }
            });
        }, 1500);
    }

    function resetImportModal(submitBtn) {
        submitBtn.prop('disabled', false);
        $('#upload-form-group').show();
        $('#import-progress-container').hide();
        document.getElementById('import-file').value = '';
    }
});

function confirmDeleteAllProducts() {
    if (!confirm('PERINGATAN: Semua produk dan data terkait akan dihapus permanen:\n- Stok gudang & riwayat stok\n- Keranjang (regular & distributor)\n- Pesanan & item pesanan\n- Menu Hari Ini\n- Harga level & gambar produk\n\nLanjutkan?')) {
        return;
    }
    var typed = prompt('Ketik HAPUS SEMUA untuk konfirmasi:');
    if (typed !== 'HAPUS SEMUA') {
        alert('Konfirmasi dibatalkan.');
        return;
    }
    document.getElementById('delete-all-confirm-input').value = typed;
    document.getElementById('delete-all-products-form').submit();
}
</script>
@endpush
