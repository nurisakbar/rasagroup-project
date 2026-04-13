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

<div class="page-content pt-50 pb-80" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm border-radius-20 overflow-hidden" style="background-color: #ffffff;">
                    <div class="card-header bg-white border-bottom p-30">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Edit Alamat</h3>
                            <a href="{{ route('buyer.addresses.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill font-sm">
                                <i class="fi-rs-arrow-left mr-5"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-30 p-md-40">
                        @if ($errors->any())
                            <div class="alert alert-danger border-radius-12 border-0 mb-30" style="background-color: #fff5f5; color: #c53030;">
                                <ul class="mb-0 font-sm">
                                    @foreach ($errors->all() as $error)
                                        <li><i class="fi-rs-cross-circle mr-5"></i> {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('buyer.addresses.update', $address) }}" method="POST" id="addressForm">
                            @csrf
                            @method('PUT')

                            <div class="mb-40">
                                <h5 class="mb-20" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;"><i class="fi-rs-user mr-10"></i> Informasi Penerima</h5>
                                
                                <div class="form-group mb-25">
                                    <label class="form-label-custom">Simpan Alamat Sebagai</label>
                                    <div class="d-flex flex-wrap gap-2 mb-3 label-selector">
                                        @foreach(['Rumah' => 'home', 'Kantor' => 'briefcase', 'Toko' => 'shop', 'Lainnya' => 'marker'] as $label => $icon)
                                            <input type="radio" name="label" class="btn-check" id="label-{{ $icon }}" value="{{ $label }}" {{ old('label', $address->label) == $label ? 'checked' : '' }}>
                                            <label class="btn btn-outline-maroon-pill px-4 py-2" for="label-{{ $icon }}"><i class="fi-rs-{{ $icon }} mr-5"></i> {{ $label }}</label>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label-custom">Nama Penerima</label>
                                            <div class="input-with-icon">
                                                <i class="fi-rs-user icon-field"></i>
                                                <input type="text" required class="form-control custom-input px-40" name="recipient_name" value="{{ old('recipient_name', $address->recipient_name) }}" placeholder="Nama Lengkap">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label-custom">No. Telepon / WhatsApp</label>
                                            <div class="input-with-icon">
                                                <i class="fi-rs-smartphone icon-field"></i>
                                                <input type="text" required class="form-control custom-input px-40" name="phone" value="{{ old('phone', $address->phone) }}" placeholder="081234567890">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-40">
                                <h5 class="mb-20" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;"><i class="fi-rs-marker mr-10"></i> Informasi Lokasi</h5>
                                
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Provinsi</label>
                                        <div class="custom_select_wrapper">
                                            <select class="form-control select-active" id="province_id" name="province_id" required>
                                                <option value="">Pilih Provinsi</option>
                                                @foreach($provinces as $province)
                                                    <option value="{{ $province['id'] }}" {{ old('province_id', $address->province_id) == $province['id'] ? 'selected' : '' }}>{{ $province['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Kabupaten/Kota</label>
                                        <div class="custom_select_wrapper position-relative">
                                            <select class="form-control select-active" id="regency_id" name="regency_id" required>
                                                <option value="">Pilih Kabupaten</option>
                                                @foreach($regencies as $regency)
                                                    <option value="{{ $regency['id'] }}" {{ old('regency_id', $address->regency_id) == $regency['id'] ? 'selected' : '' }}>{{ $regency['name'] }}</option>
                                                @endforeach
                                            </select>
                                            <div class="loading-overlay-new" id="regency_loading"><div class="spinner-border spinner-border-sm text-maroon"></div></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Kecamatan</label>
                                        <div class="custom_select_wrapper position-relative">
                                            <select class="form-control select-active" id="district_id" name="district_id" required>
                                                <option value="">Pilih Kecamatan</option>
                                                @foreach($districts as $district)
                                                    <option value="{{ $district['id'] }}" {{ old('district_id', $address->district_id) == $district['id'] ? 'selected' : '' }}>{{ $district['name'] }}</option>
                                                @endforeach
                                            </select>
                                            <div class="loading-overlay-new" id="district_loading"><div class="spinner-border spinner-border-sm text-maroon"></div></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="village_container" style="{{ $address->village_id ? '' : 'display: none;' }}">
                                            <label class="form-label-custom">Kelurahan/Desa</label>
                                            <div class="custom_select_wrapper position-relative">
                                                <select class="form-control select-active" id="village_id" name="village_id">
                                                    <option value="">Pilih Desa</option>
                                                    @foreach($villages as $village)
                                                        <option value="{{ $village->id }}" {{ old('village_id', $address->village_id) == $village->id ? 'selected' : '' }}>{{ $village->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="loading-overlay-new" id="village_loading"><div class="spinner-border spinner-border-sm text-maroon"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label-custom">Alamat Lengkap</label>
                                        <textarea required class="form-control custom-textarea" name="address_detail" rows="3" placeholder="Nama Jalan, No. Rumah, RT/RW">{{ old('address_detail', $address->address_detail) }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Kode Pos</label>
                                        <input type="text" class="form-control custom-input" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" placeholder="12345">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Patokan (Opsional)</label>
                                        <input type="text" class="form-control custom-input" name="notes" value="{{ old('notes', $address->notes) }}" placeholder="Contoh: Depan Masjid">
                                    </div>
                                </div>
                            </div>

                            <div class="form-check mb-40 p-20 border-radius-12" style="background-color: #F8F9FA;">
                                <input class="form-check-input ms-0" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }}>
                                <label class="form-check-label ps-2 mt-1" for="is_default" style="font-size: 14px; color: #253D4E;">
                                    <strong>Jadikan alamat utama</strong> (digunakan otomatis saat checkout)
                                </label>
                            </div>

                            <button type="submit" class="btn btn-maroon-lg w-100" id="btnSubmit">
                                <span id="btnText">Simpan Perubahan Alamat</span>
                                <span id="btnLoading" style="display: none;"><i class="fi-rs-refresh spin mr-5"></i> Menyimpan...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-label-custom { font-family: 'Fira Sans', sans-serif; font-weight: 600; color: #253D4E; margin-bottom: 8px; font-size: 14px; display: block; }
    .custom-input, .custom-textarea {
        background-color: #F8F9FA !important;
        border: 1.5px solid #ECECEC !important;
        border-radius: 12px !important;
        padding: 15px 20px !important;
        transition: all 0.3s ease;
    }
    .custom-input:focus, .custom-textarea:focus {
        border-color: #6A1B1B !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(106, 27, 27, 0.05) !important;
    }
    .input-with-icon { position: relative; }
    .icon-field { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #6A1B1B; font-size: 16px; z-index: 5; }
    .px-40 { padding-left: 50px !important; }
    
    .btn-outline-maroon-pill {
        color: #7E7E7E;
        border: 1.5px solid #ECECEC;
        background-color: #F8F9FA;
        border-radius: 50px;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s;
    }
    .btn-check:checked + .btn-outline-maroon-pill {
        background-color: #6A1B1B !important;
        color: #fff !important;
        border-color: #6A1B1B !important;
    }

    .btn-maroon-lg {
        background-color: #6A1B1B !important;
        color: #fff !important;
        padding: 18px !important;
        border-radius: 15px !important;
        font-family: 'Fira Sans', sans-serif !important;
        font-weight: 700 !important;
        border: none !important;
        transition: all 0.3s;
    }
    .btn-maroon-lg:hover {
        background-color: #4D1313 !important;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(106, 27, 27, 0.2);
    }

    .loading-overlay-new { position: absolute; right: 15px; top: 18px; display: none; z-index: 10; }
    .text-maroon { color: #6A1B1B !important; }

    /* Select2 Skinning */
    .select2-container--default .select2-selection--single {
        background-color: #F8F9FA !important;
        border: 1.5px solid #ECECEC !important;
        border-radius: 12px !important;
        height: 54px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 52px !important;
        padding-left: 15px !important;
        color: #253D4E !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 52px !important; }
    
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #6A1B1B !important;
    }

    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .spin { display: inline-block; animation: spin 1s linear infinite; }
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

    // Initialize Select2 with 100% width
    $('.select-active').select2({
        width: '100%'
    });

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
            target.prop('disabled', true);
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
        updateSelect(districtSelect, [], '-- Pilih Kecamatan --');
        updateSelect(villageSelect, [], '-- Pilih Desa --');

        if (provinceId) {
            regencyLoading.css('display', 'flex');
            
            $.ajax({
                url: '{{ route("buyer.addresses.get-regencies") }}',
                type: 'GET',
                data: { province_id: provinceId },
                dataType: 'json',
                success: function(data) {
                    updateSelect(regencySelect, data, '-- Pilih Kabupaten/Kota --');
                    // Focus next select after brief delay for natural flow
                    setTimeout(() => {
                        regencySelect.select2('open');
                    }, 200);
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
        updateSelect(villageSelect, [], '-- Pilih Desa --');

        if (regencyId) {
            districtLoading.css('display', 'flex');
            $.ajax({
                url: '{{ route("buyer.addresses.get-districts") }}',
                type: 'GET',
                data: { regency_id: regencyId },
                dataType: 'json',
                success: function(data) {
                    updateSelect(districtSelect, data, '-- Pilih Kecamatan --');
                    setTimeout(() => {
                        districtSelect.select2('open');
                    }, 200);
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
        updateSelect(villageSelect, [], '-- Pilih Desa --');

        if (districtId) {
            villageLoading.css('display', 'flex');
            $.ajax({
                url: '{{ route("buyer.addresses.get-villages") }}',
                type: 'GET',
                data: { district_id: districtId },
                dataType: 'json',
                success: function(data) {
                    if (data && data.length > 0) {
                        $('#village_container').fadeIn();
                        updateSelect(villageSelect, data, '-- Pilih Desa --');
                        setTimeout(() => {
                            villageSelect.select2('open');
                        }, 200);
                    } else {
                        $('#village_container').fadeOut();
                        // Scroll to address detail if village is skipped
                        $('textarea[name="address_detail"]').focus();
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

    // Form submission helper
    $('#addressForm').on('submit', function() {
        $('#btnSubmit').prop('disabled', true);
        $('#btnText').hide();
        $('#btnLoading').show();
        $('.fi-rs-check-circle').hide();
    });

    // Auto focus and smooth scroll when select changes
    $('select.select-active').on('select2:select', function (e) {
        const nextGroup = $(this).closest('.form-group').nextAll('.form-group:not(:disabled)').first();
        if (nextGroup.length) {
            $('html, body').animate({
                scrollTop: nextGroup.offset().top - 200
            }, 500);
        }
    });
});
</script>
@endpush
@endsection
