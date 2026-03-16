@extends('layouts.admin')

@section('title', 'Edit Potongan Harga')
@section('page-title', 'Edit Potongan Harga')

@section('breadcrumb')
    <li><a href="{{ route('admin.discount-tiers.index') }}">Potongan Harga</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Data Potongan Harga Edit</h3>
            </div>
            <form action="{{ route('admin.discount-tiers.update', $discountTier) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="box-body">
                    <div class="form-group">
                        <label for="min_quantity">Minimal Item Belanja</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="min_quantity" id="min_quantity" value="{{ $discountTier->min_quantity }}" required>
                            <span class="input-group-addon">Item</span>
                        </div>
                        <p class="help-block">Masukkan jumlah minimal item belanja untuk mendapatkan diskon ini.</p>
                    </div>
                    <div class="form-group">
                        <label for="discount_percent">Potongan (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" name="discount_percent" id="discount_percent" value="{{ $discountTier->discount_percent }}" required>
                            <span class="input-group-addon">%</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_active" value="1" {{ $discountTier->is_active ? 'checked' : '' }}> Aktif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="{{ route('admin.discount-tiers.index') }}" class="btn btn-default">Kembali</a>
                    <button type="submit" class="btn btn-primary pull-right">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
