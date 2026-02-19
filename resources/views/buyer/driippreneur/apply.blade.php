<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Daftar DRiiPPreneur
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
                                            <h5 class="mb-1">Jadilah DRiiPPreneur Kami!</h5>
                                            <p class="font-sm text-muted mb-0">Dapatkan keuntungan lebih dengan menjadi mitra DRiiPPreneur. Kelola stock sendiri dan raih penghasilan lebih besar.</p>
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

                                    <form action="{{ route('buyer.driippreneur.apply') }}" method="POST">
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

                                        <div class="form-group mb-4">
                                            <label>Nomor NPWP <span class="required">*</span></label>
                                            <input type="text" class="form-control @error('no_npwp') is-invalid @enderror" 
                                                   name="no_npwp" value="{{ old('no_npwp') }}" 
                                                   placeholder="Contoh: 12.345.678.9-012.345" maxlength="20" required>
                                            <small class="font-xs text-muted">Masukkan nomor NPWP Anda (wajib untuk DRiiPPreneur).</small>
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
                                    <h5 class="mb-0"><i class="fi-rs-star mr-10 text-brand"></i>Keuntungan Menjadi DRiiPPreneur</h5>
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
</style>
@endsection


