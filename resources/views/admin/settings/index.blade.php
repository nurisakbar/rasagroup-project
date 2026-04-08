@extends('layouts.admin')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')
@section('page-description', 'Kelola pengaturan sistem')

@section('breadcrumb')
    <li class="active">Pengaturan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">

        <!-- Affiliator Point Rate Setting -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cog"></i> Point Rate Affiliator</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.settings.update-driippreneur-point-rate') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="point_rate">Point per Item</label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="point_rate" 
                                   name="point_rate" 
                                   value="{{ old('point_rate', $driippreneurPointRate) }}" 
                                   min="0" 
                                   step="1" 
                                   required>
                            <span class="input-group-addon">point/item</span>
                        </div>
                        <p class="help-block">
                            Point yang akan diberikan kepada Affiliator untuk setiap item yang dibeli saat pesanan selesai.
                            <br>
                            <strong>Contoh:</strong> Jika di-set 1000 point/item, dan Affiliator membeli 5 item, maka akan mendapat 5000 point.
                        </p>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Expedition Setting -->
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-truck"></i> Pengaturan Ekspedisi</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.settings.update-expeditions') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Pilih Ekspedisi Aktif</label>
                        <div class="row">
                            @foreach ($expeditions as $expedition)
                                <div class="col-md-4">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="expeditions[]" value="{{ $expedition->id }}" 
                                                {{ $expedition->is_active ? 'checked' : '' }}>
                                            {{ $expedition->name }} ({{ strtoupper($expedition->code) }})
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="help-block">Ekspedisi yang dicentang akan muncul di pilihan pengiriman pada halaman checkout.</p>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-save"></i> Simpan Pengaturan Ekspedisi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // Any remaining scripts can go here
</script>
@endpush
@endsection








