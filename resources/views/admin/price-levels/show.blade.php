@extends('layouts.admin')

@section('title', 'Detail Level Harga')
@section('page-title', 'Detail Level Harga')
@section('page-description', 'Detail informasi level harga')

@section('breadcrumb')
    <li><a href="{{ route('admin.price-levels.index') }}">Level Harga</a></li>
    <li class="active">Detail</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Informasi Level Harga</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.price-levels.edit', $priceLevel) }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.price-levels.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Nama Level</th>
                            <td><strong>{{ $priceLevel->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td>{{ $priceLevel->description ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Persentase Diskon</th>
                            <td><strong>{{ number_format($priceLevel->discount_percentage, 2, ',', '.') }}%</strong></td>
                        </tr>
                        <tr>
                            <th>Urutan</th>
                            <td>{{ $priceLevel->order }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($priceLevel->is_active)
                                    <span class="label label-success">Aktif</span>
                                @else
                                    <span class="label label-danger">Tidak Aktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Jumlah Produk</th>
                            <td>{{ $priceLevel->products()->count() }} produk</td>
                        </tr>
                        <tr>
                            <th>Dibuat</th>
                            <td>{{ $priceLevel->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diupdate</th>
                            <td>{{ $priceLevel->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection







