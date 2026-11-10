@extends('layouts.admin')

@section('title', 'Detail Penjualan Distributor')
@section('page-title', 'Detail Penjualan Distributor')
@section('page-description', $sale->external_order_number)

@section('breadcrumb')
    <li><a href="{{ route('admin.reports.distributor-sales.index') }}">Penjualan Distributor</a></li>
    <li class="active">Detail</li>
@endsection

@php
    $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

@section('content')
    <div class="box box-primary">
        <div class="box-body">
            <table class="table">
                <tr><th style="width: 220px;">No. Order Distributor</th><td>{{ $sale->external_order_number }}</td></tr>
                <tr><th>Distributor</th><td>{{ $sale->distributor?->name ?? '-' }}</td></tr>
                <tr><th>Tanggal Jual</th><td>{{ \App\Support\Wib::format($sale->sold_at) }} WIB</td></tr>
                <tr><th>Pelanggan</th><td>{{ $sale->customer_name }}{{ $sale->customer_code ? ' (' . $sale->customer_code . ')' : '' }}</td></tr>
                <tr><th>Status</th><td>{{ $sale->status === \App\Models\DistributorExternalSale::STATUS_CANCELLED ? 'Dibatalkan' : 'Terkonfirmasi' }}</td></tr>
                <tr><th>Diterima</th><td>{{ \App\Support\Wib::format($sale->created_at) }} WIB, terakhir diperbarui {{ \App\Support\Wib::format($sale->updated_at) }} WIB</td></tr>
            </table>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">Item</h3></div>
        <div class="box-body table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Kode</th><th>Produk</th><th class="text-right">Qty</th><th class="text-right">Harga</th><th class="text-right">Subtotal</th></tr></thead>
                <tbody>
                    @foreach($sale->items as $item)
                        <tr>
                            <td>{{ $item->external_product_code }}</td>
                            <td>
                                {{ $item->product?->name ?? $item->product_name ?? '-' }}
                                @unless($item->product_id)
                                    <span class="label label-warning">Belum dikenali</span>
                                @endunless
                            </td>
                            <td class="text-right">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }} {{ $item->uom }}</td>
                            <td class="text-right">{{ $rupiah($item->unit_price) }}</td>
                            <td class="text-right">{{ $rupiah($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><th colspan="4" class="text-right">Subtotal</th><td class="text-right">{{ $rupiah($sale->subtotal) }}</td></tr>
                    <tr><th colspan="4" class="text-right">Diskon</th><td class="text-right">{{ $rupiah($sale->discount) }}</td></tr>
                    <tr><th colspan="4" class="text-right">Pajak</th><td class="text-right">{{ $rupiah($sale->tax) }}</td></tr>
                    <tr><th colspan="4" class="text-right">Total</th><th class="text-right">{{ $rupiah($sale->total) }}</th></tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
