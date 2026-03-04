@extends('layouts.shop')

@section('title', 'Tambah Alamat Baru')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('buyer.addresses.index') }}">Alamat</a>
            <span></span> Tambah Alamat
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
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-create-address">
                                    @csrf
                                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-create-address').submit();">
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
                                        <h3 class="mb-0 heading-3">Tambah Alamat Baru</h3>
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

                                    <form action="{{ route('buyer.addresses.store') }}" method="POST" id="addressForm">
                                        @csrf

                                        <div class="row section-info mb-30 p-3 border-radius-15 bg-white shadow-sm border">
                                            <div class="col-12 mb-20">
                                                <h5 class="text-brand mb-10"><i class="fi-rs-user mr-10"></i> Informasi Penerima</h5>
                                                <p class="text-muted font-xs">Informasi dasar untuk identitas pengiriman.</p>
                                            </div>
                                            <div class="form-group col-md-12 mb-4">
                                                <label class="form-label">Simpan Alamat Sebagai <span class="required">*</span></label>
                                                <div class="d-flex flex-wrap gap-2 mb-3 mt-2 label-selector">
                                                    <input type="radio" name="label" class="btn-check" id="label-home" value="Rumah" {{ old('label', 'Rumah') == 'Rumah' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-brand rounded-pill px-4 py-2" for="label-home"><i class="fi-rs-home mr-5"></i> Rumah</label>

                                                    <input type="radio" name="label" class="btn-check" id="label-office" value="Kantor" {{ old('label') == 'Kantor' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-brand rounded-pill px-4 py-2" for="label-office"><i class="fi-rs-briefcase mr-5"></i> Kantor</label>

                                                    <input type="radio" name="label" class="btn-check" id="label-shop" value="Toko" {{ old('label') == 'Toko' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-brand rounded-pill px-4 py-2" for="label-shop"><i class="fi-rs-shop mr-5"></i> Toko</label>

                                                    <input type="radio" name="label" class="btn-check" id="label-other" value="Lainnya" {{ old('label') == 'Lainnya' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-brand rounded-pill px-4 py-2" for="label-other"><i class="fi-rs-marker mr-5"></i> Lainnya</label>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group col-md-6 mb-3">
                                                <label class="form-label">Nama Penerima <span class="required">*</span></label>
                                                <div class="input-with-icon">
                                                    <i class="fi-rs-user icon-field"></i>
                                                    <input type="text" required class="form-control px-40 @error('recipient_name') is-invalid @enderror" 
                                                           name="recipient_name" value="{{ old('recipient_name', Auth::user()->name) }}" placeholder="Nama Lengkap">
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6 mb-3">
                                                <label class="form-label">No. Telepon / WhatsApp <span class="required">*</span></label>
                                                <div class="input-with-icon">
                                                    <i class="fi-rs-smartphone icon-field"></i>
                                                    <input type="text" required class="form-control px-40 @error('phone') is-invalid @enderror" 
                                                           name="phone" value="{{ old('phone', Auth::user()->phone) }}" placeholder="Contoh: 081234567890">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="location-box border-radius-15 bg-white p-4 p-md-5 mt-20 mb-30 border shadow-sm">
                                            <div class="row align-items-start">
                                                <div class="col-12 mb-30">
                                                    <div class="d-flex align-items-center mb-10">
                                                        <div class="icon-shape bg-brand-light text-brand rounded-circle mr-15">
                                                            <i class="fi-rs-marker"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="mb-0 text-brand">Informasi Lokasi</h5>
                                                            <p class="text-muted font-xs mb-0">Wilayah pengiriman untuk akurasi ongkos kirim.</p>
                                                        </div>
                                                    </div>
                                                    <hr class="mt-20">
                                                </div>

                                                <div class="form-group col-md-6 mb-20 province-group">
                                                    <label class="form-label-custom">Provinsi <span class="required">*</span></label>
                                                    <div class="custom_select_wrapper">
                                                        <select class="form-control select-active @error('province_id') is-invalid @enderror" id="province_id" name="province_id" required>
                                                            <option value="">-- Pilih Provinsi --</option>
                                                            @foreach($provinces as $province)
                                                                <option value="{{ $province['id'] }}" {{ old('province_id') == $province['id'] ? 'selected' : '' }}>
                                                                    {{ $province['name'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6 mb-20 regency-group">
                                                    <label class="form-label-custom">Kabupaten/Kota <span class="required">*</span></label>
                                                    <div class="custom_select_wrapper position-relative">
                                                        <select class="form-control select-active @error('regency_id') is-invalid @enderror" id="regency_id" name="regency_id" required disabled>
                                                            <option value="">-- Pilih Kabupaten/Kota --</option>
                                                        </select>
                                                        <div class="loading-overlay-new" id="regency_loading">
                                                            <div class="spinner-border spinner-border-sm text-brand" role="status"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group col-md-4 mb-20 district-group">
                                                    <label class="form-label-custom">Kecamatan <span class="required">*</span></label>
                                                    <div class="custom_select_wrapper position-relative">
                                                        <select class="form-control select-active @error('district_id') is-invalid @enderror" id="district_id" name="district_id" required disabled>
                                                            <option value="">-- Pilih Kecamatan --</option>
                                                        </select>
                                                        <div class="loading-overlay-new" id="district_loading">
                                                            <div class="spinner-border spinner-border-sm text-brand" role="status"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-4 mb-20 village-group" id="village_container">
                                                    <label class="form-label-custom">Kelurahan/Desa</label>
                                                    <div class="custom_select_wrapper position-relative">
                                                        <select class="form-control select-active @error('village_id') is-invalid @enderror" id="village_id" name="village_id" disabled>
                                                            <option value="">-- Pilih Desa --</option>
                                                        </select>
                                                        <div class="loading-overlay-new" id="village_loading">
                                                            <div class="spinner-border spinner-border-sm text-brand" role="status"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-4 mb-20">
                                                    <label class="form-label-custom">Kode Pos</label>
                                                    <input type="text" class="form-control custom-input @error('postal_code') is-invalid @enderror" 
                                                           name="postal_code" value="{{ old('postal_code') }}" 
                                                           placeholder="12345" maxlength="10">
                                                </div>

                                                <div class="form-group col-md-12 mb-4">
                                                    <label class="form-label-custom d-flex justify-content-between">
                                                        <span>Alamat Lengkap <span class="required">*</span></span>
                                                        <span class="text-muted font-xxs">Jl. Rumah, No. Rumah, Blok, RT/RW</span>
                                                    </label>
                                                    <textarea required class="form-control custom-textarea @error('address_detail') is-invalid @enderror" 
                                                              name="address_detail" rows="3" 
                                                              tabindex="1"
                                                              placeholder="Contoh: Jl. Diponegoro No. 123, Blok C2, RT. 001/002, Kel. Menteng">{{ old('address_detail') }}</textarea>
                                                </div>

                                                <div class="form-group col-md-12 mb-0">
                                                    <label class="form-label-custom text-muted"><i class="fi-rs-info mr-5 font-xs"></i> Patokan / Catatan (Opsional)</label>
                                                    <input type="text" class="form-control custom-input @error('notes') is-invalid @enderror" 
                                                           name="notes" value="{{ old('notes') }}" 
                                                           placeholder="Contoh: Depan Alfamart, pagar kayu warna putih">
                                                </div>
                                            </div>
                                        </div>
                                        

                                        <div class="form-check mb-30 custom-control custom-checkbox bg-light p-3 border-radius-10">
                                            <input class="form-check-input custom-control-input ml-10" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                                            <label class="form-check-label custom-control-label font-sm ml-25" for="is_default">
                                                <strong>Jadikan alamat utama</strong> (digunakan otomatis saat checkout)
                                            </label>
                                        </div>

                                        <div class="d-grid mt-40 pt-20 border-top">
                                            <button type="submit" class="btn btn-fill-out btn-lg w-100 rounded-pill font-weight-bold" id="btnSubmit">
                                                <i class="fi-rs-check-circle mr-10"></i> <span id="btnText">Simpan Alamat Baru</span>
                                                <span id="btnLoading" style="display: none;"><i class="fi-rs-refresh spin mr-5"></i> Menyimpan...</span>
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
    
    .bg-info-light { background-color: rgba(59, 183, 126, 0.05); color: #3BB77E; }
    .required { color: #fd3d11; font-weight: bold; }
    .border-radius-15 { border-radius: 15px !important; }
    .border-radius-10 { border-radius: 10px !important; }
    
    .form-label-custom { font-weight: 700; color: #253D4E; margin-bottom: 10px; font-size: 14px; display: block; }
    .location-box { border-radius: 20px !important; border-color: #f1f1f1 !important; background-color: #ffffff; border: 1px solid #f1f1f1 !important; }
    .icon-shape { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .bg-brand-light { background-color: #f0f9f4 !important; }

    .section-info { transition: all 0.3s ease; border-radius: 20px !important; border: 1px solid #f1f1f1 !important; }
    .section-info:focus-within { border-color: #3BB77E !important; box-shadow: 0 10px 30px rgba(59, 183, 126, 0.08) !important; }

    /* Normalizing Input Heights & Padding */
    .form-control,
    .custom-input, 
    .custom-textarea,
    .custom_select_wrapper,
    .select2-container,
    .select2-selection--single {
        width: 100% !important;
        max-width: 100% !important;
        display: block !important;
    }

    .form-control,
    .custom-input, 
    .custom_select_wrapper .select2-container--default .select2-selection--single {
        height: 56px !important;
        background-color: #f9f9f9 !important;
        border: 1px solid #f1f1f1 !important;
        border-radius: 12px !important;
        transition: all 0.25s ease;
        padding-left: 5px !important;
    }

    .custom-textarea {
        background-color: #f9f9f9 !important;
        border: 1px solid #f1f1f1 !important;
        border-radius: 12px !important;
        padding: 18px 20px !important;
        height: auto !important;
    }

    /* Input with icon */
    .input-with-icon { position: relative; }
    .icon-field { position: absolute; left: 18px; top: 19px; color: #9B9B9B; font-size: 18px; z-index: 5; }
    .px-40 { padding-left: 50px !important; }

    /* Label Selector Hook */
    .btn-check:checked + label.btn-outline-brand {
        background-color: #3BB77E;
        color: white;
        border-color: #3BB77E;
    }
    .btn-outline-brand {
        color: #687188;
        border-color: #f1f1f1;
        background-color: #f9f9f9;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    .btn-outline-brand:hover {
        border-color: #3BB77E;
        color: #3BB77E;
    }

    /* Loading Overlay New */
    .loading-overlay-new {
        position: absolute;
        right: 15px;
        top: 0;
        height: 100%;
        display: none;
        align-items: center;
        z-index: 10;
        pointer-events: none;
    }

    /* Select2 Force Fixes */
    .custom_select_wrapper .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 54px !important;
        padding-left: 15px !important;
        color: #253D4E !important;
        font-size: 14px !important;
        display: block;
        width: 100%;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .custom_select_wrapper .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 54px !important;
        right: 12px !important;
    }

    .select2-container--default .select2-selection--single:focus,
    .custom-input:focus, .custom-textarea:focus {
        border-color: #3BB77E !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 3px rgba(59, 183, 126, 0.05) !important;
        outline: none;
    }

    .select2-container--open .select2-dropdown {
        border-color: #3BB77E;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-body { padding: 20px 15px !important; }
        .location-box, .section-info { padding: 25px 15px !important; }
        .form-label-custom { font-size: 13px; }
        .btn-fill-out { padding: 15px 20px !important; font-size: 15px; }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .spin {
        display: inline-block;
        animation: spin 1s linear infinite;
    }
    
    .text-brand { color: #3BB77E !important; }
    .btn-fill-out {
        background-color: #3BB77E !important;
        border: 1px solid #3BB77E !important;
        color: #fff !important;
        padding: 18px 40px !important;
        font-size: 16px;
        border-radius: 12px !important;
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
