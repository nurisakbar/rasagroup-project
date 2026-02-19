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
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4">
                    <div class="dashboard-menu">
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
                            <li class="nav-item">
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
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3 class="mb-0">Edit Alamat</h3>
                                        <a href="{{ route('buyer.addresses.index') }}" class="btn btn-sm btn-outline-secondary rounded font-sm">
                                            <i class="fi-rs-arrow-left mr-5"></i> Kembali
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body p-4 pt-0">
                                    @if ($errors->any())
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
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

                                        <div class="row mb-4">
                                            <div class="form-group col-md-4 mb-3">
                                                <label>Label Alamat <span class="required">*</span></label>
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
                                            <div class="form-group col-md-4 mb-3">
                                                <label>Nama Penerima <span class="required">*</span></label>
                                                <input required type="text" class="form-control @error('recipient_name') is-invalid @enderror" 
                                                       name="recipient_name" value="{{ old('recipient_name', $address->recipient_name) }}">
                                            </div>
                                            <div class="form-group col-md-4 mb-3">
                                                <label>No. HP Penerima <span class="required">*</span></label>
                                                <input required type="text" class="form-control @error('phone') is-invalid @enderror" 
                                                       name="phone" value="{{ old('phone', $address->phone) }}" placeholder="08xxxxxxxxxx">
                                            </div>
                                        </div>

                                        <div class="divider mt-2 mb-4"></div>
                                        <h5 class="mb-3">Informasi Lokasi</h5>
                                        
                                        <div class="alert alert-info border-0 bg-info-light mb-4">
                                            <p class="font-sm mb-0">
                                                <i class="fi-rs-info mr-5"></i> <strong>Penting:</strong> Pilih wilayah sesuai KTP atau alamat pengiriman Anda untuk perhitungan ongkos kirim yang akurat.
                                            </p>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="form-group col-md-6 mb-3">
                                                <label>Provinsi <span class="required">*</span></label>
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
                                            <div class="form-group col-md-6 mb-3">
                                                <label>Kabupaten/Kota <span class="required">*</span></label>
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
                                        </div>

                                        <div class="row mb-3">
                                            <div class="form-group col-md-6 mb-3">
                                                <label>Kecamatan <span class="required">*</span></label>
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
                                            <div class="form-group col-md-6 mb-3" id="village_container" style="{{ $address->village_id ? '' : 'display: none;' }}">
                                                <label>Kelurahan/Desa</label>
                                                <div class="custom_select">
                                                    <select class="form-control select-active @error('village_id') is-invalid @enderror" id="village_id" name="village_id">
                                                        <option value="">-- Pilih Kelurahan/Desa --</option>
                                                        @foreach($villages as $village)
                                                            <option value="{{ $village->id }}" {{ $address->village_id == $village->id ? 'selected' : '' }}>
                                                                {{ $village->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <small class="text-brand font-xs" id="village_loading" style="display: none;">
                                                    <i class="fi-rs-refresh spin mr-5"></i> Memuat data...
                                                </small>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="form-group col-md-9 mb-3">
                                                <label>Alamat Lengkap <span class="required">*</span></label>
                                                <textarea required class="form-control @error('address_detail') is-invalid @enderror" 
                                                          name="address_detail" rows="2" 
                                                          placeholder="Nama jalan, nomor rumah, RT/RW, patokan, dll">{{ old('address_detail', $address->address_detail) }}</textarea>
                                            </div>
                                            <div class="form-group col-md-3 mb-3">
                                                <label>Kode Pos</label>
                                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                                       name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" 
                                                       placeholder="12345" maxlength="10">
                                            </div>
                                        </div>

                                        <div class="form-group col-md-12 mb-4">
                                            <label>Catatan untuk Kurir (Opsional)</label>
                                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                      name="notes" rows="2" 
                                                      placeholder="Contoh: Rumah warna biru, dekat masjid, dll">{{ old('notes', $address->notes) }}</textarea>
                                        </div>

                                        <div class="form-check mb-4 custom-control custom-checkbox">
                                            <input class="form-check-input custom-control-input" type="checkbox" id="is_default" name="is_default" value="1" 
                                                   {{ old('is_default', $address->is_default) ? 'checked' : '' }}>
                                            <label class="form-check-label custom-control-label font-sm" for="is_default">
                                                Jadikan sebagai alamat utama
                                            </label>
                                        </div>

                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-fill-out submit font-weight-bold">
                                                <i class="fi-rs-check-circle mr-5"></i> Simpan Perubahan
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
    .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    .required { color: #fd3d11; }
    .divider { height: 1px; background-color: #f2f2f2; width: 100%; }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .spin {
        display: inline-block;
        animation: spin 1s linear infinite;
    }
</style>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.spin {
    display: inline-block;
    animation: spin 1s linear infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var provinceSelect = document.getElementById('province_id');
    var regencySelect = document.getElementById('regency_id');
    var districtSelect = document.getElementById('district_id');
    var villageSelect = document.getElementById('village_id');
    
    var regencyLoading = document.getElementById('regency_loading');
    var districtLoading = document.getElementById('district_loading');
    var villageLoading = document.getElementById('village_loading');

    // Helper function for fetch with error handling and retry
    function fetchData(url, retries) {
        retries = retries || 3;
        
        return new Promise(function(resolve, reject) {
            function attemptFetch(attempt) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', url, true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                
                var csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken.getAttribute('content'));
                }
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                var data = JSON.parse(xhr.responseText);
                                resolve(data);
                            } catch (e) {
                                console.error('JSON parse error:', e);
                                if (attempt < retries) {
                                    console.log('Retrying... attempt ' + (attempt + 1));
                                    setTimeout(function() { attemptFetch(attempt + 1); }, 500);
                                } else {
                                    reject(new Error('Failed to parse JSON'));
                                }
                            }
                        } else {
                            console.error('HTTP error:', xhr.status);
                            if (attempt < retries) {
                                console.log('Retrying... attempt ' + (attempt + 1));
                                setTimeout(function() { attemptFetch(attempt + 1); }, 500);
                            } else {
                                reject(new Error('HTTP error: ' + xhr.status));
                            }
                        }
                    }
                };
                
                xhr.onerror = function() {
                    console.error('Network error');
                    if (attempt < retries) {
                        console.log('Retrying... attempt ' + (attempt + 1));
                        setTimeout(function() { attemptFetch(attempt + 1); }, 500);
                    } else {
                        reject(new Error('Network error'));
                    }
                };
                
                xhr.timeout = 10000; // 10 second timeout
                xhr.ontimeout = function() {
                    console.error('Request timeout');
                    if (attempt < retries) {
                        console.log('Retrying... attempt ' + (attempt + 1));
                        setTimeout(function() { attemptFetch(attempt + 1); }, 500);
                    } else {
                        reject(new Error('Request timeout'));
                    }
                };
                
                xhr.send();
            }
            
            attemptFetch(1);
        });
    }

    // Province change
    provinceSelect.addEventListener('change', function() {
        var provinceId = this.value;
        
        // Reset child selects
        regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
        districtSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        villageSelect.innerHTML = '<option value="">-- Pilih Kelurahan/Desa --</option>';

        if (provinceId) {
            regencyLoading.style.display = 'block';
            
            fetchData('{{ route("buyer.addresses.get-regencies") }}?province_id=' + provinceId)
                .then(function(data) {
                    regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                    if (data && data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            var option = document.createElement('option');
                            option.value = data[i].id;
                            option.textContent = data[i].name;
                            regencySelect.appendChild(option);
                        }
                    } else {
                        regencySelect.innerHTML = '<option value="">-- Tidak ada data untuk provinsi ini --</option>';
                    }
                })
                .catch(function(error) {
                    console.error('Error loading regencies:', error);
                    regencySelect.innerHTML = '<option value="">-- Gagal memuat data, coba refresh --</option>';
                })
                .finally(function() {
                    regencyLoading.style.display = 'none';
                });
        }
    });

    // Regency change
    regencySelect.addEventListener('change', function() {
        var regencyId = this.value;
        
        // Reset child selects
        districtSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        villageSelect.innerHTML = '<option value="">-- Pilih Kelurahan/Desa --</option>';

        if (regencyId) {
            districtLoading.style.display = 'block';
            
            fetchData('{{ route("buyer.addresses.get-districts") }}?regency_id=' + regencyId)
                .then(function(data) {
                    districtSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                    if (data && data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            var option = document.createElement('option');
                            option.value = data[i].id;
                            option.textContent = data[i].name;
                            districtSelect.appendChild(option);
                        }
                    } else {
                        districtSelect.innerHTML = '<option value="">-- Tidak ada data untuk kabupaten ini --</option>';
                    }
                })
                .catch(function(error) {
                    console.error('Error loading districts:', error);
                    districtSelect.innerHTML = '<option value="">-- Gagal memuat data, coba refresh --</option>';
                })
                .finally(function() {
                    districtLoading.style.display = 'none';
                });
        }
    });

    // District change
    districtSelect.addEventListener('change', function() {
        var districtId = this.value;
        
        // Reset child select
        villageSelect.innerHTML = '<option value="">-- Pilih Kelurahan/Desa --</option>';

        if (districtId) {
            villageLoading.style.display = 'block';
            
            fetchData('{{ route("buyer.addresses.get-villages") }}?district_id=' + districtId)
                .then(function(data) {
                    villageSelect.innerHTML = '<option value="">-- Pilih Kelurahan/Desa --</option>';
                    if (data && data.length > 0) {
                        document.getElementById('village_container').style.display = 'block';
                        for (var i = 0; i < data.length; i++) {
                            var option = document.createElement('option');
                            option.value = data[i].id;
                            option.textContent = data[i].name;
                            villageSelect.appendChild(option);
                        }
                    } else {
                        document.getElementById('village_container').style.display = 'none';
                    }
                })
                .catch(function(error) {
                    console.error('Error loading villages:', error);
                    villageSelect.innerHTML = '<option value="">-- Gagal memuat data, coba refresh --</option>';
                })
                .finally(function() {
                    villageLoading.style.display = 'none';
                });
        }
    });
});
</script>
@endsection
