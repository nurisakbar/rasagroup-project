@extends('layouts.admin')

@section('title', 'Tambah Hub')
@section('page-title', 'Tambah Hub')
@section('page-description', 'Tambah hub baru beserta akun pengelolanya')

@section('breadcrumb')
    <li><a href="{{ route('admin.warehouses.index') }}">Hub</a></li>
    <li class="active">Tambah</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <form role="form" action="{{ route('admin.warehouses.store') }}" method="POST">
                @csrf
                
                <!-- Hub Info -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-building"></i> Data Hub</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group @error('name') has-error @enderror">
                            <label for="name">Nama Hub <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama hub" required>
                            <p class="help-block">Contoh: Hub Jakarta Pusat, Hub Surabaya, dll</p>
                            @error('name')
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
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Masukkan alamat lengkap hub">{{ old('address') }}</textarea>
                            @error('address')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('latitude') has-error @enderror">
                                    <label for="latitude">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="Contoh: -6.2088">
                                    @error('latitude')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('longitude') has-error @enderror">
                                    <label for="longitude">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="Contoh: 106.8456">
                                    @error('longitude')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-sm btn-info" id="btn-get-coordinates">
                                <i class="fa fa-map-marker"></i> Ambil dari Lokasi Saya Saat Ini
                            </button>
                            <button type="button" class="btn btn-sm btn-success" id="btn-geocode">
                                <i class="fa fa-search"></i> Cari dari Kota/Kecamatan
                            </button>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('phone') has-error @enderror">
                                    <label for="phone">Nomor Telepon Hub</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 021-12345678">
                                    @error('phone')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                            <strong>Hub Aktif</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Deskripsi tambahan tentang hub">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- User Account -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-user"></i> Akun Pengelola Hub</h3>
                    </div>
                    <div class="box-body">
                        <div class="callout callout-info">
                            <p><i class="fa fa-info-circle"></i> Akun ini akan digunakan untuk login ke panel hub dan mengelola stock.</p>
                        </div>

                        <div class="form-group @error('user_name') has-error @enderror">
                            <label for="user_name">Nama Pengelola <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_name" name="user_name" value="{{ old('user_name') }}" placeholder="Nama lengkap pengelola" required>
                            @error('user_name')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('user_email') has-error @enderror">
                                    <label for="user_email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="user_email" name="user_email" value="{{ old('user_email') }}" placeholder="Email untuk login" required>
                                    @error('user_email')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('user_phone') has-error @enderror">
                                    <label for="user_phone">No. HP Pengelola</label>
                                    <input type="text" class="form-control" id="user_phone" name="user_phone" value="{{ old('user_phone') }}" placeholder="Nomor HP pengelola">
                                    @error('user_phone')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('user_password') has-error @enderror">
                                    <label for="user_password">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="user_password" name="user_password" placeholder="Minimal 8 karakter" minlength="8" required>
                                    @error('user_password')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_password_confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="user_password_confirmation" name="user_password_confirmation" placeholder="Ulangi password" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> Simpan Hub & Akun
                        </button>
                        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-default btn-lg">
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
                    <p><strong>Hub</strong> digunakan untuk mengelola lokasi penyimpanan dan stock produk.</p>
                    <hr>
                    <p><i class="fa fa-user text-green"></i> <strong>Akun Pengelola:</strong></p>
                    <p>Setiap hub memiliki akun pengelola yang dapat:</p>
                    <ul>
                        <li>Login ke panel hub</li>
                        <li>Melihat dashboard stock</li>
                        <li>Update jumlah stock produk</li>
                    </ul>
                    <hr>
                    <p><i class="fa fa-link text-blue"></i> <strong>URL Login Hub:</strong></p>
                    <code>{{ url('/warehouse/login') }}</code>
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
                url: '{{ route("admin.get-regencies") }}',
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
                url: '{{ route("admin.get-districts") }}',
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
                url: '{{ route("admin.get-villages") }}',
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
    // Geolocation for coordinates
    $('#btn-get-coordinates').click(function() {
        if ("geolocation" in navigator) {
            $(this).html('<i class="fa fa-spinner fa-spin"></i> Mendeteksi...').prop('disabled', true);
            navigator.geolocation.getCurrentPosition(function(position) {
                $('#latitude').val(position.coords.latitude.toFixed(8));
                $('#longitude').val(position.coords.longitude.toFixed(8));
                $('#btn-get-coordinates').html('<i class="fa fa-map-marker"></i> Ambil dari Lokasi Saya Saat Ini').prop('disabled', false);
                alert('Lokasi berhasil dideteksi!');
            }, function(error) {
                console.error("Error detecting location: ", error);
                $('#btn-get-coordinates').html('<i class="fa fa-map-marker"></i> Ambil dari Lokasi Saya Saat Ini').prop('disabled', false);
                alert('Gagal mendeteksi lokasi. Pastikan izin lokasi diberikan.');
            });
        } else {
            alert('Browser Anda tidak mendukung geolokasi.');
        }
    });

    // Geocode from selected city/district
    $('#btn-geocode').click(function() {
        var province = $('#province_id option:selected').text();
        var regency = $('#regency_id option:selected').text();
        var district = $('#district_id option:selected').text();
        
        if (!regency || regency.includes('Pilih')) {
            alert('Silakan pilih Kabupaten/Kota terlebih dahulu.');
            return;
        }

        var query = "";
        if (district && !district.includes('Pilih')) query += district + ", ";
        query += regency + ", " + province + ", Indonesia";

        var btn = $(this);
        btn.html('<i class="fa fa-spinner fa-spin"></i> Mencari...').prop('disabled', true);

        $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            type: 'GET',
            data: {
                q: query,
                format: 'json',
                limit: 1
            },
            success: function(data) {
                if (data && data.length > 0) {
                    $('#latitude').val(parseFloat(data[0].lat).toFixed(8));
                    $('#longitude').val(parseFloat(data[0].lon).toFixed(8));
                    alert('Berhasil menemukan koordinat untuk: ' + query);
                } else {
                    alert('Gagal menemukan koordinat untuk lokasi tersebut.');
                }
                btn.html('<i class="fa fa-search"></i> Cari dari Kota/Kecamatan').prop('disabled', false);
            },
            error: function() {
                alert('Gagal menghubungi layanan pencarian lokasi.');
                btn.html('<i class="fa fa-search"></i> Cari dari Kota/Kecamatan').prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
