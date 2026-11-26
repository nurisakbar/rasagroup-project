@extends('layouts.admin')

@section('title', 'Dokumentasi API Penjualan Distributor')
@section('page-title', 'Dokumentasi API')
@section('page-description', 'Panduan bagi tim IT distributor untuk mengirim data penjualan ke RasaConnect')

@section('breadcrumb')
    <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route('admin.reports.distributor-sales.index') }}">Penjualan Distributor</a></li>
    <li class="active">Dokumentasi API</li>
@endsection

@push('styles')
<style>
    .api-docs { max-width: 920px; font-size: 14px; line-height: 1.6; }
    .api-docs h1 { font-size: 24px; margin-top: 0; }
    .api-docs h2 { font-size: 19px; margin-top: 28px; padding-bottom: 6px; border-bottom: 1px solid #e5e5e5; }
    .api-docs pre { background: #272822; color: #f8f8f2; padding: 12px 14px; border-radius: 4px; overflow-x: auto; }
    .api-docs code { color: #c7254e; background: #f9f2f4; padding: 1px 4px; border-radius: 3px; }
    .api-docs pre code { color: inherit; background: transparent; padding: 0; }
    .api-docs table { width: 100%; margin: 12px 0; border-collapse: collapse; }
    .api-docs th, .api-docs td { border: 1px solid #ddd; padding: 6px 10px; vertical-align: top; }
    .api-docs th { background: #f4f4f4; }
</style>
@endpush

@section('content')
    <div class="box box-default">
        <div class="box-header with-border">
            <a href="{{ route('admin.reports.distributor-sales.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Kembali ke laporan</a>
            <button type="button" class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Cetak</button>
        </div>
        <div class="box-body api-docs">{!! $html !!}</div>
    </div>
@endsection
