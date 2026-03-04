@extends('layouts.admin')

@section('title', 'Tambah Potongan Harga')
@section('page-title', 'Tambah Potongan Harga')

@section('breadcrumb')
    <li><a href="{{ route('admin.discount-tiers.index') }}">Potongan Harga</a></li>
    <li class="active">Tambah</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Data Potongan Harga Baru</h3>
            </div>
            <form action="{{ route('admin.discount-tiers.store') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label for="min_purchase_display">Minimal Pembelian (Subtotal)</label>
                        <div class="input-group">
                            <span class="input-group-addon">Rp</span>
                            <input type="text" class="form-control" id="min_purchase_display" required placeholder="Contoh: 1.000.000">
                            <input type="hidden" name="min_purchase" id="min_purchase">
                        </div>
                        <p class="help-block">Gunakan format angka tanpa titik/koma untuk input manual, titik akan muncul otomatis.</p>
                    </div>
                    <div class="form-group">
                        <label for="discount_percent">Potongan (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" name="discount_percent" id="discount_percent" required placeholder="Contoh: 5">
                            <span class="input-group-addon">%</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_active" value="1" checked> Aktif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="{{ route('admin.discount-tiers.index') }}" class="btn btn-default">Kembali</a>
                    <button type="submit" class="btn btn-primary pull-right">Simpan Potongan Harga</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const displayInput = $('#min_purchase_display');
    const hiddenInput = $('#min_purchase');

    function formatNumber(n) {
        return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    displayInput.on('input', function() {
        let val = $(this).val();
        let rawVal = val.replace(/\./g, '');
        
        // Update hidden input with raw number
        hiddenInput.val(rawVal);
        
        // Update display with formatted number
        $(this).val(formatNumber(rawVal));
    });
});
</script>
@endpush
