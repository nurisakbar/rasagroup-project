<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('buyer.point-withdrawals.index') }}">Penarikan Poin</a>
            <span></span> Ajukan Penarikan
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
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-withdraw-create">
                                    @csrf
                                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-withdraw-create').submit();">
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
                                        <h3 class="mb-0">Ajukan Penarikan Poin</h3>
                                        <a href="{{ route('buyer.point-withdrawals.index') }}" class="btn btn-sm btn-outline-secondary rounded font-sm">
                                            <i class="fi-rs-arrow-left mr-5"></i> Kembali
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body p-4 pt-0">
                                    <!-- Poin Info -->
                                    <div class="alert alert-info border-0 bg-info-light mb-4 text-center">
                                        <h6 class="text-brand mb-1">Poin Tersedia</h6>
                                        <h2 class="mb-0 text-brand">{{ number_format($user->points, 0, ',', '.') }} <span class="font-sm text-medium">Poin</span></h2>
                                    </div>

                                    @if(session('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="fi-rs-cross-circle mr-5"></i> {{ session('error') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif

                                    <form action="{{ route('buyer.point-withdrawals.store') }}" method="POST">
                                        @csrf

                                        <div class="form-group mb-3">
                                            <label>Jumlah Poin yang Ditarik <span class="required">*</span></label>
                                            <input type="number" 
                                                   class="form-control @error('amount') is-invalid @enderror" 
                                                   name="amount" 
                                                   value="{{ old('amount') }}" 
                                                   min="1" 
                                                   max="{{ $user->points }}"
                                                   placeholder="0"
                                                   required>
                                            @error('amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="font-xs text-muted">Maksimal penarikan: {{ number_format($user->points, 0, ',', '.') }} poin</small>
                                        </div>

                                        <div class="row">
                                            <div class="form-group col-md-12 mb-3">
                                                <label>Nama Bank <span class="required">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('bank_name') is-invalid @enderror" 
                                                       name="bank_name" 
                                                       value="{{ old('bank_name') }}" 
                                                       placeholder="Contoh: BCA, Mandiri, BNI, dll"
                                                       required>
                                                @error('bank_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group col-md-6 mb-3">
                                                <label>Nomor Rekening <span class="required">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('account_number') is-invalid @enderror" 
                                                       name="account_number" 
                                                       value="{{ old('account_number') }}" 
                                                       placeholder="Masukkan nomor rekening Anda"
                                                       required>
                                                @error('account_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group col-md-6 mb-3">
                                                <label>Atas Nama <span class="required">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('account_name') is-invalid @enderror" 
                                                       name="account_name" 
                                                       value="{{ old('account_name') }}" 
                                                       placeholder="Masukkan nama pemilik rekening"
                                                       required>
                                                @error('account_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="alert alert-warning border-0 bg-warning-light mb-4">
                                            <p class="font-sm mb-0">
                                                <i class="fi-rs-info mr-5"></i> 
                                                <strong>Penting:</strong> Permintaan penarikan akan diproses oleh tim admin. Poin Anda akan dikurangi secara otomatis setelah status penarikan diselesaikan.
                                            </p>
                                        </div>

                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-fill-out submit font-weight-bold">
                                                <i class="fi-rs-check-circle mr-5"></i> Ajukan Penarikan
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
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
    .required { color: #fd3d11; }
</style>
@endsection







