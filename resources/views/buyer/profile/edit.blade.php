@extends('layouts.shop')

@section('title', 'Edit Profil')

@section('content')
<div class="page-content pt-50 pb-80 buyer-profile-edit" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm border-radius-20 overflow-hidden" style="background-color: #ffffff;">
                    <div class="card-header bg-white border-bottom p-30">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0 fw-bold text-dark">Edit Profil</h3>
                            <a href="{{ route('buyer.profile') }}" class="btn btn-sm rounded-pill font-sm px-20 buyer-btn-maroon-outline">
                                <i class="fi-rs-arrow-left mr-5"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-30 p-md-40">
                        <form action="{{ route('buyer.profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-40">
                                <h5 class="mb-20 fw-bold text-brand"><i class="fi-rs-user mr-10"></i> Informasi Dasar</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Nama Lengkap</label>
                                            <input type="text" name="name" id="name" class="form-control custom-input" value="{{ old('name', Auth::user()->name) }}" required placeholder="Contoh: Budi Santoso">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Email</label>
                                            <input type="email" name="email" id="email" class="form-control custom-input" value="{{ old('email', Auth::user()->email) }}" required placeholder="email@contoh.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Telepon</label>
                                            <input type="text" name="phone" id="phone" class="form-control custom-input" value="{{ old('phone', Auth::user()->phone) }}" placeholder="081234567890">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Tanggal Lahir</label>
                                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control custom-input" value="{{ old('date_of_birth', optional(Auth::user()->date_of_birth)->format('Y-m-d')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Pekerjaan</label>
                                            <input type="text" name="occupation" id="occupation" class="form-control custom-input" value="{{ old('occupation', Auth::user()->occupation) }}" placeholder="Contoh: Karyawan Swasta">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-40">
                                <h5 class="mb-20 fw-bold text-brand"><i class="fi-rs-bank mr-10"></i> Informasi Rekening Bank</h5>
                                <p class="text-muted small mb-20">Data ini digunakan untuk pengajuan penarikan poin hasil afiliasi Anda.</p>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Nama Bank</label>
                                            @php
                                                $selectedBank = old('bank_name', Auth::user()->bank_name);
                                                $banks = [
                                                    'BCA',
                                                    'BRI',
                                                    'BNI',
                                                    'Bank Mandiri',
                                                    'CIMB Niaga',
                                                    'Bank Danamon',
                                                    'PermataBank',
                                                    'Bank BTN',
                                                    'BTPN',
                                                    'Bank Mega',
                                                    'Bank Sinarmas',
                                                    'OCBC NISP',
                                                    'Maybank Indonesia',
                                                    'PaninBank',
                                                    'Bank Bukopin',
                                                    'Bank Jago',
                                                    'Bank Muamalat',
                                                    'BSI (Bank Syariah Indonesia)',
                                                    'Bank Syariah Mandiri',
                                                    'BCA Syariah',
                                                    'BRI Syariah',
                                                    'BNI Syariah',
                                                    'Bank Jatim',
                                                    'Bank Jabar Banten (bjb)',
                                                    'Bank DKI',
                                                    'Bank Sumut',
                                                    'Bank Nagari',
                                                    'Bank Kaltimtara',
                                                    'Bank Kalsel',
                                                    'Bank Kalteng',
                                                    'Bank Sulselbar',
                                                    'Bank SulutGo',
                                                    'Bank Papua',
                                                ];
                                            @endphp
                                            <select name="bank_name" id="bank_name" class="form-control custom-input select2-bank" data-placeholder="Pilih bank">
                                                <option value=""></option>
                                                @foreach($banks as $bank)
                                                    <option value="{{ $bank }}" {{ $selectedBank === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                                @endforeach
                                                @if($selectedBank && !in_array($selectedBank, $banks, true))
                                                    <option value="{{ $selectedBank }}" selected>{{ $selectedBank }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Nomor Rekening</label>
                                            <input type="text" name="bank_account_number" id="bank_account_number" class="form-control custom-input" value="{{ old('bank_account_number', Auth::user()->bank_account_number) }}" placeholder="Nomor Rekening Anda">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Atas Nama</label>
                                            <input type="text" name="bank_account_name" id="bank_account_name" class="form-control custom-input" value="{{ old('bank_account_name', Auth::user()->bank_account_name) }}" placeholder="Nama sesuai buku tabungan">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-30 pt-30 border-top">
                                <button type="submit" class="btn w-100">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .buyer-profile-edit {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
    }
    .buyer-profile-edit i[class*="fi-rs"] {
        font-family: "uicons-regular-straight" !important;
    }
    .form-label-custom { font-weight: 600; color: #253D4E; margin-bottom: 8px; font-size: 14px; display: block; }
    .custom-input {
        background-color: #F8F9FA !important;
        border: 1.5px solid #ECECEC !important;
        border-radius: 12px !important;
        padding: 15px 20px !important;
        transition: all 0.3s ease;
    }
    .custom-input:focus {
        border-color: #6A1B1B !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(106, 27, 27, 0.05) !important;
    }
    .buyer-btn-maroon-outline {
        color: #6A1B1B !important;
        border: 2px solid #6A1B1B !important;
        background: transparent !important;
        font-weight: 600;
    }
    .buyer-btn-maroon-outline:hover {
        background: #6A1B1B !important;
        color: #fff !important;
    }

    /* Select2: tema Nest memakai .select2-container { max-width: 135px } — harus dibatalkan di sini */
    .buyer-profile-edit .select2-container {
        width: 100% !important;
        max-width: none !important;
    }
    .buyer-profile-edit .select2-container .select2-selection--single {
        background-color: #F8F9FA !important;
        border: 1.5px solid #ECECEC !important;
        border-radius: 12px !important;
        min-height: 60px !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 12px !important;
    }
    .buyer-profile-edit .select2-container .select2-selection--single .select2-selection__rendered {
        padding-left: 6px !important;
        color: #253D4E !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        line-height: 1.2 !important;
    }
    .buyer-profile-edit .select2-container .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        right: 10px !important;
    }
    .buyer-profile-edit .select2-container--default.select2-container--open .select2-selection--single,
    .buyer-profile-edit .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #6A1B1B !important;
        box-shadow: 0 0 0 4px rgba(106, 27, 27, 0.05) !important;
        background-color: #fff !important;
    }
    .buyer-profile-edit .select2-container--open .select2-dropdown,
    .buyer-profile-edit .select2-container--default .select2-dropdown {
        min-width: 100% !important;
        width: 100% !important;
        max-width: none !important;
        box-sizing: border-box !important;
        border: 1.5px solid #ECECEC !important;
        border-radius: 12px !important;
        overflow: hidden;
    }
    .buyer-profile-edit .select2-container--default .select2-results__option {
        padding: 12px 16px !important;
        font-size: 15px;
    }
    .buyer-profile-edit .select2-dropdown .select2-search__field {
        width: 100% !important;
        box-sizing: border-box !important;
    }
</style>

@push('scripts')
<script>
    $(function () {
        const $bank = $('#bank_name.select2-bank');
        if ($bank.length && $.fn.select2) {
            const $parent = $('.buyer-profile-edit').first();
            $bank.select2({
                width: '100%',
                placeholder: $bank.data('placeholder') || 'Pilih bank',
                allowClear: true,
                dropdownAutoWidth: false,
                dropdownParent: $parent.length ? $parent : undefined
            });
        }
    });
</script>
@endpush
@endsection









