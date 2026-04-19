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
