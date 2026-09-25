@extends('layouts.admin')

@section('title', 'Tambah Distributor')
@section('page-title', 'Tambah Distributor')
@section('page-description', 'Tambah distributor baru beserta hub-nya')

@section('breadcrumb')
    <li><a href="{{ route('admin.distributors.index') }}">Distributor</a></li>
    <li class="active">Tambah</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <form role="form" action="{{ route('admin.distributors.store') }}" method="POST">
                @csrf
                
                <!-- Hub Info -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-building"></i> Data Hub Distributor</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group @error('hub_name') has-error @enderror">
                            <label for="hub_name">Nama Hub <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="hub_name" name="hub_name" value="{{ old('hub_name') }}" placeholder="Contoh: Hub Distributor Jakarta" required>
                            @error('hub_name')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('province_id') has-error @enderror">
                                    <label for="province_id">Provinsi <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="province_id" name="province_id" required style="width: 100%;">
                                        <option value="">-- Pilih Provinsi --</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province['id'] }}" {{ old('province_id') == $province['id'] ? 'selected' : '' }}>
                                                {{ $province['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('province_id')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('regency_id') has-error @enderror">
                                    <label for="regency_id">Kabupaten/Kota <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="regency_id" name="regency_id" required style="width: 100%;">
                                        <option value="">-- Pilih Kabupaten/Kota --</option>
                                    </select>
                                    @error('regency_id')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('district_id') has-error @enderror">
                                    <label for="district_id">Kecamatan <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="district_id" name="district_id" required style="width: 100%;">
                                        <option value="">-- Pilih Kecamatan --</option>
                                    </select>
                                    @error('district_id')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('village_id') has-error @enderror">
                                    <label for="village_id">Desa/Kelurahan</label>
                                    <select class="form-control select2" id="village_id" name="village_id" style="width: 100%;">
                                        <option value="">-- Pilih Desa/Kelurahan --</option>
                                    </select>
                                    @error('village_id')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('address') has-error @enderror">
                            <label for="address">Alamat Lengkap</label>
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Alamat lengkap hub">{{ old('address') }}</textarea>
                            @error('address')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('postal_code') has-error @enderror">
                                    <label for="postal_code">Kode Pos</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" placeholder="Contoh: 12345" maxlength="10">
                                    @error('postal_code')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>



                        <div class="form-group @error('hub_phone') has-error @enderror">
                            <label for="hub_phone">Nomor Telepon Hub</label>
                            <input type="text" class="form-control" id="hub_phone" name="hub_phone" value="{{ old('hub_phone') }}" placeholder="Contoh: 021-12345678">
                            @error('hub_phone')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- User Account -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-user"></i> Akun Distributor</h3>
                    </div>
                    <div class="box-body">
                        <div class="callout callout-info">
                            <p><i class="fa fa-info-circle"></i> Detail entitas distributor utama. Akun staff untuk login ditambahkan melalui tab "Kelola Staff" setelah data disimpan.</p>
                        </div>

                        <div class="form-group @error('user_name') has-error @enderror">
                            <label for="user_name">Nama Distributor / Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_name" name="user_name" value="{{ old('user_name') }}" placeholder="Nama perusahaan atau perorangan" required>
                            @error('user_name')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        @include('admin.distributors.partials.payment-terms')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('credit_limit') has-error @enderror">
                                    <label for="credit_limit">Limit Kredit</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">Rp</span>
                                        <input type="text" class="form-control rupiah-format" id="credit_limit" name="credit_limit" value="{{ number_format(old('credit_limit', 0), 0, ',', '.') }}" placeholder="Contoh: 100.000.000">
                                    </div>
                                    @error('credit_limit')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('ar_outstanding') has-error @enderror">
                                    <label for="ar_outstanding">AR Outstanding</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">Rp</span>
                                        <input type="text" class="form-control rupiah-format" id="ar_outstanding" name="ar_outstanding" value="{{ number_format(old('ar_outstanding', 0), 0, ',', '.') }}" placeholder="Contoh: 50.000.000">
                                    </div>
                                    @error('ar_outstanding')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('aturan_minimal_masa_berlaku') has-error @enderror">
                                    <label for="aturan_minimal_masa_berlaku">Aturan Minimal Masa Berlaku (Bulan)</label>
                                    <input type="number" class="form-control" id="aturan_minimal_masa_berlaku" name="aturan_minimal_masa_berlaku" value="{{ old('aturan_minimal_masa_berlaku', 9) }}" placeholder="9">
                                    @error('aturan_minimal_masa_berlaku')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                @php
                                    $pakaiPpn = (string) old('pakai_ppn', '1');
                                @endphp
                                <div class="form-group @error('pakai_ppn') has-error @enderror">
                                    <label for="pakai_ppn">Pakai PPN</label>
                                    <select class="form-control" id="pakai_ppn" name="pakai_ppn">
                                        <option value="1" {{ $pakaiPpn === '1' ? 'selected' : '' }}>YA</option>
                                        <option value="0" {{ $pakaiPpn === '0' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                    @error('pakai_ppn')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> Simpan Distributor
                        </button>
                        <a href="{{ route('admin.distributors.index') }}" class="btn btn-default btn-lg">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi</h3>
                </div>
                <div class="box-body">
                    <p><strong>Distributor</strong> adalah mitra yang mengelola stock dan order di wilayahnya.</p>
                    <hr>
                    <p><i class="fa fa-check text-green"></i> Dapat login ke sistem</p>
                    <p><i class="fa fa-check text-green"></i> Mengelola stock produk</p>
                    <p><i class="fa fa-check text-green"></i> Memiliki hub sendiri</p>
                    <hr>
                    <p><i class="fa fa-link text-blue"></i> <strong>URL Login:</strong></p>
                    <code>{{ url('/distributor/login') }}</code>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2();

    // Load regencies when province changes
    $('#province_id').change(function() {
        var provinceId = $(this).val();
        var regencySelect = $('#regency_id');
        var districtSelect = $('#district_id');
        var villageSelect = $('#village_id');
        
        regencySelect.html('<option value="">Loading...</option>').trigger('change');
        districtSelect.html('<option value="">-- Pilih Kecamatan --</option>').trigger('change');
        villageSelect.html('<option value="">-- Pilih Desa/Kelurahan --</option>').trigger('change');
        
        if (provinceId) {
            $.ajax({
                url: '{{ route("admin.distributors.get-regencies") }}',
                type: 'GET',
                data: { province_id: provinceId },
                success: function(data) {
                    regencySelect.html('<option value="">-- Pilih Kabupaten/Kota --</option>');
                    $.each(data, function(index, regency) {
                        regencySelect.append('<option value="' + regency.id + '">' + regency.name + '</option>');
                    });
                    regencySelect.trigger('change');
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    regencySelect.html('<option value="">Error loading data</option>');
                }
            });
        } else {
            regencySelect.html('<option value="">-- Pilih Kabupaten/Kota --</option>').trigger('change');
        }
    });

    // Load districts when regency changes
    $('#regency_id').change(function() {
        var regencyId = $(this).val();
        var districtSelect = $('#district_id');
        var villageSelect = $('#village_id');
        
        districtSelect.html('<option value="">Loading...</option>').trigger('change');
        villageSelect.html('<option value="">-- Pilih Desa/Kelurahan --</option>').trigger('change');
        
        if (regencyId) {
            $.ajax({
                url: '{{ route("admin.distributors.get-districts") }}',
                type: 'GET',
                data: { regency_id: regencyId },
                success: function(data) {
                    districtSelect.html('<option value="">-- Pilih Kecamatan --</option>');
                    $.each(data, function(index, district) {
                        districtSelect.append('<option value="' + district.id + '">' + district.name + '</option>');
                    });
                    districtSelect.trigger('change');
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    districtSelect.html('<option value="">Error loading data</option>');
                }
            });
        } else {
            districtSelect.html('<option value="">-- Pilih Kecamatan --</option>').trigger('change');
        }
    });

    // Load villages when district changes
    $('#district_id').change(function() {
        var districtId = $(this).val();
        var villageSelect = $('#village_id');
        
        villageSelect.html('<option value="">Loading...</option>').trigger('change');
        
        if (districtId) {
            $.ajax({
                url: '{{ route("admin.distributors.get-villages") }}',
                type: 'GET',
                data: { district_id: districtId },
                success: function(data) {
                    villageSelect.html('<option value="">-- Pilih Desa/Kelurahan --</option>');
                    $.each(data, function(index, village) {
                        villageSelect.append('<option value="' + village.id + '">' + village.name + '</option>');
                    });
                    villageSelect.trigger('change');
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    villageSelect.html('<option value="">Error loading data</option>');
                }
            });
        } else {
            villageSelect.html('<option value="">-- Pilih Desa/Kelurahan --</option>').trigger('change');
        }
    });



    // Rupiah Formatter
    function formatRupiah(angka, prefix) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
    }

    $('.rupiah-format').on('keyup', function() {
        $(this).val(formatRupiah($(this).val()));
    });

    $('form').on('submit', function() {
        $('.rupiah-format').each(function() {
            var val = $(this).val().replace(/\./g, '');
            $(this).val(val);
        });
    });

    // Toggle TOP container based on payment method
    $('#payment_method').change(function() {
        if ($(this).val() === 'TOP') {
            $('#top_container').show();
        } else {
            $('#top_container').hide();
            $('#credit_terms_code').val('');
        }
    });
});
</script>
@endpush

