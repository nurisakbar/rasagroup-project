@extends('layouts.admin')

@section('title', 'Biaya Layanan Pembayaran')
@section('page-title', 'Biaya Layanan Pembayaran')
@section('page-description', 'Kelola pengaturan biaya tambahan untuk pembayaran')

@section('breadcrumb')
    <li class="active">Biaya Layanan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- Faspay Payment Fee Setting -->
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-money"></i> Pengaturan Biaya Layanan Faspay</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.payment-fees.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <p class="help-block">Masukkan nominal biaya tambahan (admin/layanan) untuk tiap channel pembayaran Faspay. Kosongkan atau isi 0 jika tidak ada biaya tambahan.</p>
                    
                    <div class="row">
                        @foreach([
                            'bca_va' => 'BCA Virtual Account',
                            'mandiri_va' => 'Mandiri Virtual Account',
                            'bri_va' => 'BRI Virtual Account',
                            'bni_va' => 'BNI Virtual Account',
                            'cimb_va' => 'CIMB Virtual Account',
                            'permata_va' => 'Permata Virtual Account',
                            'sinarmas_va' => 'Sinarmas Virtual Account',
                            'maybank_va' => 'Maybank Virtual Account',
                            'danamon_va' => 'Danamon Virtual Account',
                            'bsi_va' => 'BSI Virtual Account',
                            'qris' => 'QRIS',
                        ] as $key => $label)
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fee_faspay_{{ $key }}">{{ $label }}</label>
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input type="number" class="form-control" id="fee_faspay_{{ $key }}" name="fee_faspay_{{ $key }}" 
                                        value="{{ old('fee_faspay_'.$key, $faspayFees['fee_faspay_'.$key] ?? 0) }}" min="0">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Simpan Biaya Layanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
