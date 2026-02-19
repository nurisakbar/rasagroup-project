@extends('layouts.shop')

@section('title', 'Daftar Distributor')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Daftar Distributor
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
                                <a class="nav-link" href="{{ route('buyer.addresses.index') }}"><i class="fi-rs-marker mr-10"></i>Alamat Saya</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('buyer.profile') }}"><i class="fi-rs-user mr-10"></i>Detail Akun</a>
                            </li>
                            <li class="nav-item">
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-distributor">
                                    @csrf
                                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-distributor').submit();">
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
                            <!-- Info Banner -->
                            <div class="card mb-4 border-0 bg-info-light border-radius-10">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white p-3 rounded-circle me-4">
                                            <i class="fi-rs-truck-side text-info fs-1"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 text-info">Jadilah Distributor Kami!</h5>
                                            <p class="font-sm text-muted mb-0">Dapatkan keuntungan lebih dengan menjadi mitra distributor resmi. Kelola stok Anda sendiri dan raih penghasilan lebih besar bersama kami.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Application Form -->
                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <h3 class="mb-0">Formulir Pendaftaran Distributor</h3>
                                </div>
                                <div class="card-body p-4 pt-0">
                                    @if ($errors->any())
                                        <div class="alert alert-danger border-radius-5 font-sm mb-4">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li><i class="fi-rs-cross-circle mr-5"></i> {{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form action="{{ route('buyer.distributor.apply') }}" method="POST">
                                        @csrf

                                        <!-- Informasi Pribadi -->
                                        <h6 class="mb-3 text-brand border-bottom pb-2">Informasi Akun</h6>
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <label class="form-label font-sm">Nama Lengkap</label>
                                                <input type="text" class="form-control rounded font-sm" value="{{ $user->name }}" disabled>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label font-sm">Email</label>
                                                <input type="email" class="form-control rounded font-sm" value="{{ $user->email }}" disabled>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label font-sm">No. HP</label>
                                                <input type="text" class="form-control rounded font-sm" value="{{ $user->phone ?? '-' }}" disabled>
                                            </div>
                                        </div>

                                        <!-- Dokumen Verifikasi -->
                                        <h6 class="mb-3 text-brand border-bottom pb-2">Dokumen Verifikasi</h6>
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <label for="no_ktp" class="form-label font-sm">Nomor KTP <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded font-sm @error('no_ktp') is-invalid @enderror" 
                                                       id="no_ktp" name="no_ktp" value="{{ old('no_ktp') }}" 
                                                       placeholder="16 digit nomor KTP" maxlength="16" required>
                                                @error('no_ktp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="no_npwp" class="form-label font-sm">Nomor NPWP <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded font-sm @error('no_npwp') is-invalid @enderror" 
                                                       id="no_npwp" name="no_npwp" value="{{ old('no_npwp') }}" 
                                                       placeholder="Nomor NPWP" maxlength="20" required>
                                                @error('no_npwp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        <!-- Lokasi Usaha -->
                                        <h6 class="mb-3 text-brand border-bottom pb-2">Lokasi Usaha</h6>
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label for="distributor_province_id" class="form-label font-sm">Provinsi <span class="text-danger">*</span></label>
                                                <select class="form-select rounded font-sm @error('distributor_province_id') is-invalid @enderror" 
                                                        id="distributor_province_id" name="distributor_province_id" required>
                                                    <option value="">-- Pilih Provinsi --</option>
                                                    @foreach($provinces as $province)
                                                        <option value="{{ $province->id }}" {{ old('distributor_province_id') == $province->id ? 'selected' : '' }}>
                                                            {{ $province->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('distributor_province_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="distributor_regency_id" class="form-label font-sm">Kabupaten/Kota <span class="text-danger">*</span></label>
                                                <select class="form-select rounded font-sm @error('distributor_regency_id') is-invalid @enderror" 
                                                        id="distributor_regency_id" name="distributor_regency_id" required>
                                                    <option value="">-- Pilih Kabupaten/Kota --</option>
                                                </select>
                                                @error('distributor_regency_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="distributor_address" class="form-label font-sm">Alamat Lengkap Usaha <span class="text-danger">*</span></label>
                                            <textarea class="form-control rounded font-sm @error('distributor_address') is-invalid @enderror" 
                                                      id="distributor_address" name="distributor_address" rows="3" 
                                                      placeholder="Masukkan alamat lengkap lokasi usaha Anda (Jalan, RT/RW, Kelurahan, Kecamatan)" required>{{ old('distributor_address') }}</textarea>
                                            <div class="form-text font-xs">Alamat ini akan digunakan untuk pengiriman barang dan koordinasi stok.</div>
                                            @error('distributor_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="alert alert-warning border-0 bg-warning-light mb-4">
                                            <div class="d-flex align-items-center">
                                                <i class="fi-rs-info text-warning mr-10 fs-5"></i>
                                                <p class="font-xs text-muted mb-0">
                                                    Tim kami akan memverifikasi data Anda dalam 1-3 hari kerja. Anda akan mendapatkan notifikasi setelah pengajuan diproses.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <button type="submit" class="btn btn-brand rounded font-sm">
                                                <i class="fi-rs-paper-plane mr-5"></i> Kirim Pengajuan
                                            </button>
                                            <a href="{{ route('buyer.dashboard') }}" class="btn btn-outline-secondary rounded font-sm ml-10">
                                                Batal
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Benefits Section -->
                            <div class="card mt-4 border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom-0 p-4 pb-0">
                                    <h5 class="mb-0">Keuntungan Menjadi Distributor</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <ul class="list-unstyled font-sm">
                                                <li class="mb-2 d-flex align-items-center"><i class="fi-rs-check text-brand mr-10 fs-6"></i> Harga khusus & margin tinggi</li>
                                                <li class="mb-2 d-flex align-items-center"><i class="fi-rs-check text-brand mr-10 fs-6"></i> Panel manajemen stok distributor</li>
                                                <li class="mb-2 d-flex align-items-center"><i class="fi-rs-check text-brand mr-10 fs-6"></i> Akses langsung ke supply chain</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled font-sm">
                                                <li class="mb-2 d-flex align-items-center"><i class="fi-rs-check text-brand mr-10 fs-6"></i> Prioritas pengiriman barang</li>
                                                <li class="mb-2 d-flex align-items-center"><i class="fi-rs-check text-brand mr-10 fs-6"></i> Dashboard analitik penjualan</li>
                                                <li class="mb-2 d-flex align-items-center"><i class="fi-rs-check text-brand mr-10 fs-6"></i> Program reward eksklusif</li>
                                            </ul>
                                        </div>
                                    </div>
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
    .bg-info-light { background-color: rgba(13, 202, 240, 0.08); }
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.08); }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const provinceSelect = document.getElementById('distributor_province_id');
    const regencySelect = document.getElementById('distributor_regency_id');

    if (provinceSelect) {
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
                        
                        // If there's old value for regency, set it
                        const oldRegency = '{{ old('distributor_regency_id') }}';
                        if (oldRegency) {
                            regencySelect.value = oldRegency;
                        }
                    })
                    .catch(error => {
                        regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                        console.error('Error loading regencies:', error);
                    });
            } else {
                regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
            }
        });

        // Trigger loading regencies if province is already selected
        if (provinceSelect.value) {
            provinceSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endpush

