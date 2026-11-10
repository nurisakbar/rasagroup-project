@extends('layouts.admin')

@section('title', 'Penjualan Distributor')
@section('page-title', 'Penjualan Distributor')
@section('page-description', 'Penjualan distributor dari sistem eksternal mereka (hanya pencatatan)')

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">Penjualan Distributor</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
@endpush

@php
    $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

@section('content')
    <div class="box box-default">
        <div class="box-body">
            <form method="GET" action="{{ route('admin.reports.distributor-sales.index') }}" id="filter-form">
                <div class="row">
                    <div class="col-md-2 col-sm-6">
                        <label for="start_date">Dari tanggal</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $filters['start_date'] }}">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label for="end_date">Sampai tanggal</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $filters['end_date'] }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="distributor_id">Distributor</label>
                        <select class="form-control" id="distributor_id" name="distributor_id">
                            <option value="">Semua distributor</option>
                            @foreach($distributors as $distributor)
                                <option value="{{ $distributor->id }}" {{ $filters['distributor_id'] === $distributor->id ? 'selected' : '' }}>{{ $distributor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="province_id">Provinsi</label>
                        <select class="form-control" id="province_id" name="province_id">
                            <option value="">Semua provinsi</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" {{ (string) $filters['province_id'] === (string) $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-12" style="margin-top: 25px;">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Terapkan</button>
                        <a class="btn btn-success" href="{{ route('admin.reports.distributor-sales.export', $filters) }}"><i class="fa fa-file-excel-o"></i> Excel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-green"><div class="inner"><h3 style="font-size: 26px;">{{ $rupiah($summary['total_sales']) }}</h3><p>Total penjualan</p></div></div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-aqua"><div class="inner"><h3 style="font-size: 26px;">{{ number_format($summary['transactions'], 0, ',', '.') }}</h3><p>Transaksi</p></div></div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-yellow"><div class="inner"><h3 style="font-size: 26px;">{{ number_format($summary['distributors'], 0, ',', '.') }}</h3><p>Distributor melapor</p></div></div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-red"><div class="inner"><h3 style="font-size: 26px;">{{ number_format($summary['cancelled'], 0, ',', '.') }}</h3><p>Transaksi dibatalkan</p></div></div>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Penjualan per Distributor</h3>
            <p class="text-muted" style="margin: 5px 0 0;">Pembelian ke Rasa ditampilkan sebagai pembanding. Jangan dijumlahkan dengan penjualan, karena barang yang sama akan terhitung dua kali.</p>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Distributor</th><th>Kode</th><th class="text-right">Transaksi</th><th class="text-right">Penjualan</th><th class="text-right">Pembelian ke Rasa</th></tr></thead>
                <tbody>
                    @forelse($distributorRows as $row)
                        <tr>
                            <td>{{ $row->name }}</td>
                            <td>{{ $row->code ?: '-' }}</td>
                            <td class="text-right">{{ number_format($row->transactions, 0, ',', '.') }}</td>
                            <td class="text-right">{{ $rupiah($row->total_sales) }}</td>
                            <td class="text-right">{{ $rupiah($row->total_purchases) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">Belum ada penjualan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title">Produk Terlaris</h3></div>
                <div class="box-body table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Produk</th><th class="text-right">Qty</th><th class="text-right">Penjualan</th></tr></thead>
                        <tbody>
                            @forelse($topProducts as $product)
                                <tr>
                                    <td>{{ $product->name }}<br><small class="text-muted">{{ $product->code }}</small></td>
                                    <td class="text-right">{{ rtrim(rtrim(number_format((float) $product->quantity, 2, ',', '.'), '0'), ',') }} {{ $product->uom }}</td>
                                    <td class="text-right">{{ $rupiah($product->total_sales) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title">Tren Harian</h3></div>
                <div class="box-body table-responsive" style="max-height: 360px; overflow-y: auto;">
                    <table class="table table-striped">
                        <thead><tr><th>Tanggal</th><th class="text-right">Transaksi</th><th class="text-right">Penjualan</th></tr></thead>
                        <tbody>
                            @forelse($trend as $day)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($day->sold_date)->format('d M Y') }}</td>
                                    <td class="text-right">{{ number_format($day->transactions, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ $rupiah($day->total_sales) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($unmappedProducts->isNotEmpty())
    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title">Kode Produk Belum Dikenali</h3>
            <p class="text-muted" style="margin: 5px 0 0;">Nilainya tetap masuk total penjualan, tetapi tidak muncul di Produk Terlaris.</p>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Kode</th><th>Nama dari distributor</th><th class="text-right">Penjualan</th></tr></thead>
                <tbody>
                    @foreach($unmappedProducts as $product)
                        <tr>
                            <td>{{ $product->external_product_code }}</td>
                            <td>{{ $product->product_name ?: '-' }}</td>
                            <td class="text-right">{{ $rupiah($product->total_sales) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">Daftar Transaksi</h3></div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-hover" id="sales-table" style="width: 100%;">
                <thead><tr><th>Tanggal Jual</th><th>No. Order</th><th>Distributor</th><th>Pelanggan</th><th>Total</th><th>Status</th></tr></thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script>
    $(function () {
        $('#sales-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: @json(route('admin.reports.distributor-sales.datatable')),
                data: function (d) {
                    d.start_date = @json($filters['start_date']);
                    d.end_date = @json($filters['end_date']);
                    d.distributor_id = @json($filters['distributor_id']);
                    d.province_id = @json($filters['province_id']);
                }
            },
            columns: [
                {data: 'sold_at', name: 'sold_at'},
                {data: 'external_order_number', name: 'external_order_number'},
                {data: 'distributor_name', name: 'distributor.name', orderable: false, searchable: false},
                {data: 'customer_name', name: 'customer_name'},
                {data: 'total', name: 'total'},
                {data: 'status', name: 'status'}
            ],
            order: [[0, 'desc']]
        });
    });
</script>
@endpush
