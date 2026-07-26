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

        <!-- General Setting -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cogs"></i> Pengaturan Umum</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.settings.update-general') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="payment_confirmation_email">Email Konfirmasi Pembayaran</label>
                        <input type="email" class="form-control" id="payment_confirmation_email" name="payment_confirmation_email" 
                            value="{{ old('payment_confirmation_email', $payment_confirmation_email) }}" 
                            placeholder="Contoh: admin@rasagroup.id">
                        <p class="help-block">Email yang akan digunakan untuk mendapatkan konfirmasi pembayaran.</p>
                    </div>

                    <div class="form-group">
                        <label for="distributor_default_hub">Hub Pengiriman Default Distributor</label>
                        <select class="form-control" id="distributor_default_hub" name="distributor_default_hub">
                            <option value="">-- Pilih Hub --</option>
                            @foreach ($hubs as $hub)
                                <option value="{{ $hub->id }}" {{ old('distributor_default_hub', $distributor_default_hub) == $hub->id ? 'selected' : '' }}>
                                    {{ $hub->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="help-block">Data hub terpilih yang akan digunakan sebagai base pengiriman barang dari orderan distributor.</p>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan Pengaturan Umum
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
