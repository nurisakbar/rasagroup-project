@extends('layouts.shop')

@section('title', 'Kelola Stock')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Kelola Stock
        </div>
    </div>
</div>

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4">
                    @include('buyer.partials.sidebar')
                </div>
                <div class="col-lg-8">
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0">Kelola Stock</h3>
                                        <p class="text-muted font-sm">Pantau dan update stok produk di warehouse Anda.</p>
                                    </div>
                                    <div>
                                        <form action="{{ route('distributor.stock.sync') }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-fill-out rounded-pill">
                                                <i class="fi-rs-refresh mr-5"></i> Sinkron Produk
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <!-- Filter Tabs -->
                                    <div class="nav-tabs-wrap mb-4">
                                        <ul class="nav nav-pills dash-tabs" id="stock-filter-tabs">
                                            <li class="nav-item">
                                                <button class="nav-link active" data-filter="">Semua</button>
                                            </li>
                                            <li class="nav-item">
                                                <button class="nav-link" data-filter="low">Stok Rendah (≤10)</button>
                                            </li>
                                            <li class="nav-item">
                                                <button class="nav-link" data-filter="medium">Stok Sedang (11-50)</button>
                                            </li>
                                            <li class="nav-item">
                                                <button class="nav-link" data-filter="high">Stok Banyak (>50)</button>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="stock-table" class="table table-clean font-sm w-100">
                                            <thead>
                                                <tr class="main-heading">
                                                    <th width="50">Produk</th>
                                                    <th>Nama Produk</th>
                                                    <th>Price</th>
                                                    <th>Stock</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Stock Modal -->
<div class="modal fade" id="updateStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 border-radius-15 shadow">
            <div class="modal-header border-bottom p-4">
                <h5 class="modal-title">Update Stok Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateStockForm">
                @csrf
                <input type="hidden" name="stock_id" id="modal-stock-id">
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <img id="modal-product-image" src="" alt="" class="border-radius-10 me-3" style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h6 id="modal-product-name" class="mb-1"></h6>
                            <p class="text-brand font-sm mb-0">Rp <span id="modal-product-price"></span></p>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label font-sm fw-bold">Jumlah Stok Saat Ini</label>
                        <input type="number" name="stock" id="modal-current-stock" class="form-control font-sm border-radius-5" min="0" required>
                        <small class="text-muted mt-1 d-block">Masukkan jumlah total stok fisik yang tersedia.</small>
                    </div>
                </div>
                <div class="modal-footer border-top p-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-brand rounded-pill px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_filter { display: none; }
    #stock-table_wrapper .row:first-child { display: none; }
    #stock-table thead th { border-top: 0; border-bottom: 1px solid #f2f2f2; color: #253D4E; font-weight: 700; padding-bottom: 15px; }
    #stock-table tbody td { vertical-align: middle; padding: 15px 0; border-top: 0; border-bottom: 1px solid #f2f2f2; }
    .page-link { border-radius: 5px !important; margin: 0 3px; color: #253D4E; border-color: #f2f2f2; font-size: 13px; }
    .page-item.active .page-link { background-color: #3BB77E; border-color: #3BB77E; color: #fff; }
    
    .dash-tabs .nav-link { 
        background-color: #f7f8f9; color: #253D4E; border-radius: 30px; padding: 8px 20px; font-weight: 600; font-size: 13px; margin-right: 10px; border: 1px solid #f2f2f2; 
    }
    .dash-tabs .nav-link.active { background-color: #3BB77E; color: #fff; border-color: #3BB77E; }
    
    .badge.bg-red { background-color: #fd3d11 !important; color: white; }
    .badge.bg-yellow { background-color: #ffc107 !important; color: #212529; }
    .badge.bg-green { background-color: #3bb77e !important; color: white; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    var stockFilter = '';

    var table = $('#stock-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('distributor.stock.index') }}",
            data: function(d) {
                d.filter = stockFilter;
            }
        },
        columns: [
            { data: 'product_image', name: 'product_id', orderable: false, searchable: false },
            { data: 'product_name', name: 'product.name' },
            { data: 'product_price', name: 'product.price' },
            { data: 'stock_badge', name: 'stock' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[3, 'asc']],
        pageLength: 10,
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-brand" role="status"></div>',
            info: "Menampilkan _START_ - _END_ dari _TOTAL_",
            paginate: {
                previous: '<i class="fi-rs-angle-left"></i>',
                next: '<i class="fi-rs-angle-right"></i>'
            }
        }
    });

    $('#stock-filter-tabs .nav-link').on('click', function() {
        $('#stock-filter-tabs .nav-link').removeClass('active');
        $(this).addClass('active');
        stockFilter = $(this).data('filter');
        table.draw();
    });

    // Handle modal trigger
    $(document).on('click', '.btn-update-stock', function() {
        var stockId = $(this).data('stock-id');
        var productName = $(this).data('product-name');
        var productPrice = $(this).data('product-price');
        var currentStock = $(this).data('current-stock');
        var productImage = $(this).data('product-image');

        $('#modal-stock-id').val(stockId);
        $('#modal-product-name').text(productName);
        $('#modal-product-price').text(productPrice);
        $('#modal-current-stock').val(currentStock);
        $('#modal-product-image').attr('src', productImage || '{{ asset("themes/nest-frontend/assets/imgs/theme/icons/icon-cart.svg") }}');
        
        $('#updateStockModal').modal('show');
    });

    // Handle form submit
    $('#updateStockForm').on('submit', function(e) {
        e.preventDefault();
        var stockId = $('#modal-stock-id').val();
        var formData = $(this).serialize();

        $.ajax({
            url: "{{ url('distributor/stock') }}/" + stockId,
            type: "POST",
            data: formData + "&_method=PUT",
            success: function(response) {
                $('#updateStockModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                table.draw();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan saat mengupdate stok.'
                });
            }
        });
    });
});
</script>
@endpush
