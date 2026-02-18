@extends('layouts.admin')

@section('title', 'Hub')
@section('page-title', 'Manajemen Hub')
@section('page-description', 'Kelola data hub dan stock produk')

@section('breadcrumb')
    <li class="active">Hub</li>
@endsection



@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Hub</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah Hub
                        </a>
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
                                <th>Lokasi</th>
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
        table.draw();
    });


});
</script>
@endpush
