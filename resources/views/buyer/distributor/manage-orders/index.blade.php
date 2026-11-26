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
                    <div class="tab-content account dashboard-content mo-content">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom p-4">
                                    <h3 class="mb-0">Kelola Pesanan Masuk</h3>
                                    <p class="text-muted font-sm">Daftar pesanan dari customer yang masuk ke warehouse Anda.</p>
                                </div>
                                <div class="card-body p-4 mo-card-body">
                                    <!-- Filter Box -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-6 col-md-3">
                                            <label class="font-xs text-muted mb-1">Status</label>
                                            <select id="filter-status" class="form-select form-select-sm">
                                                <option value="">Semua Status</option>
                                                @foreach(\App\Models\Order::ORDER_STATUS_LABELS as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="font-xs text-muted mb-1">Tipe Order</label>
                                            <select id="filter-order-type" class="form-select form-select-sm">
                                                <option value="">Semua Tipe</option>
                                                <option value="regular">Online (Regular)</option>
                                                <option value="pos">Offline (POS)</option>
                                                <option value="distributor">Distributor</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="font-xs text-muted mb-1">Dari Tanggal</label>
                                            <input type="date" id="filter-date-from" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="font-xs text-muted mb-1">Sampai Tanggal</label>
                                            <input type="date" id="filter-date-to" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
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
    .mo-content { padding-left: 0 !important; }
    .page-link { border-radius: 8px !important; margin: 0 3px; color: #253D4E; border-color: #E3D6C8; font-size: 13px; }
    .page-item.active .page-link { background-color: #6A1B1B; border-color: #6A1B1B; color: #fff; }
    .dataTables_info { font-size: 13px; color: #8A7B70; padding-top: 14px !important; }

    #manage-orders-table { width: 100% !important; border-collapse: separate; border-spacing: 0; font-size: 14px; }
    #manage-orders-table thead th {
        white-space: nowrap; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; color: #8A7B70;
        font-weight: 700; padding: 0 22px 12px 8px; border-bottom: 2px solid #F0E6DB; background: transparent;
    }
    #manage-orders-table thead th.sorting::before, #manage-orders-table thead th.sorting::after,
    #manage-orders-table thead th.sorting_asc::before, #manage-orders-table thead th.sorting_asc::after,
    #manage-orders-table thead th.sorting_desc::before, #manage-orders-table thead th.sorting_desc::after { opacity: .35; }
    #manage-orders-table tbody td { padding: 16px 8px; vertical-align: middle; border-bottom: 1px solid #F0E6DB; background: #fff; }
    #manage-orders-table tbody tr:hover td { background: #FFFDFB; }
    #manage-orders-table th:first-child, #manage-orders-table td:first-child { display: none; }  /* kolom No */

    .mo-order { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .mo-order strong { color: #253D4E; font-size: 15px; white-space: nowrap; }
    .mo-date { font-size: 12px; color: #8A7B70; margin-top: 3px; white-space: nowrap; }
    .mo-buyer { display: flex; flex-direction: column; min-width: 120px; max-width: 170px; }
    .mo-buyer strong { color: #253D4E; }
    .mo-email { font-size: 12px; color: #8A7B70; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mo-exp { white-space: nowrap; color: #253D4E; }
    .mo-exp i { color: #6A1B1B; margin-right: 4px; }
    .mo-resi { font-size: 12px; color: #6A1B1B; font-weight: 700; margin-top: 2px; }
    .mo-resi-none { color: #B7791F; font-weight: 600; }
    .mo-muted { color: #9A9A9A; }
    .mo-total { font-weight: 800; color: #253D4E; white-space: nowrap; }
    .mo-badge { display: inline-flex; align-items: center; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
    .mo-status { font-size: 12px; padding: 5px 12px; }
    .mo-tone-warn { background: #FFF4DB; color: #9A6200; }
    .mo-tone-info { background: #E4F2FB; color: #1B6A9C; }
    .mo-tone-primary { background: #EEE6FB; color: #5B33A8; }
    .mo-tone-ok { background: #E3F4E9; color: #1E7B45; }
    .mo-tone-bad { background: #FBE6E6; color: #B42318; }
    .mo-tone-muted { background: #ECECEC; color: #6B6B6B; }
    .mo-actions { display: flex; gap: 6px; justify-content: flex-end; white-space: nowrap; }
    .mo-btn { display: inline-flex; align-items: center; justify-content: center; height: 34px; padding: 0 14px; border-radius: 10px; font-weight: 700; font-size: 13px; text-decoration: none; border: 2px solid #6A1B1B; }
    .mo-btn-main { background: #6A1B1B !important; color: #fff !important; }
    .mo-btn-main:hover { background: #531414 !important; border-color: #531414; }
    .mo-btn-out { background: #fff !important; color: #6A1B1B !important; }
    .mo-btn-icon { width: 34px; padding: 0; }
    .mo-btn-icon i { font-size: 15px; line-height: 1; }
    .mo-btn-text { display: none; }
    .mo-btn-out:hover { background: #FAF6F1 !important; }

    /* Layar sedang: kolom lebih rapat */
    @media (max-width: 1199px) and (min-width: 768px) { #manage-orders-table tbody td { padding: 12px 8px; } .mo-actions { flex-direction: column; } }

    /* Tampilan mobile: setiap pesanan menjadi kartu */
    @media (max-width: 767px) {
        .mo-card-body { padding: 16px 12px !important; }
        #filter-status, #filter-order-type, #filter-date-from, #filter-date-to { height: 44px; font-size: 14px; }
        #manage-orders-table, #manage-orders-table tbody { display: block; width: 100% !important; }
        #manage-orders-table thead { display: none; }
        #manage-orders-table tbody tr {
            display: flex; flex-wrap: wrap; align-items: flex-start;
            background: #fff; border: 1px solid #E3D6C8; border-radius: 14px;
            margin-bottom: 14px; padding: 12px 14px; box-shadow: 0 4px 14px rgba(106, 27, 27, 0.08);
        }
        #manage-orders-table tbody td {
            display: block; border: 0 !important; padding: 6px 0 !important; box-sizing: border-box;
            flex: 0 0 100%; max-width: 100%; font-size: 14px; text-align: left !important; background: transparent !important;
        }
        #manage-orders-table tbody td:nth-child(2) { padding-bottom: 10px !important; margin-bottom: 4px; border-bottom: 1px dashed #E3D6C8 !important; }
        #manage-orders-table tbody td:nth-child(4),
        #manage-orders-table tbody td:nth-child(5),
        #manage-orders-table tbody td:nth-child(6),
        #manage-orders-table tbody td:nth-child(7) { flex: 0 0 50%; max-width: 50%; }
        #manage-orders-table tbody td:nth-child(5),
        #manage-orders-table tbody td:nth-child(7) { text-align: right !important; }
        #manage-orders-table tbody td:nth-child(5) .mo-total { font-size: 16px; }
        #manage-orders-table tbody td[data-label]::before {
            content: attr(data-label); display: block; float: none !important; width: auto !important; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .4px; color: #8A7B70; margin-bottom: 3px;
        }
        #manage-orders-table tbody td:last-child { margin-top: 6px; padding-top: 10px !important; border-top: 1px dashed #E3D6C8 !important; }
        #manage-orders-table tbody td:last-child .mo-actions { gap: 8px; }
        #manage-orders-table tbody td:last-child .mo-btn { flex: 1 1 0; height: 44px; font-size: 14px; width: auto; padding: 0 14px; }
        #manage-orders-table tbody td:last-child .mo-btn-text { display: inline; margin-left: 6px; }
        .mo-buyer { max-width: none; }
        .mo-email { white-space: normal; word-break: break-all; }
        #manage-orders-table tbody td.dataTables_empty { text-align: center !important; padding: 24px 0 !important; }
        #manage-orders-table tbody td.dataTables_empty::before { display: none; }
        .dataTables_info, .dataTables_paginate { float: none !important; text-align: center !important; }
        .pagination { flex-wrap: wrap; justify-content: center; }
    }
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
        // Label tiap kolom untuk tampilan kartu di mobile
        createdRow: function(row) {
            var labels = ['', '', 'Pembeli', 'Ekspedisi', 'Total', 'Status', 'Pembayaran', ''];
            $('td', row).each(function(i) { if (labels[i]) { $(this).attr('data-label', labels[i]); } });
        },
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
