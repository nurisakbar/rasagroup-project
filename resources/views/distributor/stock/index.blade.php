@extends('layouts.distributor')

@section('title', 'Kelola Stock')
@section('page-title', 'Kelola Stock')
@section('page-description', 'Kelola stock produk di hub Anda')

@section('breadcrumb')
    <li class="active">Kelola Stock</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
<style>
    .dataTables_filter { display: none; }
    .filter-box { margin-bottom: 0; }
    #stock-table_length { margin-bottom: 15px; }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Stock Produk - {{ $warehouse->name }}</h3>
                    <div class="box-tools">
                        <form action="{{ route('distributor.stock.sync') }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Sync semua produk dengan stock 0?');">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fa fa-refresh"></i> Sync Produk
                            </button>
                        </form>
                    </div>
                </div>
                <!-- Filter Box -->
                <div class="box box-default filter-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-filter"></i> Filter Stock</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cari Produk</label>
                                    <input type="text" id="filter-search" class="form-control" placeholder="Nama produk atau kode...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Filter Stock</label>
                                    <select id="filter-stock" class="form-control">
                                        <option value="">Semua Stock</option>
                                        <option value="low">Stock Rendah (≤10)</option>
                                        <option value="medium">Stock Sedang (11-50)</option>
                                        <option value="high">Stock Tinggi (>50)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="button" id="btn-reset" class="btn btn-default">
                                            <i class="fa fa-refresh"></i> Reset Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Table -->
                <div class="box-body">
                    <table id="stock-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="60px">Gambar</th>
                                <th>Nama Produk</th>
                                <th>Brand</th>
                                <th>Harga</th>
                                <th width="120px">Stock</th>
                                <th>Terakhir Update</th>
                                <th width="100px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Stock Modal Template (will be populated dynamically) -->
    <div class="modal fade" id="updateStockModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="updateStockForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header" style="background-color: #f39c12; color: #fff;">
                        <button type="button" class="close" data-dismiss="modal" style="color: #fff;">&times;</button>
                        <h4 class="modal-title">Update Stock</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4" id="modal-product-image">
                                <!-- Product image will be inserted here -->
                            </div>
                            <div class="col-md-8">
                                <p id="modal-product-name"><strong></strong></p>
                                <p class="text-muted" id="modal-product-price">Harga: -</p>
                                <hr>
                                <div class="form-group">
                                    <label for="modal-stock">Jumlah Stock Baru</label>
                                    <input type="number" class="form-control input-lg" id="modal-stock" name="stock" value="0" min="0" required autofocus>
                                    <p class="help-block">Stock saat ini: <strong id="modal-current-stock">0</strong> unit</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#stock-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('distributor.stock.index') }}",
            data: function(d) {
                d.filter = $('#filter-stock').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'product_image', name: 'product.image', orderable: false, searchable: false },
            { data: 'product_name', name: 'product.name' },
            { data: 'product_brand', name: 'product.brand.name', orderable: false },
            { data: 'product_price', name: 'product.price' },
            { data: 'stock_badge', name: 'stock', orderable: true },
            { data: 'updated_at_formatted', name: 'updated_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']],
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Tidak ada data yang cocok",
            emptyTable: "Tidak ada stock tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });

    // Custom search
    $('#filter-search').on('keyup', function() {
        table.column(2).search(this.value).draw();
    });

    // Filter by stock level
    $('#filter-stock').on('change', function() {
        table.draw();
    });

    // Reset button
    $('#btn-reset').on('click', function() {
        $('#filter-search').val('');
        $('#filter-stock').val('');
        table.search('').columns().search('').draw();
    });

    // Handle update stock modal
    $(document).on('click', '.btn-update-stock', function() {
        var stockId = $(this).data('stock-id');
        var productName = $(this).data('product-name');
        var productPrice = $(this).data('product-price');
        var currentStock = $(this).data('current-stock');
        var productImage = $(this).data('product-image');
        
        // Set form action
        var updateUrl = "{{ route('distributor.stock.update', ':id') }}".replace(':id', stockId);
        $('#updateStockForm').attr('action', updateUrl);
        
        // Populate modal
        $('#modal-product-name strong').text(productName);
        $('#modal-product-price').text('Harga: Rp ' + productPrice);
        $('#modal-current-stock').text(currentStock);
        $('#modal-stock').val(currentStock);
        
        // Set product image
        if (productImage) {
            $('#modal-product-image').html('<img src="' + productImage + '" alt="' + productName + '" class="img-responsive" style="border-radius: 5px;">');
        } else {
            $('#modal-product-image').html('<div style="width: 100%; height: 150px; background: #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center;"><i class="fa fa-image fa-3x text-muted"></i></div>');
        }
    });

    // Handle form submission
    $('#updateStockForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();
        var formAction = form.attr('action');
        
        $.ajax({
            url: formAction,
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#updateStockModal').modal('hide');
                table.draw();
                // Show success message if available
                if (response.message) {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                var errorMsg = 'Terjadi kesalahan saat update stock.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
            }
        });
    });
});
</script>
@endpush
