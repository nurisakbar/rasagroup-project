@extends('layouts.shop')

@section('title', 'Edit Alamat')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('buyer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('buyer.addresses.index') }}">Alamat</a></li>
            <li class="breadcrumb-item active">Edit Alamat</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Alamat Pengiriman</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('buyer.addresses.update', $address) }}" method="POST" id="addressForm">
                        @csrf
                        @method('PUT')

                        <!-- Label & Penerima -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="label" class="form-label">Label Alamat <span class="text-danger">*</span></label>
                                <select class="form-select @error('label') is-invalid @enderror" id="label" name="label" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Rumah" {{ old('label', $address->label) == 'Rumah' ? 'selected' : '' }}>🏠 Rumah</option>
                                    <option value="Kantor" {{ old('label', $address->label) == 'Kantor' ? 'selected' : '' }}>🏢 Kantor</option>
                                    <option value="Toko" {{ old('label', $address->label) == 'Toko' ? 'selected' : '' }}>🏪 Toko</option>
                                    <option value="Lainnya" {{ old('label', $address->label) == 'Lainnya' ? 'selected' : '' }}>📍 Lainnya</option>
                                </select>
                                @error('label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="recipient_name" class="form-label">Nama Penerima <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('recipient_name') is-invalid @enderror" 
                                       id="recipient_name" name="recipient_name" value="{{ old('recipient_name', $address->recipient_name) }}" required>
                                @error('recipient_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="phone" class="form-label">No. HP Penerima <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $address->phone) }}" 
                                       placeholder="08xxxxxxxxxx" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="text-muted mb-3"><i class="bi bi-map"></i> Lokasi</h6>
                        
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle"></i> <strong>Info:</strong> Data wilayah saat ini tersedia untuk wilayah DKI Jakarta, Jawa Barat (Bandung), dan Jawa Timur (Surabaya). Jika wilayah Anda tidak tersedia, silakan hubungi admin.
                        </div>

                        <!-- Lokasi -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="province_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                                <select class="form-select @error('province_id') is-invalid @enderror" id="province_id" name="province_id" required>
                                    <option value="">-- Pilih Provinsi --</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('province_id', $address->province_id) == $province->id ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('province_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="regency_id" class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
                                <select class="form-select @error('regency_id') is-invalid @enderror" id="regency_id" name="regency_id" required>
                                    <option value="">-- Pilih Kabupaten/Kota --</option>
                                    @foreach($regencies as $regency)
                                        <option value="{{ $regency->id }}" {{ old('regency_id', $address->regency_id) == $regency->id ? 'selected' : '' }}>
                                            {{ $regency->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="regency_loading" style="display: none;">
                                    <i class="bi bi-arrow-repeat spin"></i> Memuat data...
                                </small>
                                @error('regency_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="district_id" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                                <select class="form-select @error('district_id') is-invalid @enderror" id="district_id" name="district_id" required>
                                    <option value="">-- Pilih Kecamatan --</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}" {{ old('district_id', $address->district_id) == $district->id ? 'selected' : '' }}>
                                            {{ $district->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="district_loading" style="display: none;">
                                    <i class="bi bi-arrow-repeat spin"></i> Memuat data...
                                </small>
                                @error('district_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="village_id" class="form-label">Kelurahan/Desa <span class="text-danger">*</span></label>
                                <select class="form-select @error('village_id') is-invalid @enderror" id="village_id" name="village_id" required>
                                    <option value="">-- Pilih Kelurahan/Desa --</option>
                                    @foreach($villages as $village)
                                        <option value="{{ $village->id }}" {{ old('village_id', $address->village_id) == $village->id ? 'selected' : '' }}>
                                            {{ $village->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="village_loading" style="display: none;">
                                    <i class="bi bi-arrow-repeat spin"></i> Memuat data...
                                </small>
                                @error('village_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-9">
                                <label for="address_detail" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('address_detail') is-invalid @enderror" 
                                          id="address_detail" name="address_detail" rows="2" 
                                          placeholder="Nama jalan, nomor rumah, RT/RW, patokan, dll" required>{{ old('address_detail', $address->address_detail) }}</textarea>
                                @error('address_detail')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="postal_code" class="form-label">Kode Pos</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" 
                                       placeholder="12345" maxlength="10">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan untuk Kurir (Opsional)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="2" 
                                      placeholder="Contoh: Rumah warna biru, dekat masjid, dll">{{ old('notes', $address->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" 
                                       {{ old('is_default', $address->is_default) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_default">
                                    Jadikan sebagai alamat utama
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('buyer.addresses.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
                        for (var i = 0; i < data.length; i++) {
                            var option = document.createElement('option');
                            option.value = data[i].id;
                            option.textContent = data[i].name;
                            villageSelect.appendChild(option);
                        }
                    } else {
                        villageSelect.innerHTML = '<option value="">-- Tidak ada data untuk kecamatan ini --</option>';
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
