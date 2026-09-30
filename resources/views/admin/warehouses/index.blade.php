@extends('layouts.admin')

@section('title', 'Hub')
@section('page-title', 'Manajemen Hub')
@section('page-description', 'Kelola data hub dan stock produk')

@section('breadcrumb')
    <li class="active">Hub</li>
@endsection

@push('styles')
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 40px;
  height: 20px;
  margin-bottom: 0;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}
.slider:before {
  position: absolute;
  content: "";
  height: 14px;
  width: 14px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}
input:checked + .slider {
  background-color: #2196F3;
}
input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}
input:checked + .slider:before {
  -webkit-transform: translateX(20px);
  -ms-transform: translateX(20px);
  transform: translateX(20px);
}
.slider.round {
  border-radius: 20px;
}
.slider.round:before {
  border-radius: 50%;
}
</style>
@endpush



@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Hub</h3>
                    <div class="box-tools" style="display: flex; gap: 15px; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="font-size: 13px;">Tampilkan Hanya Gudang Aktif</span>
                            <label class="switch">
                                <input type="checkbox" id="toggle-active-only" checked>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div style="display: flex; gap: 5px;">
                            @include('admin.partials.sync-qad-jubelio')
                            <button type="button" class="btn btn-success btn-sm" id="btn-sync-qad-batches">
                                <i class="fa fa-refresh"></i> Sinkronisasi Batch QAD
                            </button>
                            @if(app()->environment('local'))
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteAllWarehouses()" title="Hanya tersedia di APP_ENV=local">
                                    <i class="fa fa-trash"></i> Hapus Semua
                                </button>
                                <form id="delete-all-warehouses-form" action="{{ route('admin.warehouses.destroy-all') }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="confirm" id="delete-all-warehouses-confirm-input" value="">
                                </form>
                            @endif
                        </div>
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
                        <div class="form-group">
                            <label for="filter-status">Status:</label>
                            <select name="status" id="filter-status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="filter-sync-source">Sumber:</label>
                            <select name="sync_source" id="filter-sync-source" class="form-control">
                                <option value="">Semua Sumber</option>
                                <option value="jubelio">Jubelio</option>
                                <option value="qad">QAD</option>
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
                    <table id="warehouses-table" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Hub</th>
                                <th>Alamat</th>
                                <th>Telepon</th>
                                <th>Jenis Produk</th>
                                <th>Total Stock</th>
                                <th>Status</th>
                                <th width="120">Action</th>
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

    <div class="modal fade" id="qadBatchModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Sinkronisasi Batch QAD</h4>
                </div>
                <div class="modal-body">
                    <p id="qadBatchMessage" class="text-muted">Menyiapkan lokasi yang sudah di-mapping...</p>
                    <div class="progress" style="height: 22px; margin-bottom: 8px;">
                        <div id="qadBatchBar" class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width: 0%; min-width: 2em;">
                            <span id="qadBatchPercent">0%</span>
                        </div>
                    </div>
                    <small id="qadBatchDetail" class="text-muted"></small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="qadBatchClose" data-dismiss="modal" style="display: none;">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="qadLocationsModal" tabindex="-1" role="dialog" aria-labelledby="qadLocationsModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="qadLocationsModalLabel">Daftar Gudang QAD</h4>
                </div>
                <div class="modal-body">
                    <div id="qad-locations-loading" class="text-center" style="display: none; padding: 20px;">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Memuat data dari QAD...</p>
                    </div>
                    <div id="qad-locations-error" class="alert alert-danger" style="display: none;"></div>
                    <div id="qad-locations-content" style="display: none;">
                        <table class="table table-bordered table-striped" id="qad-locations-table">
                            <thead>
                                <tr>
                                    <th>Kode Lokasi (Location)</th>
                                    <th>Deskripsi (Description)</th>
                                    <th>Status (Tstatus)</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(function() {
    var table = $('#warehouses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.warehouses.index') }}",
            data: function(d) {
                d.province_id = $('#filter-province').val();
                d.regency_id = $('#filter-regency').val();
                d.status = $('#filter-status').val();
                d.sync_source = $('#filter-sync-source').val();
                d.active_only = $('#toggle-active-only').is(':checked') ? 1 : 0;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name_info', name: 'name' },
            { data: 'location_info', name: 'location', orderable: false, searchable: false },
            { data: 'phone_display', name: 'phone', orderable: false },
            { data: 'products_info', name: 'products_count', orderable: false, searchable: false },
            { data: 'stock_info', name: 'stocks_sum_stock', orderable: false, searchable: false },
            { data: 'status_info', name: 'is_active', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        language: {
            processing: '<i class="fa fa-spinner fa-spin"></i> Memuat...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            emptyTable: '<i class="fa fa-building fa-3x"></i><br><br>Belum ada data Hub.',
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
        $('#filter-status').val('');
        $('#filter-sync-source').val('');
        $('#toggle-active-only').prop('checked', true);
        table.draw();
    });

    // Toggle active only
    $('#toggle-active-only').on('change', function() {
        table.draw();
    });

    var qadBatchRunning = false;

    function setQadBatchProgress(percent, message, detail) {
        var rounded = Math.max(0, Math.min(100, Math.round(percent)));
        $('#qadBatchBar').css('width', rounded + '%');
        $('#qadBatchPercent').text(rounded + '%');
        if (message) {
            $('#qadBatchMessage').text(message);
        }
        $('#qadBatchDetail').text(detail || '');
    }

    function finishQadBatch(message, detail, failed) {
        qadBatchRunning = false;
        $('#qadBatchBar').removeClass('active progress-bar-striped');
        if (failed) {
            $('#qadBatchBar').removeClass('progress-bar-success').addClass('progress-bar-warning');
        }
        setQadBatchProgress(100, message, detail);
        $('#qadBatchClose').show();
    }

    function syncNextQadBatch(locations, index, totals) {
        if (index >= locations.length) {
            var detail = totals.batches + ' batch dari ' + locations.length + ' lokasi.';
            if (totals.failed > 0) {
                detail += ' ' + totals.failed + ' lokasi gagal.';
                finishQadBatch('Sinkronisasi selesai dengan beberapa kegagalan.', detail, true);
            } else {
                finishQadBatch('Sinkronisasi batch QAD selesai.', detail, false);
            }
            return;
        }

        var location = locations[index];
        var current = index + 1;
        setQadBatchProgress(
            (index / locations.length) * 100,
            'Menarik batch ' + location.name + ' (' + current + '/' + locations.length + ')',
            location.code
        );

        $.ajax({
            url: @json(route('admin.warehouses.sync-qad-batches')),
            method: 'POST',
            timeout: 180000,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            data: { location_code: location.code },
            success: function(response) {
                totals.batches += parseInt(response.synced || 0, 10);
                totals.products += parseInt(response.products || 0, 10);
                setQadBatchProgress(
                    (current / locations.length) * 100,
                    'Selesai ' + location.name + ' (' + current + '/' + locations.length + ')',
                    response.message || ''
                );
                syncNextQadBatch(locations, current, totals);
            },
            error: function(xhr) {
                totals.failed += 1;
                var message = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : ('Gagal menarik lokasi ' + location.code);
                setQadBatchProgress(
                    (current / locations.length) * 100,
                    message + ' (' + current + '/' + locations.length + ')',
                    'Lanjut ke lokasi berikutnya.'
                );
                syncNextQadBatch(locations, current, totals);
            }
        });
    }

    $('#btn-sync-qad-batches').on('click', function() {
        if (qadBatchRunning) {
            return;
        }
        qadBatchRunning = true;
        $('#qadBatchClose').hide();
        $('#qadBatchBar')
            .removeClass('progress-bar-warning')
            .addClass('progress-bar-success progress-bar-striped active')
            .css('width', '0%');
        $('#qadBatchPercent').text('0%');
        $('#qadBatchMessage').text('Menyiapkan lokasi yang sudah di-mapping...');
        $('#qadBatchDetail').text('');
        $('#qadBatchModal').modal('show');

        $.ajax({
            url: @json(route('admin.warehouses.sync-qad-batches.locations')),
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            success: function(response) {
                var locations = response.locations || [];
                if (!locations.length) {
                    finishQadBatch('Tidak ada lokasi yang sudah di-mapping.', '', false);
                    return;
                }
                syncNextQadBatch(locations, 0, { batches: 0, products: 0, failed: 0 });
            },
            error: function() {
                finishQadBatch('Gagal memuat daftar lokasi.', '', true);
            }
        });
    });

});

