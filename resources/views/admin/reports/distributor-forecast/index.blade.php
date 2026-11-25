@extends('layouts.admin')

@section('title', 'Forecast Distributor')
@section('page-title', 'Forecast Distributor')
@section('page-description', 'Perkiraan kebutuhan distributor per outlet rekrutan dan produk (satuan botol/pcs)')

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">Forecast Distributor</li>
@endsection

@push('styles')
<style>
    #forecast-table { white-space: nowrap; font-size: 12px; }
    #forecast-table td.num, #forecast-table th.num { text-align: right; }
</style>
@endpush

@section('content')
    @php $months = \App\Models\DistributorForecast::MONTH_LABELS; @endphp
    <div class="box box-default">
        <div class="box-body">
            <form method="GET" action="{{ route('admin.reports.distributor-forecast.index') }}">
                <div class="row">
                    <div class="col-md-2 col-sm-4">
                        <label for="year">Tahun</label>
                        <select class="form-control" id="year" name="year">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ (int) $y === $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-8">
                        <label for="distributor_id">Distributor</label>
                        <select class="form-control" id="distributor_id" name="distributor_id">
                            <option value="">Semua distributor</option>
                            @foreach($distributors as $distributor)
                                <option value="{{ $distributor->id }}" {{ (string) $distributorId === (string) $distributor->id ? 'selected' : '' }}>{{ $distributor->name }} ({{ $distributor->distributor_sales_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-sm-12" style="padding-top: 25px;">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Tampilkan</button>
                        <a href="{{ route('admin.reports.distributor-forecast.export', ['year' => $year, 'distributor_id' => $distributorId]) }}" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export Excel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Total per bulan {{ $year }}</h3>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-condensed" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        @foreach($months as $label)
                            <th class="text-right">{{ $label }}</th>
                        @endforeach
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @php $grand = 0; @endphp
                        @foreach(array_keys($months) as $m)
                            @php $value = (int) ($totals->{'m' . $m} ?? 0); $grand += $value; @endphp
                            <td class="text-right">{{ number_format($value, 0, ',', '.') }}</td>
                        @endforeach
                        <td class="text-right"><strong>{{ number_format($grand, 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-body table-responsive">
            <table id="forecast-table" class="table table-bordered table-striped" style="width: 100%;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Distributor</th>
                        <th>Customer Name</th>
                        <th>Item Number</th>
                        <th>Description</th>
                        <th>Brand</th>
                        @foreach($months as $label)
                            <th class="num">{{ $label }}</th>
                        @endforeach
                        <th class="num">Total</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        // Pilihan distributor bisa dicari (nama atau kode distributor)
        $('#distributor_id').select2({
            theme: 'bootstrap',
            width: '100%',
            placeholder: 'Semua distributor',
            allowClear: true,
            language: { noResults: function () { return 'Distributor tidak ditemukan'; }, searching: function () { return 'Mencari...'; } }
        });
        // Menghapus pilihan (x) langsung menampilkan semua distributor
        $('#distributor_id').on('select2:clear', function () { $(this).closest('form').trigger('submit'); });

        $('#forecast-table').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: @json(route('admin.reports.distributor-forecast.index', ['year' => $year, 'distributor_id' => $distributorId])),
            columns: [
                {data: 'DT_RowIndex', searchable: false},
                {data: 'distributor'},
                {data: 'customer'},
                {data: 'item_number'},
                {data: 'description'},
                {data: 'brand'},
                @foreach(array_keys($months) as $m)
                {data: 'qty_{{ $m }}', className: 'num', searchable: false},
                @endforeach
                {data: 'total', className: 'num', searchable: false}
            ],
            language: {
                processing: "Memproses...", search: "Cari:", lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data", infoEmpty: "Belum ada data forecast",
                infoFiltered: "(disaring dari _MAX_ data)", zeroRecords: "Data tidak ditemukan", emptyTable: "Belum ada data forecast",
                paginate: {first: "Pertama", previous: "Sebelumnya", next: "Selanjutnya", last: "Terakhir"}
            }
        });
    });
</script>
@endpush
