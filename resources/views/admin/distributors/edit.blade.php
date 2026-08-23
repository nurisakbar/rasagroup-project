@extends('layouts.admin')

@section('title', 'Edit Distributor')
@section('page-title', 'Edit Distributor')
@section('page-description', 'Perbarui data distributor dan hub-nya')

@section('breadcrumb')
    <li><a href="{{ route('admin.distributors.index') }}">Distributor</a></li>
    <li><a href="{{ route('admin.distributors.show', $distributor) }}">{{ $distributor->name }}</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <form role="form" action="{{ route('admin.distributors.update', $distributor) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Hub Info -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-building"></i> Data Hub Distributor</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group @error('hub_name') has-error @enderror">
                                    <label for="hub_name">Nama Hub <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="hub_name" name="hub_name" value="{{ old('hub_name', $distributor->warehouse->name ?? '') }}" placeholder="Contoh: Hub Distributor Jakarta" required>
                                    @error('hub_name')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group @error('is_active') has-error @enderror">
                                    <label for="is_active">Status Hub <span class="text-danger">*</span></label>
                                    <select class="form-control" id="is_active" name="is_active" required>
                                        <option value="1" {{ old('is_active', $distributor->warehouse->is_active ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ old('is_active', $distributor->warehouse->is_active ?? 1) == 0 ? 'selected' : '' }}>Non Aktif</option>
                                    </select>
                                    @error('is_active')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('province_id') has-error @enderror">
                                    <label for="province_id">Provinsi <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="province_id" name="province_id" required style="width: 100%;">
                                        <option value="">-- Pilih Provinsi --</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province['id'] }}" {{ old('province_id', $distributor->warehouse->province_id ?? '') == $province['id'] ? 'selected' : '' }}>
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
                                        @foreach($regencies as $regency)
                                            <option value="{{ $regency['id'] }}" {{ old('regency_id', $distributor->warehouse->regency_id ?? '') == $regency['id'] ? 'selected' : '' }}>
                                                {{ $regency['name'] }}
                                            </option>
                                        @endforeach
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
                                        @foreach($districts as $district)
                                            <option value="{{ $district['id'] }}" {{ old('district_id', $distributor->warehouse->district_id ?? '') == $district['id'] ? 'selected' : '' }}>
                                                {{ $district['name'] }}
                                            </option>
                                        @endforeach
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
                                        @foreach($villages as $village)
                                            @php $vId = is_array($village) ? $village['id'] : $village->id; $vName = is_array($village) ? $village['name'] : $village->name; @endphp
                                            <option value="{{ $vId }}" {{ old('village_id', $distributor->warehouse->village_id ?? '') == $vId ? 'selected' : '' }}>
                                                {{ $vName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('village_id')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('address') has-error @enderror">
                            <label for="address">Alamat Lengkap</label>
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Alamat lengkap hub">{{ old('address', $distributor->warehouse->address ?? '') }}</textarea>
                            @error('address')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('postal_code') has-error @enderror">
                                    <label for="postal_code">Kode Pos</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code', $distributor->warehouse->postal_code ?? '') }}" placeholder="Contoh: 12345" maxlength="10">
                                    @error('postal_code')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>



                        <div class="form-group @error('hub_phone') has-error @enderror">
                            <label for="hub_phone">Nomor Telepon Hub</label>
                            <input type="text" class="form-control" id="hub_phone" name="hub_phone" value="{{ old('hub_phone', $distributor->warehouse->phone ?? '') }}" placeholder="Contoh: 021-12345678">
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
                            <p><i class="fa fa-info-circle"></i> Detail entitas distributor utama. Akun staff untuk login dikelola melalui tab "Kelola Staff".</p>
                        </div>

                        <div class="form-group @error('user_name') has-error @enderror">
                            <label for="user_name">Nama Distributor / Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_name" name="user_name" value="{{ old('user_name', $distributor->name) }}" placeholder="Nama perusahaan atau perorangan" required>
                            @error('user_name')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('price_level_id') has-error @enderror">
                            <label for="price_level_id">Level Harga</label>
                            <select class="form-control" id="price_level_id" name="price_level_id">
                                <option value="">-- Pilih Level Harga (Opsional) --</option>
                                @foreach($priceLevels as $priceLevel)
                                    <option value="{{ $priceLevel->id }}" {{ old('price_level_id', $distributor->price_level_id) == $priceLevel->id ? 'selected' : '' }}>
                                        {{ $priceLevel->name }} (Diskon: {{ number_format($priceLevel->discount_percentage, 2) }}%)
                                    </option>
                                @endforeach
                            </select>
                            <span class="help-block">Pilih level harga untuk memberikan harga khusus pada distributor ini.</span>
                            @error('price_level_id')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('payment_method') has-error @enderror">
                                    <label for="payment_method">Cara Bayar</label>
                                    <select class="form-control" id="payment_method" name="payment_method">
                                        <option value="">-- Pilih Cara Bayar --</option>
                                        <option value="TOP" {{ old('payment_method', $distributor->payment_method) == 'TOP' ? 'selected' : '' }}>TOP</option>
                                        <option value="CIA" {{ old('payment_method', $distributor->payment_method) == 'CIA' ? 'selected' : '' }}>CIA</option>
                                    </select>
                                    @error('payment_method')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6" id="top_container" style="{{ old('payment_method', $distributor->payment_method) == 'TOP' ? '' : 'display: none;' }}">
                                <div class="form-group @error('term_of_payment') has-error @enderror">
                                    <label for="term_of_payment">Term Of Payment (Hari)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="term_of_payment" name="term_of_payment" value="{{ old('term_of_payment', $distributor->term_of_payment) }}" placeholder="Contoh: 30">
                                        <span class="input-group-addon">Hari</span>
                                    </div>
                                    @error('term_of_payment')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('credit_limit') has-error @enderror">
                                    <label for="credit_limit">Limit Kredit</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">Rp</span>
                                        <input type="text" class="form-control rupiah-format" id="credit_limit" name="credit_limit" value="{{ number_format(old('credit_limit', $distributor->credit_limit ?? 0), 0, ',', '.') }}" placeholder="Contoh: 100.000.000">
                                    </div>
                                    @error('credit_limit')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> Perbarui Distributor
                        </button>
                        <a href="{{ route('admin.distributors.show', $distributor) }}" class="btn btn-default btn-lg">
                            <i class="fa fa-arrow-left"></i> Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Statistik Hub</h3>
                </div>
                <div class="box-body">
                    <p><strong>Hub:</strong> {{ $distributor->warehouse->name ?? '-' }}</p>
                    <p><strong>Lokasi:</strong> {{ $distributor->warehouse->location_display ?? '-' }}</p>
                    <hr>
                    <p>Pastikan data alamat dan telepon sudah sesuai untuk keperluan pengiriman dan koordinasi.</p>
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
            $('#term_of_payment').val('');
        }
    });
});
</script>
@endpush
