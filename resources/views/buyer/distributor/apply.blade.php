@extends('layouts.shop')

@section('title', 'Daftar Distributor')

@push('styles')
<style>
    .form-select-lg, .form-control-lg {
        font-size: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('buyer.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Daftar Distributor</li>
                </ol>
            </nav>

            <!-- Info Banner -->
            <div class="card bg-warning bg-opacity-25 border-warning mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-truck fs-1 text-warning"></i>
                        </div>
                        <div class="ms-4">
                            <h5 class="card-title mb-1">Jadilah Distributor Kami!</h5>
                            <p class="card-text mb-0">Dapatkan keuntungan lebih dengan menjadi mitra distributor. Kelola stock sendiri dan raih penghasilan lebih besar.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Form -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Formulir Pendaftaran Distributor</h5>
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

                    <form action="{{ route('buyer.distributor.apply') }}" method="POST">
                        @csrf

                        <!-- Informasi Pribadi (readonly) -->
                        <h6 class="text-muted mb-3"><i class="bi bi-person"></i> Informasi Akun</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">No. HP</label>
                                <input type="text" class="form-control" value="{{ $user->phone ?? '-' }}" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Dokumen Verifikasi -->
                        <h6 class="text-muted mb-3"><i class="bi bi-card-text"></i> Dokumen Verifikasi</h6>
                        
                        <div class="mb-3">
                            <label for="no_ktp" class="form-label">Nomor KTP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('no_ktp') is-invalid @enderror" 
                                   id="no_ktp" name="no_ktp" value="{{ old('no_ktp') }}" 
                                   placeholder="Masukkan 16 digit nomor KTP" maxlength="16" required>
                            <div class="form-text">Masukkan 16 digit nomor KTP Anda sesuai e-KTP.</div>
                            @error('no_ktp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="no_npwp" class="form-label">Nomor NPWP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('no_npwp') is-invalid @enderror" 
                                   id="no_npwp" name="no_npwp" value="{{ old('no_npwp') }}" 
                                   placeholder="Contoh: 12.345.678.9-012.345" maxlength="20" required>
                            <div class="form-text">Masukkan nomor NPWP Anda (wajib untuk distributor).</div>
                            @error('no_npwp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Lokasi Usaha -->
                        <h6 class="text-muted mb-3"><i class="bi bi-geo-alt"></i> Lokasi Usaha</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="distributor_province_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg @error('distributor_province_id') is-invalid @enderror" 
                                        id="distributor_province_id" name="distributor_province_id" required>
                                    <option value="">-- Pilih Provinsi --</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('distributor_province_id') == $province->id ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('distributor_province_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="distributor_regency_id" class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg @error('distributor_regency_id') is-invalid @enderror" 
                                        id="distributor_regency_id" name="distributor_regency_id" required>
                                    <option value="">-- Pilih Kabupaten/Kota --</option>
                                </select>
                                @error('distributor_regency_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="distributor_address" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('distributor_address') is-invalid @enderror" 
                                      id="distributor_address" name="distributor_address" rows="4" 
                                      placeholder="Masukkan alamat lengkap lokasi usaha Anda (Jalan, RT/RW, Kelurahan, Kecamatan)" required>{{ old('distributor_address') }}</textarea>
                            <div class="form-text">Alamat lengkap akan digunakan untuk pengiriman barang dan koordinasi.</div>
                            @error('distributor_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Catatan:</strong> Setelah pengajuan dikirim, tim kami akan memverifikasi data Anda dalam 1-3 hari kerja. 
                            Anda akan mendapatkan notifikasi setelah pengajuan diproses.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send"></i> Kirim Pengajuan
                            </button>
                            <a href="{{ route('buyer.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Benefits -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-star"></i> Keuntungan Menjadi Distributor</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Harga khusus distributor</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Panel khusus untuk kelola stock</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Akses langsung ke sistem</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Dukungan tim support</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Dashboard analitik</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Program reward menarik</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const provinceSelect = document.getElementById('distributor_province_id');
    const regencySelect = document.getElementById('distributor_regency_id');

    provinceSelect.addEventListener('change', function() {
        const provinceId = this.value;
        regencySelect.innerHTML = '<option value="">Memuat...</option>';
        
        if (provinceId) {
            fetch(`{{ route('buyer.distributor.get-regencies') }}?province_id=${provinceId}`)
                .then(response => response.json())
                .then(data => {
                    regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                    data.forEach(regency => {
                        const option = document.createElement('option');
                        option.value = regency.id;
                        option.textContent = regency.name;
                        regencySelect.appendChild(option);
                    });
                })
                .catch(error => {
                    regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                    console.error('Error loading regencies:', error);
                });
        } else {
            regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
        }
    });

    // If there's old value for province, trigger the change event
    @if(old('distributor_province_id'))
    provinceSelect.dispatchEvent(new Event('change'));
    setTimeout(() => {
        regencySelect.value = '{{ old('distributor_regency_id') }}';
    }, 500);
    @endif
});
</script>
@endpush

