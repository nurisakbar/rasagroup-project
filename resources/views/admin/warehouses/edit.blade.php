@extends('layouts.admin')

@section('title', 'Edit Hub')
@section('page-title', 'Edit Hub')
@section('page-description', 'Edit data hub')

@section('breadcrumb')
    <li><a href="{{ route('admin.warehouses.index') }}">Hub</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Edit Hub</h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <form role="form" action="{{ route('admin.warehouses.update', $warehouse) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        <div class="form-group @error('name') has-error @enderror">
                            <label for="name">Nama Hub <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $warehouse->name) }}" placeholder="Masukkan nama hub" required>
                            <p class="help-block">Contoh: Hub Jakarta Pusat, Hub Surabaya, dll</p>
                            @error('name')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('province_id') has-error @enderror">
                                    <label for="province_id">Provinsi <span class="text-danger">*</span></label>
                                    <select class="form-control" id="province_id" name="province_id" required>
                                        <option value="">-- Pilih Provinsi --</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->id }}" {{ old('province_id', $warehouse->province_id) == $province->id ? 'selected' : '' }}>
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('province_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('regency_id') has-error @enderror">
                                    <label for="regency_id">Kabupaten/Kota <span class="text-danger">*</span></label>
                                    <select class="form-control" id="regency_id" name="regency_id" required>
                                        <option value="">-- Pilih Kabupaten/Kota --</option>
                                        @foreach($regencies as $regency)
                                            <option value="{{ $regency->id }}" {{ old('regency_id', $warehouse->regency_id) == $regency->id ? 'selected' : '' }}>
                                                {{ $regency->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('regency_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('district_id') has-error @enderror">
                                    <label for="district_id">Kecamatan <span class="text-danger">*</span></label>
                                    <select class="form-control" id="district_id" name="district_id" required>
                                        <option value="">-- Pilih Kecamatan --</option>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->id }}" {{ old('district_id', $warehouse->district_id) == $district->id ? 'selected' : '' }}>
                                                {{ $district->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('district_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('village_id') has-error @enderror">
                                    <label for="village_id">Desa/Kelurahan</label>
                                    <select class="form-control" id="village_id" name="village_id">
                                        <option value="">-- Pilih Desa/Kelurahan --</option>
                                        @foreach($villages as $village)
                                            <option value="{{ $village->id }}" {{ old('village_id', $warehouse->village_id) == $village->id ? 'selected' : '' }}>
                                                {{ $village->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('village_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('address') has-error @enderror">
                            <label for="address">Alamat Lengkap</label>
                            <textarea class="form-control" id="address" name="address" rows="3" placeholder="Masukkan alamat lengkap hub">{{ old('address', $warehouse->address) }}</textarea>
                            @error('address')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('phone') has-error @enderror">
                            <label for="phone">Nomor Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $warehouse->phone) }}" placeholder="Contoh: 021-12345678">
                            @error('phone')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Deskripsi tambahan tentang hub">{{ old('description', $warehouse->description) }}</textarea>
                            @error('description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
                                Hub Aktif
                            </label>
                            <p class="help-block">Hub yang tidak aktif tidak akan ditampilkan dalam pilihan</p>
                        </div>
                    </div>
                    <!-- /.box-body -->

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update
                        </button>
                        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
            <!-- /.box -->
        </div>

        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi</h3>
                </div>
                <div class="box-body">
                    <p>Edit data hub sesuai kebutuhan.</p>
                    <hr>
                    <dl>
                        <dt>Dibuat Pada</dt>
                        <dd>{{ $warehouse->created_at->format('d M Y, H:i') }}</dd>
                        <dt>Diupdate Pada</dt>
                        <dd>{{ $warehouse->updated_at->format('d M Y, H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load regencies when province changes
    $('#province_id').change(function() {
        var provinceId = $(this).val();
        var regencySelect = $('#regency_id');
        var districtSelect = $('#district_id');
        var villageSelect = $('#village_id');
        
        regencySelect.html('<option value="">Loading...</option>');
        districtSelect.html('<option value="">-- Pilih Kecamatan --</option>');
        villageSelect.html('<option value="">-- Pilih Desa/Kelurahan --</option>');
        
        if (provinceId) {
            $.ajax({
                url: '{{ route("buyer.addresses.get-regencies") }}',
                type: 'GET',
                data: { province_id: provinceId },
                success: function(data) {
                    regencySelect.html('<option value="">-- Pilih Kabupaten/Kota --</option>');
                    $.each(data, function(index, regency) {
                        regencySelect.append('<option value="' + regency.id + '">' + regency.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    regencySelect.html('<option value="">Error loading data</option>');
                }
            });
        } else {
            regencySelect.html('<option value="">-- Pilih Kabupaten/Kota --</option>');
        }
    });

    // Load districts when regency changes
    $('#regency_id').change(function() {
        var regencyId = $(this).val();
        var districtSelect = $('#district_id');
        var villageSelect = $('#village_id');
        
        districtSelect.html('<option value="">Loading...</option>');
        villageSelect.html('<option value="">-- Pilih Desa/Kelurahan --</option>');
        
        if (regencyId) {
            $.ajax({
                url: '{{ route("buyer.addresses.get-districts") }}',
                type: 'GET',
                data: { regency_id: regencyId },
                success: function(data) {
                    districtSelect.html('<option value="">-- Pilih Kecamatan --</option>');
                    $.each(data, function(index, district) {
                        districtSelect.append('<option value="' + district.id + '">' + district.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    districtSelect.html('<option value="">Error loading data</option>');
                }
            });
        } else {
            districtSelect.html('<option value="">-- Pilih Kecamatan --</option>');
        }
    });

    // Load villages when district changes
    $('#district_id').change(function() {
        var districtId = $(this).val();
        var villageSelect = $('#village_id');
        
        villageSelect.html('<option value="">Loading...</option>');
        
        if (districtId) {
            $.ajax({
                url: '{{ route("buyer.addresses.get-villages") }}',
                type: 'GET',
                data: { district_id: districtId },
                success: function(data) {
                    villageSelect.html('<option value="">-- Pilih Desa/Kelurahan --</option>');
                    $.each(data, function(index, village) {
                        villageSelect.append('<option value="' + village.id + '">' + village.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    villageSelect.html('<option value="">Error loading data</option>');
                }
            });
        } else {
            villageSelect.html('<option value="">-- Pilih Desa/Kelurahan --</option>');
        }
    });
});
</script>
@endpush
