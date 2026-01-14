@extends('layouts.distributor')

@section('title', 'Riwayat Stock')
@section('page-title', 'Riwayat Stock')
@section('page-description', 'Riwayat perubahan stock produk')

@section('breadcrumb')
    <li><a href="{{ route('distributor.stock.index') }}">Kelola Stock</a></li>
    <li class="active">Riwayat Stock</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
<style>
    .dataTables_filter { display: none; }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Product Info -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cube"></i> Informasi Produk</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-2">
                            @if($stock->product && $stock->product->image)
                                <img src="{{ asset('storage/' . $stock->product->image) }}" alt="{{ $stock->product->name }}" class="img-responsive" style="border-radius: 5px;">
                            @else
                                <div style="width: 100%; height: 150px; background: #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-10">
                            <table class="table table-condensed">
                                <tr>
                                    <th style="width: 150px;">Nama Produk</th>
                                    <td><strong>{{ $stock->product->name ?? 'Produk tidak ditemukan' }}</strong></td>
                                </tr>
                                @if($stock->product && $stock->product->code)
                                <tr>
                                    <th>Kode Produk</th>
                                    <td>{{ $stock->product->code }}</td>
                                </tr>
                                @endif
                                @if($stock->product && $stock->product->brand)
                                <tr>
                                    <th>Brand</th>
                                    <td><span class="label label-primary">{{ $stock->product->brand->name }}</span></td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Stock Saat Ini</th>
                                    <td>
                                        @if($stock->stock <= 10)
                                            <span class="badge bg-red" style="font-size: 14px;">{{ number_format($stock->stock) }}</span>
                                        @elseif($stock->stock <= 50)
                                            <span class="badge bg-yellow" style="font-size: 14px;">{{ number_format($stock->stock) }}</span>
                                        @else
                                            <span class="badge bg-green" style="font-size: 14px;">{{ number_format($stock->stock) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Warehouse</th>
                                    <td><strong>{{ $stock->warehouse->name }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-history"></i> Riwayat Perubahan Stock</h3>
                </div>
                <div class="box-body">
                    <table id="history-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Tanggal & Waktu</th>
                                <th>Order</th>
                                <th>Dilakukan Oleh</th>
                                <th width="200px">Perubahan Stock</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('distributor.stock.index') }}" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Kembali ke Daftar Stock
            </a>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#history-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('distributor.stock.history', $stock) }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'created_at_formatted', name: 'created_at' },
            { data: 'order_info', name: 'order.order_number', orderable: false },
            { data: 'user_info', name: 'user.name', orderable: false },
            { data: 'stock_change', name: 'stock_after', orderable: false },
            { data: 'notes_display', name: 'notes', orderable: false },
        ],
        order: [[1, 'desc']],
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
            emptyTable: "Tidak ada riwayat stock",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });
});
</script>
@endpush

