@extends('layouts.shop')

@section('title', 'Edit Alamat')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('buyer.addresses.index') }}">Alamat</a>
            <span></span> Edit Alamat
        </div>
    </div>
</div>

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-12 m-auto">
            <div class="row">
                <div class="col-lg-4">
                    <div class="dashboard-menu shadow-sm border-radius-15 p-4 bg-white">
                        <h4 class="mb-30 border-bottom pb-2">Menu Akun</h4>
                        <ul class="nav flex-column" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('buyer.dashboard') }}"><i class="fi-rs-settings-sliders mr-10"></i>Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('buyer.orders.index') }}"><i class="fi-rs-shopping-bag mr-10"></i>Pesanan Saya</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('buyer.addresses.index') }}"><i class="fi-rs-marker mr-10"></i>Alamat Saya</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('buyer.profile') }}"><i class="fi-rs-user mr-10"></i>Detail Akun</a>
                            </li>
                            <li class="nav-item mt-20">
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-edit-address">
                                    @csrf
                                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-edit-address').submit();">
                                        <i class="fi-rs-sign-out mr-10"></i>Keluar
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="tab-content account dashboard-content pl-lg-4 pl-0">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="card border-0 shadow-sm border-radius-15 overflow-hidden">
                                <div class="card-header bg-white border-bottom p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3 class="mb-0 heading-3">Edit Alamat</h3>
                                        <a href="{{ route('buyer.addresses.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill font-sm">
                                            <i class="fi-rs-arrow-left mr-5"></i> Kembali
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body p-4 p-md-5 pt-4">
                                    @if ($errors->any())
                                        <div class="alert alert-danger alert-dismissible fade show border-0 mb-4" role="alert">
                                            <ul class="mb-0 font-sm">
                                                @foreach ($errors->all() as $error)
                                                    <li><i class="fi-rs-cross-circle mr-5"></i> {{ $error }}</li>
                                                @endforeach
                                            </ul>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif

                                    <form action="{{ route('buyer.addresses.update', $address) }}" method="POST" id="addressForm">
                                        @csrf
                                        @method('PUT')

                                        <div class="row mb-30">
                                            <div class="col-12 mb-20">
                                                <h5 class="text-brand mb-10"><i class="fi-rs-user mr-10"></i> Informasi Penerima</h5>
                                                <p class="text-muted font-xs">Perbarui informasi identitas pengiriman.</p>
                                            </div>
                                            <div class="form-group col-md-12 mb-3">
                                                <label class="form-label">Label Alamat <span class="required">*</span></label>
                                                <div class="custom_select">
                                                    <select class="form-control select-active @error('label') is-invalid @enderror" id="label" name="label" required>
                                                        <option value="">-- Pilih --</option>
                                                        <option value="Rumah" {{ old('label', $address->label) == 'Rumah' ? 'selected' : '' }}>Rumah</option>
                                                        <option value="Kantor" {{ old('label', $address->label) == 'Kantor' ? 'selected' : '' }}>Kantor</option>
                                                        <option value="Toko" {{ old('label', $address->label) == 'Toko' ? 'selected' : '' }}>Toko</option>
                                                        <option value="Lainnya" {{ old('label', $address->label) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6 mb-3">
                                                <label class="form-label">Nama Penerima <span class="required">*</span></label>
                                                <input type="text" required class="form-control @error('recipient_name') is-invalid @enderror" 
                                                       name="recipient_name" value="{{ old('recipient_name', $address->recipient_name) }}" placeholder="Nama Lengkap">
                                            </div>
                                            <div class="form-group col-md-6 mb-3">
                                                <label class="form-label">No. Telepon <span class="required">*</span></label>
                                                <input type="text" required class="form-control @error('phone') is-invalid @enderror" 
                                                       name="phone" value="{{ old('phone', $address->phone) }}" placeholder="Contoh: 081234567890">
                                            </div>
                                        </div>

                                        <div class="location-box border border-radius-15 bg-light p-4 mt-20 mb-30">
                                            <div class="row">
                                                <div class="col-12 mb-20">
                                                    <h5 class="text-brand mb-5"><i class="fi-rs-marker mr-10"></i> Informasi Lokasi</h5>
                                                    <p class="text-muted font-xs">Wilayah pengiriman untuk akurasi ongkos kirim.</p>
                                                </div>

                                                <div class="form-group col-md-6 mb-4">
                                                    <label class="form-label">Provinsi <span class="required">*</span></label>
                                                    <div class="custom_select">
                                                        <select class="form-control select-active @error('province_id') is-invalid @enderror" id="province_id" name="province_id" required>
                                                            <option value="">-- Pilih Provinsi --</option>
                                                            @foreach($provinces as $province)
                                                                <option value="{{ $province['id'] }}" {{ old('province_id', $address->province_id) == $province['id'] ? 'selected' : '' }}>
                                                                    {{ $province['name'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6 mb-4">
                                                    <label class="form-label">Kabupaten/Kota <span class="required">*</span></label>
                                                    <div class="custom_select">
                                                        <select class="form-control select-active @error('regency_id') is-invalid @enderror" id="regency_id" name="regency_id" required>
                                                            <option value="">-- Pilih Kabupaten/Kota --</option>
                                                            @foreach($regencies as $regency)
                                                                <option value="{{ $regency['id'] }}" {{ old('regency_id', $address->regency_id) == $regency['id'] ? 'selected' : '' }}>
                                                                    {{ $regency['name'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <small class="text-brand font-xs" id="regency_loading" style="display: none;">
                                                        <i class="fi-rs-refresh spin mr-5"></i> Memuat data...
                                                    </small>
                                                </div>

                                                <div class="form-group col-md-4 mb-4">
                                                    <label class="form-label">Kecamatan <span class="required">*</span></label>
                                                    <div class="custom_select">
                                                        <select class="form-control select-active @error('district_id') is-invalid @enderror" id="district_id" name="district_id" required>
                                                            <option value="">-- Pilih Kecamatan --</option>
                                                            @foreach($districts as $district)
                                                                <option value="{{ $district['id'] }}" {{ old('district_id', $address->district_id) == $district['id'] ? 'selected' : '' }}>
                                                                    {{ $district['name'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <small class="text-brand font-xs" id="district_loading" style="display: none;">
                                                        <i class="fi-rs-refresh spin mr-5"></i> Memuat data...
                                                    </small>
                                                </div>
                                                <div class="form-group col-md-4 mb-4" id="village_container" style="{{ $address->village_id ? '' : 'display: none;' }}">
                                                    <label class="form-label">Kelurahan/Desa</label>
                                                    <div class="custom_select">
                                                        <select class="form-control select-active @error('village_id') is-invalid @enderror" id="village_id" name="village_id">
                                                            <option value="">-- Pilih Kelurahan/Desa --</option>
                                                            @foreach($villages as $village)
                                                                <option value="{{ $village->id }}" {{ old('village_id', $address->village_id) == $village->id ? 'selected' : '' }}>
                                                                    {{ $village->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <small class="text-brand font-xs" id="village_loading" style="display: none;">
                                                        <i class="fi-rs-refresh spin mr-5"></i> Memuat data...
                                                    </small>
                                                </div>
                                                <div class="form-group col-md-4 mb-4" id="postal_code_container">
                                                    <label class="form-label">Kode Pos</label>
                                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                                           name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" 
                                                           placeholder="12345" maxlength="10">
                                                </div>

                                                <div class="form-group col-md-12 mb-4">
                                                    <label class="form-label">Alamat Lengkap <span class="required">*</span></label>
                                                    <textarea required class="form-control border-radius-10 @error('address_detail') is-invalid @enderror" 
                                                              name="address_detail" rows="3" 
                                                              style="height: auto;"
                                                              placeholder="Nama jalan, nomor rumah, RT/RW, patokan, dll">{{ old('address_detail', $address->address_detail) }}</textarea>
                                                </div>

                                                <div class="form-group col-md-12 mb-0">
                                                    <label class="form-label">Catatan Kurir (Opsional)</label>
                                                    <textarea class="form-control border-radius-10 @error('notes') is-invalid @enderror" 
                                                              name="notes" rows="2" 
                                                              style="height: auto;"
                                                              placeholder="Contoh: Rumah warna biru, samping masjid, dll">{{ old('notes', $address->notes) }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-check mb-30 custom-control custom-checkbox bg-light p-3 border-radius-10">
                                            <input class="form-check-input custom-control-input ml-10" type="checkbox" id="is_default" name="is_default" value="1" 
                                                   {{ old('is_default', $address->is_default) ? 'checked' : '' }}>
                                            <label class="form-check-label custom-control-label font-sm ml-25" for="is_default">
                                                <strong>Jadikan alamat utama</strong> (digunakan otomatis saat checkout)
                                            </label>
                                        </div>

                                        <div class="d-grid mt-40 pt-20 border-top">
                                            <button type="submit" class="btn btn-fill-out btn-lg w-100 rounded-pill font-weight-bold">
                                                <i class="fi-rs-check-circle mr-10"></i> Simpan Perubahan Alamat
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-info-light { background-color: rgba(59, 183, 126, 0.05); color: #3BB77E; }
    .required { color: #fd3d11; font-weight: bold; }
    .border-radius-15 { border-radius: 15px !important; }
    .border-radius-10 { border-radius: 10px !important; }
    .form-label { font-weight: 700; color: #253D4E; margin-bottom: 8px; font-size: 14px; display: block; }
    .location-box { background-color: #f9f9f9; border: 1px solid #eee !important; transition: all 0.3s ease; }
    .location-box:hover { border-color: #3BB77E !important; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .dashboard-menu { position: sticky; top: 100px; }
    
    /* Form control refinements */
    .form-control {
        height: 50px;
        padding: 0 20px;
        border-radius: 10px !important;
        border: 1px solid #ececec;
        font-size: 14px;
        background-color: #f7f8f9;
    }
    
    textarea.form-control {
        height: auto;
        padding: 15px 20px;
    }
    
    .form-control:focus {
        background-color: #fff;
        border-color: #3BB77E;
        box-shadow: 0 0 10px rgba(59, 183, 126, 0.1);
        outline: 0;
    }
    
    /* Select refinements */
    .custom_select {
        width: 100%;
    }
    
    .custom_select .select2-container--default .select2-selection--single {
        height: 50px;
        line-height: 50px;
        border-radius: 10px;
        border: 1px solid #ececec;
        background-color: #f7f8f9;
    }
    
    .custom_select .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 50px;
        padding-left: 20px;
        color: #687188;
        padding-right: 30px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .custom_select .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px;
    }

    /* Fixed height for normal select if select2 not enabled */
    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23687188' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 20px center;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .spin {
        display: inline-block;
        animation: spin 1s linear infinite;
    }
    
    /* Checkbox refinement */
    .custom-checkbox .custom-control-label::before {
        border-radius: 4px;
        border: 2px solid #3BB77E;
    }
    
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #3BB77E;
        border-color: #3BB77E;
    }
    
    .text-brand { color: #3BB77E !important; }
    .btn-fill-out {
        background-color: #3BB77E !important;
        border: 1px solid #3BB77E !important;
        color: #fff !important;
        padding: 15px 40px !important;
    }
    .btn-fill-out:hover {
        background-color: #29a56c !important;
        border-color: #29a56c !important;
    }
</style>


@push('scripts')
<script>
$(document).ready(function() {
    const provinceSelect = $('#province_id');
    const regencySelect = $('#regency_id');
    const districtSelect = $('#district_id');
    const villageSelect = $('#village_id');
    
    const regencyLoading = $('#regency_loading');
    const districtLoading = $('#district_loading');
    const villageLoading = $('#village_loading');

    function updateSelect(target, data, placeholder) {
        target.empty().append($('<option>', {
            value: '',
            text: placeholder
        }));
        
        if (data && data.length > 0) {
            $.each(data, function(i, item) {
                target.append($('<option>', {
                    value: item.id,
                    text: item.name
                }));
            });
            target.prop('disabled', false);
        } else {
            // Only disable if we are resetting from parent change
            // On initial load of edit page, some might be enabled
            // target.prop('disabled', true);
        }
        
        // Refresh Select2 if it's active
        if (target.hasClass('select2-hidden-accessible')) {
            target.trigger('change.select2');
        } else {
            target.trigger('change');
        }
    }

    provinceSelect.on('change', function() {
        const provinceId = $(this).val();
        
        // Reset sub selects
        updateSelect(regencySelect, [], '-- Pilih Kabupaten/Kota --');
        regencySelect.prop('disabled', true);
        updateSelect(districtSelect, [], '-- Pilih Kecamatan --');
        districtSelect.prop('disabled', true);
        updateSelect(villageSelect, [], '-- Pilih Kelurahan/Desa --');
        villageSelect.prop('disabled', true);

        if (provinceId) {
            regencyLoading.show();
            
            $.ajax({
                url: '{{ route("buyer.addresses.get-regencies") }}',
                type: 'GET',
                data: { province_id: provinceId },
                dataType: 'json',
                success: function(data) {
                    updateSelect(regencySelect, data, '-- Pilih Kabupaten/Kota --');
                    regencySelect.prop('disabled', false);
                    if (regencySelect.hasClass('select2-hidden-accessible')) {
                        regencySelect.trigger('change.select2');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading regencies:', error);
                    updateSelect(regencySelect, [], '-- Gagal memuat data --');
                },
                complete: function() {
                    regencyLoading.hide();
                }
            });
        }
    });

    regencySelect.on('change', function() {
        const regencyId = $(this).val();
        updateSelect(districtSelect, [], '-- Pilih Kecamatan --');
        districtSelect.prop('disabled', true);
        updateSelect(villageSelect, [], '-- Pilih Kelurahan/Desa --');
        villageSelect.prop('disabled', true);

        if (regencyId) {
            districtLoading.show();
            $.ajax({
                url: '{{ route("buyer.addresses.get-districts") }}',
                type: 'GET',
                data: { regency_id: regencyId },
                dataType: 'json',
                success: function(data) {
                    updateSelect(districtSelect, data, '-- Pilih Kecamatan --');
                    districtSelect.prop('disabled', false);
                    if (districtSelect.hasClass('select2-hidden-accessible')) {
                        districtSelect.trigger('change.select2');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading districts:', error);
                    updateSelect(districtSelect, [], '-- Gagal memuat data --');
                },
                complete: function() {
                    districtLoading.hide();
                }
            });
        }
    });

    districtSelect.on('change', function() {
        const districtId = $(this).val();
        updateSelect(villageSelect, [], '-- Pilih Kelurahan/Desa --');
        villageSelect.prop('disabled', true);

        if (districtId) {
            villageLoading.show();
            $.ajax({
                url: '{{ route("buyer.addresses.get-villages") }}',
                type: 'GET',
                data: { district_id: districtId },
                dataType: 'json',
                success: function(data) {
                    if (data && data.length > 0) {
                        $('#village_container').show();
                        updateSelect(villageSelect, data, '-- Pilih Kelurahan/Desa --');
                        villageSelect.prop('disabled', false);
                        if (villageSelect.hasClass('select2-hidden-accessible')) {
                            villageSelect.trigger('change.select2');
                        }
                    } else {
                        $('#village_container').hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading villages:', error);
                },
                complete: function() {
                    villageLoading.hide();
                }
            });
        }
    });
});
</script>
@endpush
@endsection
