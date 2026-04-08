@extends('layouts.shop')

@section('title', 'Daftar Affiliator')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Daftar Affiliator
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
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-apply">
                                    @csrf
                                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-apply').submit();">
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
                            <div class="card border-0 bg-brand-light border-radius-10 mb-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fi-rs-star fs-1 text-brand"></i>
                                        </div>
                                        <div class="ms-4">
                                            <h5 class="mb-1">Jadilah Affiliator Kami!</h5>
                                            <p class="font-sm text-muted mb-0">Dapatkan keuntungan lebih dengan menjadi mitra Affiliator. Kelola stock sendiri dan raih penghasilan lebih besar.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom-0 p-4">
                                    <h3 class="mb-0">Formulir Pendaftaran</h3>
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

                                    <form action="{{ route('buyer.driippreneur.apply') }}" method="POST" enctype="multipart/form-data">
                                        @csrf

                                        <h5 class="mb-3">Informasi Akun</h5>
                                        <div class="row mb-4">
                                            <div class="form-group col-md-6 mb-3">
                                                <label>Nama Lengkap</label>
                                                <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                                            </div>
                                            <div class="form-group col-md-6 mb-3">
                                                <label>Email</label>
                                                <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                                            </div>
                                            <div class="form-group col-md-6 mb-3">
                                                <label>No. HP</label>
                                                <input type="text" class="form-control" value="{{ $user->phone ?? '-' }}" disabled>
                                            </div>
                                        </div>

                                        <div class="divider mb-4"></div>

                                        <h5 class="mb-3">Dokumen Verifikasi</h5>
                                        
                                        <div class="form-group mb-3">
                                            <label>Nomor KTP <span class="required">*</span></label>
                                            <input type="text" class="form-control @error('no_ktp') is-invalid @enderror" 
                                                   name="no_ktp" value="{{ old('no_ktp') }}" 
                                                   placeholder="Masukkan 16 digit nomor KTP" maxlength="16" required>
                                            <small class="font-xs text-muted">Masukkan 16 digit nomor KTP Anda sesuai e-KTP.</small>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label>Foto/Scan KTP <span class="required">*</span></label>
                                            <div id="ktp-camera-container" class="mb-3" style="display: none;">
                                                <div class="position-relative">
                                                    <video id="ktp-video" width="100%" height="auto" autoplay class="border-radius-10" style="background: #000;"></video>
                                                    <div class="camera-overlay"></div>
                                                </div>
                                                <div class="mt-2 text-center">
                                                    <button type="button" class="btn btn-sm btn-brand snap-btn" data-target="ktp"><i class="fi-rs-camera mr-5"></i>Ambil Foto</button>
                                                </div>
                                            </div>
                                            
                                            <div id="ktp-preview-container" class="mb-3" style="display: none;">
                                                <img id="ktp-preview-img" src="" class="border-radius-10 w-100">
                                                <div class="mt-2 text-center">
                                                    <button type="button" class="btn btn-sm btn-secondary retake-btn" data-target="ktp"><i class="fi-rs-refresh mr-5"></i>Foto Ulang</button>
                                                </div>
                                            </div>

                                            <div id="ktp-init-container" class="text-center p-4 border-dashed border-radius-10 mb-2">
                                                <button type="button" class="btn btn-brand start-camera-btn" data-target="ktp">
                                                    <i class="fi-rs-camera mr-5"></i> Buka Kamera KTP
                                                </button>
                                            </div>
                                            
                                            <input type="hidden" name="ktp_base64" id="ktp_base64">
                                            <input type="file" name="ktp_file" id="ktp_file_input" style="display: none;">
                                            <small class="font-xs text-muted">Akses kamera Anda untuk memotret KTP secara langsung.</small>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label>Nomor NPWP <span class="required">*</span></label>
                                            <input type="text" class="form-control @error('no_npwp') is-invalid @enderror" 
                                                   name="no_npwp" value="{{ old('no_npwp') }}" 
                                                   placeholder="Contoh: 12.345.678.9-012.345" maxlength="20" required>
                                            <small class="font-xs text-muted">Masukkan nomor NPWP Anda (wajib untuk Affiliator).</small>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label>Foto/Scan NPWP <span class="required">*</span></label>
                                            <div id="npwp-camera-container" class="mb-3" style="display: none;">
                                                <div class="position-relative">
                                                    <video id="npwp-video" width="100%" height="auto" autoplay class="border-radius-10" style="background: #000;"></video>
                                                    <div class="camera-overlay"></div>
                                                </div>
                                                <div class="mt-2 text-center">
                                                    <button type="button" class="btn btn-sm btn-brand snap-btn" data-target="npwp"><i class="fi-rs-camera mr-5"></i>Ambil Foto</button>
                                                </div>
                                            </div>
                                            
                                            <div id="npwp-preview-container" class="mb-3" style="display: none;">
                                                <img id="npwp-preview-img" src="" class="border-radius-10 w-100">
                                                <div class="mt-2 text-center">
                                                    <button type="button" class="btn btn-sm btn-secondary retake-btn" data-target="npwp"><i class="fi-rs-refresh mr-5"></i>Foto Ulang</button>
                                                </div>
                                            </div>

                                            <div id="npwp-init-container" class="text-center p-4 border-dashed border-radius-10 mb-2">
                                                <button type="button" class="btn btn-brand start-camera-btn" data-target="npwp">
                                                    <i class="fi-rs-camera mr-5"></i> Buka Kamera NPWP
                                                </button>
                                            </div>
                                            
                                            <input type="hidden" name="npwp_base64" id="npwp_base64">
                                            <input type="file" name="npwp_file" id="npwp_file_input" style="display: none;">
                                            <small class="font-xs text-muted">Akses kamera Anda untuk memotret NPWP secara langsung.</small>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label>Foto Selfie dengan KTP <span class="required">*</span></label>
                                            <div id="selfie-camera-container" class="mb-3" style="display: none;">
                                                <div class="position-relative">
                                                    <video id="selfie-video" width="100%" height="auto" autoplay class="border-radius-10" style="background: #000;"></video>
                                                    <div class="camera-overlay"></div>
                                                </div>
                                                <div class="mt-2 text-center">
                                                    <button type="button" class="btn btn-sm btn-brand snap-btn" data-target="selfie"><i class="fi-rs-camera mr-5"></i>Ambil Foto</button>
                                                </div>
                                            </div>
                                            
                                            <div id="selfie-preview-container" class="mb-3" style="display: none;">
                                                <img id="selfie-preview-img" src="" class="border-radius-10 w-100">
                                                <div class="mt-2 text-center">
                                                    <button type="button" class="btn btn-sm btn-secondary retake-btn" data-target="selfie"><i class="fi-rs-refresh mr-5"></i>Foto Ulang</button>
                                                </div>
                                            </div>

                                            <div id="selfie-init-container" class="text-center p-4 border-dashed border-radius-10 mb-2">
                                                <button type="button" class="btn btn-brand start-camera-btn" data-target="selfie">
                                                    <i class="fi-rs-camera mr-5"></i> Buka Kamera Selfie
                                                </button>
                                            </div>
                                            
                                            <input type="hidden" name="selfie_base64" id="selfie_base64">
                                            <input type="file" name="selfie_file" id="selfie_file_input" style="display: none;">
                                            <small class="font-xs text-muted">Akses kamera Anda untuk mengambil foto selfie dengan KTP.</small>
                                        </div>

                                        <div class="divider mb-4"></div>

                                        <h5 class="mb-3">Informasi Rekening Bank</h5>

                                        <div class="row">
                                            <div class="form-group col-md-12 mb-3">
                                                <label>Nama Bank <span class="required">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('bank_name') is-invalid @enderror" 
                                                       name="bank_name" 
                                                       value="{{ old('bank_name') }}" 
                                                       placeholder="Contoh: BCA, Mandiri, BNI, dll"
                                                       required>
                                            </div>
                                            <div class="form-group col-md-6 mb-3">
                                                <label>Nomor Rekening <span class="required">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('bank_account_number') is-invalid @enderror" 
                                                       name="bank_account_number" 
                                                       value="{{ old('bank_account_number') }}" 
                                                       placeholder="Masukkan nomor rekening Anda"
                                                       required>
                                            </div>
                                            <div class="form-group col-md-6 mb-3">
                                                <label>Atas Nama <span class="required">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('bank_account_name') is-invalid @enderror" 
                                                       name="bank_account_name" 
                                                       value="{{ old('bank_account_name') }}" 
                                                       placeholder="Masukkan nama pemilik rekening"
                                                       required>
                                            </div>
                                        </div>

                                        <div class="alert alert-info border-0 bg-info-light mb-4">
                                            <p class="font-sm mb-0">
                                                <i class="fi-rs-info mr-5"></i> 
                                                <strong>Catatan:</strong> Tim kami akan memverifikasi data Anda dalam 1-3 hari kerja. Setelah disetujui, Anda tetap dapat berbelanja seperti biasa dan akan mendapatkan poin untuk setiap item yang dibeli.
                                            </p>
                                        </div>

                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-fill-out submit font-weight-bold">
                                                <i class="fi-rs-paper-plane mr-5"></i> Kirim Pengajuan
                                            </button>
                                            <a href="{{ route('buyer.dashboard') }}" class="btn btn-outline-secondary rounded ml-10 font-sm">
                                                Kembali
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Benefits -->
                            <div class="card border-0 shadow-sm border-radius-10 mt-4">
                                <div class="card-header bg-white border-bottom-0 p-4 pb-0">
                                    <h5 class="mb-0"><i class="fi-rs-star mr-10 text-brand"></i>Keuntungan Menjadi Affiliator</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <ul class="list-unstyled font-sm">
                                                <li class="mb-2 d-flex align-items-start">
                                                    <i class="fi-rs-check text-success mr-10 mt-1"></i> 
                                                    <span>Dapatkan poin untuk setiap item yang dibeli secara online</span>
                                                </li>
                                                <li class="mb-2 d-flex align-items-start">
                                                    <i class="fi-rs-check text-success mr-10 mt-1"></i> 
                                                    <span>Poin dapat ditukar dengan berbagai reward menarik</span>
                                                </li>
                                                <li class="mb-2 d-flex align-items-start">
                                                    <i class="fi-rs-check text-success mr-10 mt-1"></i> 
                                                    <span>Berbelanja seperti biasa dengan akses fitur eksklusif</span>
                                                </li>
                                                <li class="mb-0 d-flex align-items-start">
                                                    <i class="fi-rs-check text-success mr-10 mt-1"></i> 
                                                    <span>Sistem poin yang mudah, otomatis, dan transparan</span>
                                                </li>
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
    .bg-brand-light { background-color: rgba(59, 183, 126, 0.1); }
    .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    .required { color: #fd3d11; }
    .divider { height: 1px; background-color: #f2f2f2; width: 100%; }
    .border-dashed { border: 2px dashed #f2f2f2; }
    .camera-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border: 2px dashed rgba(255, 255, 255, 0.5);
        border-radius: 10px;
        pointer-events: none;
        box-shadow: inset 0 0 0 50px rgba(0,0,0,0.1);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.createElement('canvas');
    let activeStream = null;

    // Handle Start Camera
    document.querySelectorAll('.start-camera-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const type = this.getAttribute('data-target');
            const video = document.getElementById(`${type}-video`);
            const cameraContainer = document.getElementById(`${type}-camera-container`);
            const previewContainer = document.getElementById(`${type}-preview-container`);
            const initContainer = document.getElementById(`${type}-init-container`);
            
            try {
                // Stop any active stream first
                if (activeStream) {
                    activeStream.getTracks().forEach(track => track.stop());
                }

                activeStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: type === 'selfie' ? "user" : "environment" }, 
                    audio: false 
                });
                
                video.srcObject = activeStream;
                cameraContainer.style.display = 'block';
                previewContainer.style.display = 'none';
                initContainer.style.display = 'none';
            } catch (err) {
                alert("Tidak dapat mengakses kamera: " + err.message);
            }
        });
    });

    // Handle Snap Photo
    document.querySelectorAll('.snap-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.getAttribute('data-target');
            const video = document.getElementById(`${type}-video`);
            const cameraContainer = document.getElementById(`${type}-camera-container`);
            const previewContainer = document.getElementById(`${type}-preview-container`);
            const previewImg = document.getElementById(`${type}-preview-img`);
            const base64Input = document.getElementById(`${type}_base64`);

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            const dataUrl = canvas.toDataURL('image/jpeg');
            previewImg.src = dataUrl;
            base64Input.value = dataUrl;
            
            // Stop camera
            if (activeStream) {
                activeStream.getTracks().forEach(track => track.stop());
                activeStream = null;
            }
            
            cameraContainer.style.display = 'none';
            previewContainer.style.display = 'block';
        });
    });

    // Handle Retake Photo
    document.querySelectorAll('.retake-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.getAttribute('data-target');
            const initContainer = document.getElementById(`${type}-init-container`);
            const previewContainer = document.getElementById(`${type}-preview-container`);
            
            previewContainer.style.display = 'none';
            initContainer.style.display = 'block';
            
            // Auto click start button 
            document.querySelector(`.start-camera-btn[data-target="${type}"]`).click();
        });
    });

    // Handle File Input Change
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const id = this.id;
            const type = id.replace('_file_input', '');
            const base64Input = document.getElementById(`${type}_base64`);
            const previewContainer = document.getElementById(`${type}-preview-container`);
            const cameraContainer = document.getElementById(`${type}-camera-container`);
            const startBtn = document.querySelector(`.start-camera-btn[data-target="${type}"]`);

            if (this.files && this.files[0]) {
                base64Input.value = '';
                previewContainer.style.display = 'none';
                cameraContainer.style.display = 'none';
                startBtn.style.display = 'block';
            }
        });
    });
});
</script>
@endsection


