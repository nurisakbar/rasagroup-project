@extends('layouts.admin')

@section('title', 'Penarikan Poin')
@section('page-title', 'Manajemen Penarikan Poin')
@section('page-description', 'Kelola request penarikan poin DRiiPPreneur')

@section('breadcrumb')
    <li class="active">Penarikan Poin</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
    .dataTables_filter { display: none; }
    .filter-box { margin-bottom: 0; }
    #withdrawals-table_length { margin-bottom: 15px; }
    .datepicker { z-index: 1151 !important; }
</style>
@endpush

@section('content')
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $totalPending }}</h3>
                    <p>Baru Pengajuan</p>
                </div>
                <div class="icon">
                    <i class="fa fa-clock-o"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ $totalApproved }}</h3>
                    <p>Sedang Diproses</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $totalCompleted }}</h3>
                    <p>Sudah Diproses</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $totalRejected }}</h3>
                    <p>Rejected</p>
                </div>
                <div class="icon">
                    <i class="fa fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Box -->
    <div class="box box-default filter-box">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Status</label>
                        <select id="filter-status" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <option value="pending">Baru Pengajuan</option>
                            <option value="approved">Sedang Diproses</option>
                            <option value="completed">Sudah Diproses</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <div class="input-group date">
                            <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <input type="text" id="filter-start-date" class="form-control datepicker" placeholder="dd-mm-yyyy">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <div class="input-group date">
                            <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <input type="text" id="filter-end-date" class="form-control datepicker" placeholder="dd-mm-yyyy">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" id="btn-reset" class="btn btn-default btn-block">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTables -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Request Penarikan Poin</h3>
                </div>
                <div class="box-body">
                    <table id="withdrawals-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>User</th>
                                <th>Jumlah Poin</th>
                                <th>Informasi Bank</th>
                                <th>Status</th>
                                <th>Tanggal Request</th>
                                <th>Action</th>
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
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Datepicker
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true
    });

    var table = $('#withdrawals-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.point-withdrawals.index') }}",
            data: function(d) {
                d.status = $('#filter-status').val();
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'user_info', name: 'user.name', orderable: false },
            { data: 'amount_formatted', name: 'amount' },
            { data: 'bank_info', name: 'bank_name', orderable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'requested_at_formatted', name: 'requested_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[5, 'desc']],
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
            emptyTable: "Tidak ada data tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });

    // Filter handler
    $('#filter-status, #filter-start-date, #filter-end-date').change(function() {
        table.draw();
    });

    // Reset button
    $('#btn-reset').click(function() {
        $('#filter-status').val('');
        $('#filter-start-date').val('');
        $('#filter-end-date').val('');
        table.draw();
    });

    // Handle approve action
    $(document).on('submit', 'form.approve-form', function(e) {
        e.preventDefault();
        var form = $(this);
        
        if (!confirm('Setujui request penarikan poin ini?')) {
            return false;
        }

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                table.draw();
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.success || 'Request berhasil disetujui.');
                } else {
                    alert(response.success || 'Request berhasil disetujui.');
                }
            },
            error: function(xhr) {
                var message = 'Terjadi kesalahan. Silakan coba lagi.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    message = xhr.responseJSON.error;
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            }
        });
    });

    // Handle complete action
    $(document).on('submit', 'form.complete-form', function(e) {
        e.preventDefault();
        var form = $(this);
        
        if (!confirm('Selesaikan penarikan ini? Poin akan dikurangi dari akun user.')) {
            return false;
        }

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                table.draw();
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.success || 'Penarikan berhasil diselesaikan.');
                } else {
                    alert(response.success || 'Penarikan berhasil diselesaikan.');
                }
            },
            error: function(xhr) {
                var message = 'Terjadi kesalahan. Silakan coba lagi.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    message = xhr.responseJSON.error;
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            }
        });
    });

    // Handle reject button - redirect to detail page
    $(document).on('click', '.reject-btn', function() {
        var withdrawalId = $(this).data('withdrawal-id');
        window.location.href = '{{ url("admin/point-withdrawals") }}/' + withdrawalId;
    });
});
</script>
@endpush

