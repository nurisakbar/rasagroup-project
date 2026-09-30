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
                        @if ($errors->any())
                            <div class="alert border-radius-12 border-0 mb-30" role="alert" style="background-color: #fff5f5; border: 1px solid #feb2b2 !important; color: #742a2a;">
                                <strong class="d-block mb-2">Periksa kembali data berikut:</strong>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session('warning'))
                            <div class="alert border-radius-12 border-0 mb-30" role="alert" style="background-color: #fff8e6; border: 1px solid #f6d365 !important; color: #744210;">
                                <i class="fi-rs-info mr-5"></i>{{ session('warning') }}
                            </div>
                        @endif
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
                                    <div class="col-md-6">
                                        <div class="form-group mb-20">
                                            <label class="form-label-custom">Nama Sales</label>
                                            <select name="sales_code" id="sales_code" class="form-control custom-input select2" style="width: 100%;">
                                                <option value=""></option>
                                                @foreach($salesList as $salesUser)
                                                    <option value="{{ $salesUser->sales_code }}" {{ old('sales_code', Auth::user()?->sales_code) == $salesUser->sales_code ? 'selected' : '' }}>
                                                        {{ $salesUser->sales_code }} - {{ $salesUser->name }}
                                                    </option>
                                                @endforeach
                                            </select>
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

@endsection

@push('styles')
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
        height: 56px !important;
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

    /* Select2 overrides to match custom-input */
    body .buyer-profile-edit .select2-container {
        width: 100% !important;
        max-width: none !important;
    }
    body .buyer-profile-edit .select2-container .select2-selection--single {
        height: 56px !important;
        border: 1.5px solid #ECECEC !important;
        border-radius: 12px !important;
        background-color: #F8F9FA !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 20px !important;
        transition: all 0.3s ease;
    }
    body .buyer-profile-edit .select2-container--open .select2-selection--single,
    body .buyer-profile-edit .select2-container .select2-selection--single:focus {
        border-color: #6A1B1B !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(106, 27, 27, 0.05) !important;
    }
    body .buyer-profile-edit .select2-container .select2-selection--single .select2-selection__rendered {
        padding-left: 0 !important;
        color: #4F5D77 !important;
        line-height: normal !important;
        width: 100%;
    }
    body .buyer-profile-edit .select2-container .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        right: 15px !important;
        display: flex !important;
        align-items: center !important;
        top: 0 !important;
    }
</style>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#sales_code').select2({
            placeholder: '-- Pilih Nama Sales (Opsional) --',
            allowClear: true,
            width: '100%',
            dropdownAutoWidth: true
        });
        
        // Force width 100% via inline style and force styles on the container itself
        $('.select2-container').css({
            'width': '100%',
            'max-width': 'none'
        });
        
        // Sometimes CSS doesn't apply, so let's enforce via JS
        setTimeout(function() {
            var $selection = $('#sales_code').next('.select2-container').find('.select2-selection--single');
            $selection.css({
                'height': '56px',
                'border': '1.5px solid #ECECEC',
                'border-radius': '12px',
                'background-color': '#F8F9FA',
                'display': 'flex',
                'align-items': 'center',
                'padding': '0 20px'
            });
            $selection.find('.select2-selection__rendered').css({
                'padding-left': '0',
                'color': '#4F5D77',
                'line-height': 'normal',
                'width': '100%'
            });
            $selection.find('.select2-selection__arrow').css({
                'height': '100%',
                'right': '15px',
                'display': 'flex',
                'align-items': 'center',
                'top': '0'
            });
        }, 100);
    });
</script>
@endpush










