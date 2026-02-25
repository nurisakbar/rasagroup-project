@extends('layouts.shop')

@section('title', 'Kelola Pesanan Masuk')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Kelola Pesanan Masuk
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
                                <div class="card-header bg-white border-bottom p-4">
                                    <h3 class="mb-0">Kelola Pesanan Masuk</h3>
                                    <p class="text-muted font-sm">Daftar pesanan dari customer yang masuk ke warehouse Anda.</p>
                                </div>
                                <div class="card-body p-4">
                                    <!-- Filter Box -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-3">
                                            <label class="font-xs text-muted mb-1">Status</label>
                                            <select id="filter-status" class="form-select form-select-sm">
                                                <option value="">Semua Status</option>
                                                <option value="pending">Pending</option>
                                                <option value="processing">Processing</option>
                                                <option value="shipped">Shipped</option>
                                                <option value="delivered">Delivered</option>
                                                <option value="completed">Completed</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="font-xs text-muted mb-1">Tipe Order</label>
                                            <select id="filter-order-type" class="form-select form-select-sm">
                                                <option value="">Semua Tipe</option>
                                                <option value="regular">Online (Regular)</option>
                                                <option value="pos">Offline (POS)</option>
                                                <option value="distributor">Distributor</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="font-xs text-muted mb-1">Dari Tanggal</label>
                                            <input type="date" id="filter-date-from" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="font-xs text-muted mb-1">Sampai Tanggal</label>
                                            <input type="date" id="filter-date-to" class="form-control form-control-sm">
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="manage-orders-table" class="table table-clean font-sm w-100">
                                            <thead>
                                                <tr class="main-heading">
                                                    <th width="5%">No</th>
                                                    <th>No. Pesanan</th>
                                                    <th>Pembeli</th>
                                                    <th>Ekspedisi</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Pembayaran</th>
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
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_filter { display: none; }
    #manage-orders-table_wrapper .row:first-child { display: none; }
    #manage-orders-table thead th { border-top: 0; border-bottom: 1px solid #f2f2f2; color: #253D4E; font-weight: 700; padding-bottom: 15px; }
    #manage-orders-table tbody td { vertical-align: middle; padding: 15px 0; border-top: 0; border-bottom: 1px solid #f2f2f2; }
    .page-link { border-radius: 5px !important; margin: 0 3px; color: #253D4E; border-color: #f2f2f2; font-size: 13px; }
    .page-item.active .page-link { background-color: #3BB77E; border-color: #3BB77E; color: #fff; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#manage-orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('distributor.manage-orders.index') }}",
            data: function(d) {
                d.status = $('#filter-status').val();
                d.order_type = $('#filter-order-type').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_info', name: 'order_number' },
            { data: 'buyer_info', name: 'user.name' },
            { data: 'expedition_info', name: 'expedition.name', orderable: false },
            { data: 'total_formatted', name: 'total_amount' },
            { data: 'status_badge', name: 'order_status' },
            { data: 'payment_badge', name: 'payment_status' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[1, 'desc']],
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

    $('#filter-status, #filter-order-type, #filter-date-from, #filter-date-to').change(function() {
        table.draw();
    });
});
</script>
@endpush
