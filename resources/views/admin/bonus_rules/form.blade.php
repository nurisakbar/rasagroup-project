@extends('layouts.admin')

@php $editing = $rule->exists; @endphp
@section('title', ($editing ? 'Ubah' : 'Tambah') . ' Aturan Bonus')
@section('page-title', ($editing ? 'Ubah' : 'Tambah') . ' Aturan Bonus')

@section('breadcrumb')
    <li><a href="{{ route('admin.bonus-rules.index') }}">Bonus Pembelian</a></li>
    <li class="active">{{ $editing ? 'Ubah' : 'Tambah' }}</li>
@endsection

@section('content')
<form method="POST" action="{{ $editing ? route('admin.bonus-rules.update', $rule) : route('admin.bonus-rules.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    @php
        $type = old('type', $rule->type);
        $unit = old('unit', $rule->unit);
        $custTypes = old('customer_types', $rule->customer_types ?? []);
        $buyOld = old('buy_products') ? \App\Models\Product::whereIn('id', old('buy_products'))->get() : ($rule->relationLoaded('buyProducts') ? $rule->buyProducts : collect());
        $giftOld = old('gift_products') ? \App\Models\Product::whereIn('id', old('gift_products'))->get() : ($rule->relationLoaded('giftProducts') ? $rule->giftProducts : collect());
    @endphp
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title">Aturan</h3></div>
                <div class="box-body">
                    <div class="form-group">
                        <label>Nama aturan</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $rule->name) }}" placeholder="Contoh: Beli 2 karton Sirup Mangga gratis 1 Pulp" required>
                    </div>
                    <div class="form-group">
                        <label>Tipe bonus</label>
                        <select name="type" id="type" class="form-control">
                            <option value="fixed_gift" {{ $type === 'fixed_gift' ? 'selected' : '' }}>Beli X dapat produk tertentu (ditentukan perusahaan)</option>
                            <option value="free_choice" {{ $type === 'free_choice' ? 'selected' : '' }}>Beli X pilih produk bebas (pembeli memilih dari daftar)</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-xs-4"><div class="form-group"><label>Beli (syarat)</label><input type="number" min="1" name="buy_qty" class="form-control" value="{{ old('buy_qty', $rule->buy_qty ?? 2) }}" required></div></div>
                        <div class="col-xs-4"><div class="form-group"><label>Dapat (hadiah)</label><input type="number" min="1" name="gift_qty" class="form-control" value="{{ old('gift_qty', $rule->gift_qty ?? 1) }}" required></div></div>
                        <div class="col-xs-4"><div class="form-group"><label>Satuan</label>
                            <select name="unit" class="form-control">
                                <option value="carton" {{ $unit === 'carton' ? 'selected' : '' }}>Karton</option>
                                <option value="pcs" {{ $unit === 'pcs' ? 'selected' : '' }}>Pcs (botol)</option>
                            </select></div></div>
                    </div>
                    <div class="checkbox"><label><input type="checkbox" name="is_multiple" id="is_multiple" value="1" {{ old('is_multiple', $rule->is_multiple) ? 'checked' : '' }}> Berlaku kelipatan (beli 4 dapat 2 untuk aturan beli 2 dapat 1)</label></div>
                    <div class="form-group" id="max-wrap">
                        <label>Batas maksimal hadiah per pesanan (opsional)</label>
                        <input type="number" min="1" name="max_gift_qty" class="form-control" value="{{ old('max_gift_qty', $rule->max_gift_qty) }}" placeholder="Kosongkan bila tanpa batas">
                    </div>
                    <div class="checkbox"><label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $rule->is_active) ? 'checked' : '' }}> Aktif</label></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title">Berlaku untuk</h3></div>
                <div class="box-body">
                    <div class="form-group">
                        <label>Jenis customer</label><br>
                        @foreach(\App\Models\BonusRule::CUSTOMER_TYPES as $k => $label)
                            <label class="checkbox-inline"><input type="checkbox" name="customer_types[]" value="{{ $k }}" {{ in_array($k, $custTypes, true) ? 'checked' : '' }}> {{ $label }}</label>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-xs-6"><div class="form-group"><label>Mulai (opsional)</label><input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $rule->starts_at?->format('Y-m-d\TH:i')) }}"></div></div>
                        <div class="col-xs-6"><div class="form-group"><label>Berakhir (opsional)</label><input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', $rule->ends_at?->format('Y-m-d\TH:i')) }}"></div></div>
                    </div>
                    <div class="form-group"><label>Catatan (opsional)</label><textarea name="notes" rows="2" class="form-control">{{ old('notes', $rule->notes) }}</textarea></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border"><h3 class="box-title">Produk syarat (yang harus dibeli)</h3></div>
                <div class="box-body">
                    <select name="buy_products[]" id="buy_products" class="form-control" multiple>
                        @foreach($buyOld as $p)<option value="{{ $p->id }}" selected>{{ $p->full_name }}</option>@endforeach
                    </select>
                    <p class="help-block">Jumlah semua produk yang dipilih dijumlahkan untuk memenuhi syarat.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border"><h3 class="box-title" id="gift-title">Produk hadiah</h3></div>
                <div class="box-body">
                    <select name="gift_products[]" id="gift_products" class="form-control" multiple>
                        @foreach($giftOld as $p)<option value="{{ $p->id }}" selected>{{ $p->full_name }}</option>@endforeach
                    </select>
                    <p class="help-block" id="gift-help"></p>
                </div>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
    <a href="{{ route('admin.bonus-rules.index') }}" class="btn btn-default">Batal</a>
</form>
@endsection

@push('scripts')
<script>
$(function () {
    function productSelect(sel, placeholder) {
        $(sel).select2({theme: 'bootstrap', width: '100%', placeholder: placeholder, minimumInputLength: 0,
            ajax: {url: @json(route('admin.bonus-rules.search-products')), dataType: 'json', delay: 250, data: function (p) { return {q: p.term || ''}; }, processResults: function (d) { return d; }}});
    }
    productSelect('#buy_products', 'Cari produk syarat…');
    productSelect('#gift_products', 'Cari produk hadiah…');

    function sync() {
        var fixed = $('#type').val() === 'fixed_gift';
        $('#gift-title').text(fixed ? 'Produk hadiah (pilih satu)' : 'Daftar produk yang boleh dipilih pembeli');
        $('#gift-help').text(fixed ? 'Tepat satu produk, otomatis masuk pesanan sebagai bonus.' : 'Pembeli memilih satu produk dari daftar ini di keranjang.');
        $('#max-wrap').toggle($('#is_multiple').is(':checked'));
    }
    $('#type, #is_multiple').on('change', sync); sync();
});
</script>
@endpush
