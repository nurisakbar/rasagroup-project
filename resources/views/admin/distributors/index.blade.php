@extends('layouts.admin')

@section('title', 'Distributor')
@section('page-title', 'Manajemen Distributor')
@section('page-description', 'Kelola data Distributor')

@section('breadcrumb')
    <li class="active">Distributor</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Distributor</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.distributors.applications') }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-clock-o"></i> Pengajuan
                            @if($pendingCount > 0)
                                <span class="badge bg-red">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.distributors.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah Manual
                        </a>
                        <button type="button" id="btn-sync-qad" class="btn btn-info btn-sm">
                            <i class="fa fa-refresh"></i> Sync QAD Customers
                        </button>
                    </div>
                </div>
                <!-- Filter -->
                <div class="box-body" style="border-bottom: 1px solid #f4f4f4;">
                    <form id="filter-form" class="form-inline">
                        <div class="form-group">
                            <label for="filter-province">Provinsi:</label>
                            <select name="province_id" id="filter-province" class="form-control">
                                <option value="">Semua Provinsi</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="filter-regency">Kabupaten/Kota:</label>
                            <select name="regency_id" id="filter-regency" class="form-control" disabled>
                                <option value="">Semua Kabupaten/Kota</option>
                            </select>
                        </div>
                        <button type="button" id="btn-filter" class="btn btn-default">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <button type="button" id="btn-reset" class="btn btn-default">
                            <i class="fa fa-times"></i> Reset
                        </button>
                    </form>
                </div>
                <!-- /.box-header -->
                <div class="box-body table-responsive">
                    <table id="distributors-table" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Distributor</th>
                                <th>Email</th>
                                <th>No. HP</th>
                                <th>Hub</th>
                                <th>Lokasi</th>
                                <th>Terdaftar</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </div>
    <!-- Sync Progress Modal -->
    <div class="modal fade" id="syncProgressModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Menyinkronkan Data...</h4>
                </div>
                <div class="modal-body text-center">
                    <p>Mohon tunggu, sedang menarik dan memproses data dari QAD.</p>
                    <div class="progress active">
                        <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                            <span class="sr-only">Processing...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function() {
    var table = $('#distributors-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.distributors.index') }}",
            data: function(d) {
                d.province_id = $('#filter-province').val();
                d.regency_id = $('#filter-regency').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name_info', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'phone_display', name: 'phone', orderable: false },
            { data: 'hub_info', name: 'warehouse.name', orderable: false },
            { data: 'location_info', name: 'location', orderable: false, searchable: false },
            { data: 'created_date', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']],
        language: {
            processing: '<i class="fa fa-spinner fa-spin"></i> Memuat...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            emptyTable: '<i class="fa fa-truck fa-3x"></i><br><br>Belum ada Distributor.',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Selanjutnya',
                previous: 'Sebelumnya'
            }
        }
    });

    // Province change - load regencies
    $('#filter-province').on('change', function() {
        var provinceId = $(this).val();
        var regencySelect = $('#filter-regency');
        
        regencySelect.html('<option value="">Semua Kabupaten/Kota</option>');
        
        if (provinceId) {
            regencySelect.prop('disabled', false);
            
            $.ajax({
                url: "{{ route('admin.get-regencies') }}",
                type: 'GET',
                data: { province_id: provinceId },
                success: function(data) {
                    $.each(data, function(key, regency) {
                        regencySelect.append('<option value="' + regency.id + '">' + regency.name + '</option>');
                    });
                },
                error: function(xhr) {
                    console.error('Error loading regencies:', xhr);
                }
            });
        } else {
            regencySelect.prop('disabled', true);
        }
    });

    // Filter button
    $('#btn-filter').on('click', function() {
        table.draw();
    });

    // Reset button
    $('#btn-reset').on('click', function() {
        $('#filter-province').val('');
        $('#filter-regency').html('<option value="">Semua Kabupaten/Kota</option>').prop('disabled', true);
        table.draw();
    });

    // AJAX Sync QAD
    $('#btn-sync-qad').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Mulai sinkronisasi data Customer dari QAD? Proses ini mungkin membutuhkan waktu beberapa saat.')) {
            return;
        }

        $('#syncProgressModal').modal('show');

        $.ajax({
            url: "{{ route('admin.distributors.sync-qad') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $('#syncProgressModal').modal('hide');
                if(response.success) {
                    alert(response.message);
                    table.draw();
                }
            },
            error: function(xhr) {
                $('#syncProgressModal').modal('hide');
                var msg = 'Terjadi kesalahan sistem.';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert('Gagal: ' + msg);
            }
        });
    });
});
</script>
@endpush