function confirmDeleteAllWarehouses() {
    if (!confirm('PERINGATAN: Semua hub dan data terkait akan dihapus permanen:\n- Stok gudang & riwayat stok\n- Keranjang terkait hub\n- Jadwal operasional hub\n- Akun staff hub (role warehouse)\n\nHub distributor akan kehilangan referensi hub (warehouse_id di-reset).\n\nLanjutkan?')) {
        return;
    }
    var typed = prompt('Ketik HAPUS SEMUA untuk konfirmasi:');
    if (typed !== 'HAPUS SEMUA') {
        alert('Konfirmasi dibatalkan.');
        return;
    }
    document.getElementById('delete-all-warehouses-confirm-input').value = typed;
    document.getElementById('delete-all-warehouses-form').submit();
}

function showQadLocations() {
    $('#qadLocationsModal').modal('show');
    $('#qad-locations-loading').show();
    $('#qad-locations-error').hide();
    $('#qad-locations-content').hide();
    $('#qad-locations-table tbody').empty();

    $.ajax({
        url: "{{ route('admin.warehouses.qad-locations') }}",
        type: 'GET',
        success: function(response) {
            $('#qad-locations-loading').hide();
            if (response.success) {
                var tbody = $('#qad-locations-table tbody');
                if (response.data.length > 0) {
                    $.each(response.data, function(index, loc) {
                        tbody.append(
                            '<tr>' +
                                '<td><strong>' + loc.location + '</strong></td>' +
                                '<td>' + (loc.description || '-') + '</td>' +
                                '<td>' + (loc.tstatus || '-') + '</td>' +
                            '</tr>'
                        );
                    });
                } else {
                    tbody.append('<tr><td colspan="3" class="text-center">Tidak ada data lokasi QAD ditemukan</td></tr>');
                }
                $('#qad-locations-content').show();
            } else {
                $('#qad-locations-error').text(response.message || 'Gagal memuat data').show();
            }
        },
        error: function(xhr) {
            $('#qad-locations-loading').hide();
            $('#qad-locations-error').text('Terjadi kesalahan jaringan atau server').show();
        }
    });
}
</script>
@endpush
