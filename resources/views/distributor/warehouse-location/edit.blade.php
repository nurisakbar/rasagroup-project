@extends('layouts.shop')

@section('title', $warehouse->exists ? 'Ubah Gudang' : 'Tambah Gudang')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('distributor.profile') }}">Profil Distributor</a>
            <span></span> <a href="{{ route('distributor.warehouse-location.index') }}">Lokasi Gudang</a>
            <span></span> {{ $warehouse->exists ? 'Ubah' : 'Tambah' }}
        </div>
    </div>
</div>

<div class="page-content account pt-50 pb-80" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-lg-8">
                <div class="card border-0 border-radius-20 overflow-hidden" style="background-color: #ffffff; border: 1px solid #E3D6C8 !important; box-shadow: 0 10px 30px rgba(106, 27, 27, 0.12);">
                    <div class="card-header bg-white border-bottom p-30 wl-card-header">
                        <h3 class="mb-5 wl-title" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">{{ $warehouse->exists ? 'Ubah Gudang' : 'Tambah Gudang' }}</h3>
                        <p class="mb-0 text-muted font-sm">Lokasi ini dipakai sebagai sumber pengiriman dan titik ambil barang oleh subdist Anda. Perubahan langsung berlaku. Alamat tujuan pengiriman Anda tetap diatur di menu Alamat.</p>
                        <a href="{{ route('distributor.warehouse-location.index') }}" class="font-sm" style="color:#6A1B1B;font-weight:700;">&larr; Kembali ke daftar gudang</a>
                    </div>
                    <div class="card-body p-30 wl-card-body">
                        @if (session('success'))
                            <div class="alert alert-success border-0 border-radius-12 mb-25">{{ session('success') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 border-radius-12 mb-25">
                                <ul class="mb-0 font-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ $warehouse->exists ? route('distributor.warehouse-location.update', $warehouse->id) : route('distributor.warehouse-location.store') }}" method="POST" id="wlForm">
                            @csrf
                            @if($warehouse->exists) @method('PUT') @endif

                            <div class="form-group mb-20">
                                <label class="wl-label">Nama Gudang</label>
                                @if($isPrimary)
                                    <input type="text" class="form-control wl-input" value="{{ $warehouse->name }}" disabled>
                                    <small class="text-muted">Nama gudang utama diatur oleh admin.</small>
                                @else
                                    <input type="text" class="form-control wl-input" name="name" value="{{ old('name', $warehouse->name) }}" required placeholder="Contoh: Gudang Cabang Bekasi">
                                @endif
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="wl-label">Provinsi</label>
                                    <select class="form-control wl-input" id="province_id" name="province_id" required>
                                        <option value="">Pilih Provinsi</option>
                                        @foreach ($provinces as $province)
                                            <option value="{{ $province['id'] }}" {{ (string) old('province_id', $warehouse->province_id) === (string) $province['id'] ? 'selected' : '' }}>{{ $province['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="wl-label">Kabupaten/Kota</label>
                                    <select class="form-control wl-input" id="regency_id" name="regency_id" required disabled><option value="">Pilih Kabupaten/Kota</option></select>
                                </div>
                                <div class="col-md-6">
                                    <label class="wl-label">Kecamatan</label>
                                    <select class="form-control wl-input" id="district_id" name="district_id" required disabled><option value="">Pilih Kecamatan</option></select>
                                </div>
                                <div class="col-md-6">
                                    <label class="wl-label">Kelurahan/Desa <span class="text-muted">(opsional)</span></label>
                                    <select class="form-control wl-input" id="village_id" name="village_id" disabled><option value="">Pilih Desa</option></select>
                                </div>
                                <div class="col-12">
                                    <label class="wl-label">Alamat Lengkap</label>
                                    <textarea class="form-control wl-input" name="address" rows="3" required placeholder="Nama jalan, nomor, patokan">{{ old('address', $warehouse->address) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="wl-label">Kode Pos</label>
                                    <input type="text" class="form-control wl-input" name="postal_code" value="{{ old('postal_code', $warehouse->postal_code) }}" placeholder="12345">
                                </div>
                                <div class="col-md-6">
                                    <label class="wl-label">Telepon Gudang</label>
                                    <input type="text" class="form-control wl-input" name="phone" value="{{ old('phone', $warehouse->phone) }}" required placeholder="081234567890">
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="wl-label">Koordinat Lokasi <span class="text-muted">(opsional, membantu subdist menemukan gudang)</span></label>
                                    <div class="row g-3">
                                        <div class="col-md-5"><input type="text" class="form-control wl-input" id="latitude" name="latitude" value="{{ old('latitude', $warehouse->latitude) }}" placeholder="Latitude"></div>
                                        <div class="col-md-5"><input type="text" class="form-control wl-input" id="longitude" name="longitude" value="{{ old('longitude', $warehouse->longitude) }}" placeholder="Longitude"></div>
                                        <div class="col-md-2"><button type="button" class="btn w-100 wl-btn-outline" id="btnLocate" title="Pakai lokasi saat ini"><i class="fi-rs-marker"></i></button></div>
                                    </div>
                                    <small class="text-muted d-block mt-2" id="locStatus"></small>
                                </div>
                            </div>

                            <button type="submit" class="btn w-100 mt-30 wl-submit" id="wlSubmit">{{ $warehouse->exists ? 'Simpan Perubahan' : 'Tambah Gudang' }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .wl-title { font-size: clamp(20px, 5.5vw, 28px); line-height: 1.2; }
    .wl-label { font-family: 'Fira Sans', sans-serif; font-weight: 600; color: #253D4E; font-size: 14px; margin-bottom: 8px; display: block; }
    .wl-input { background-color: #F8F9FA !important; border: 1.5px solid #ECECEC !important; border-radius: 12px !important; padding: 12px 16px !important; min-height: 52px; }
    textarea.wl-input { min-height: 90px; }
    .wl-input:focus { border-color: #6A1B1B !important; background-color: #fff !important; box-shadow: 0 0 0 4px rgba(106, 27, 27, 0.05) !important; }
    .wl-btn-outline { background: #FAF6F1 !important; color: #6A1B1B !important; border: 1.5px solid #D9CFC4 !important; border-radius: 12px !important; height: 52px; }
    .wl-submit { background-color: #6A1B1B !important; color: #fff !important; border: none !important; border-radius: 12px !important; height: 56px; font-weight: 700; font-size: 16px; box-shadow: 0 8px 20px rgba(106, 27, 27, 0.25); }
    @media (max-width: 575px) { .wl-card-header { padding: 18px !important; } .wl-card-body { padding: 20px 16px !important; } }
</style>
@endsection

@push('scripts')
<script>
(function () {
    var cur = {
        province: @json((string) old('province_id', $warehouse->province_id)),
        regency: @json((string) old('regency_id', $warehouse->regency_id)),
        district: @json((string) old('district_id', $warehouse->district_id)),
        village: @json((string) old('village_id', $warehouse->village_id)),
    };
    var urls = {
        regencies: '{{ route("buyer.addresses.get-regencies") }}',
        districts: '{{ route("buyer.addresses.get-districts") }}',
        villages: '{{ route("buyer.addresses.get-villages") }}',
    };
    function fill($sel, data, placeholder, selected) {
        $sel.empty().append($('<option>', { value: '', text: placeholder }));
        (data || []).forEach(function (i) {
            $sel.append($('<option>', { value: i.id, text: i.name, selected: String(i.id) === String(selected) }));
        });
        $sel.prop('disabled', !(data && data.length));
    }
    function load(url, params, $sel, placeholder, selected) {
        $sel.prop('disabled', true);
        return $.getJSON(url, params).done(function (d) { fill($sel, d, placeholder, selected); })
            .fail(function () { fill($sel, [], 'Gagal memuat data', ''); });
    }
    var $prov = $('#province_id'), $reg = $('#regency_id'), $dist = $('#district_id'), $vil = $('#village_id');
    $prov.on('change', function () {
        fill($reg, [], 'Pilih Kabupaten/Kota'); fill($dist, [], 'Pilih Kecamatan'); fill($vil, [], 'Pilih Desa');
        if (this.value) load(urls.regencies, { province_id: this.value }, $reg, 'Pilih Kabupaten/Kota', '');
    });
    $reg.on('change', function () {
        fill($dist, [], 'Pilih Kecamatan'); fill($vil, [], 'Pilih Desa');
        if (this.value) load(urls.districts, { regency_id: this.value }, $dist, 'Pilih Kecamatan', '');
    });
    $dist.on('change', function () {
        fill($vil, [], 'Pilih Desa');
        if (this.value) load(urls.villages, { district_id: this.value }, $vil, 'Pilih Desa', '');
    });
    // Muat nilai tersimpan secara berurutan
    if (cur.province) {
        load(urls.regencies, { province_id: cur.province }, $reg, 'Pilih Kabupaten/Kota', cur.regency).always(function () {
            if (cur.regency) load(urls.districts, { regency_id: cur.regency }, $dist, 'Pilih Kecamatan', cur.district).always(function () {
                if (cur.district) load(urls.villages, { district_id: cur.district }, $vil, 'Pilih Desa', cur.village);
            });
        });
    }
    $('#btnLocate').on('click', function () {
        var $s = $('#locStatus');
        if (!navigator.geolocation) { $s.text('Peramban tidak mendukung geolokasi.'); return; }
        $s.text('Mengambil lokasi...');
        navigator.geolocation.getCurrentPosition(function (p) {
            $('#latitude').val(p.coords.latitude.toFixed(7)); $('#longitude').val(p.coords.longitude.toFixed(7));
            $s.text('Koordinat terisi dari lokasi Anda saat ini.');
        }, function () { $s.text('Gagal mengambil lokasi. Isi koordinat secara manual.'); });
    });
    $('#wlForm').on('submit', function () { $('#wlSubmit').prop('disabled', true).text('Menyimpan...'); });
})();
</script>
@endpush
