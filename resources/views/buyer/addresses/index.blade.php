@extends('layouts.shop')

@section('title', 'Alamat Pengiriman')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Alamat Pengiriman
        </div>
    </div>
</div>

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-12 m-auto">
            <div class="row">
                <div class="col-lg-4">
                    @include('buyer.partials.sidebar')
                </div>
                <div class="col-lg-8">
                    <div class="tab-content account dashboard-content pl-lg-4 pl-0">
                        <div class="tab-pane fade show active" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-40">
                                        <h3 class="mb-0 address-header">Alamat Pengiriman</h3>
                                        <a href="{{ route('buyer.addresses.create') }}" class="btn btn-sm">
                                            <i class="fi-rs-plus mr-5"></i> Tambah Baru
                                        </a>
                                    </div>

                                    @if(session('success'))
                                        <div class="alert alert-success border-radius-12 mb-25">{{ session('success') }}</div>
                                    @endif

                                    @if($addresses->count() > 1)
                                        <div class="shopping-address-info mb-30 p-25 border-radius-15">
                                            <h6 class="mb-10 address-header" style="font-size: 16px;">
                                                <i class="fi-rs-shop mr-5 text-maroon"></i> Alamat untuk Hub Terdekat
                                            </h6>
                                            <p class="font-sm mb-0 text-muted">
                                                Pilih alamat yang menjadi acuan lokasi Anda saat mencari hub atau distributor terdekat untuk belanja.
                                            </p>
                                            @if($selectedShoppingAddressId && $selectedHubName)
                                                <p class="font-sm mb-0 mt-10">
                                                    <strong>Hub saat ini:</strong>
                                                    <span class="text-maroon">{{ $selectedHubName }}</span>
                                                </p>
                                            @elseif(!$selectedShoppingAddressId)
                                                <p class="font-sm mb-0 mt-10 text-warning">
                                                    Belum ada alamat belanja terpilih. Silakan pilih salah satu alamat di bawah.
                                                </p>
                                            @endif
                                        </div>
                                    @endif

                                    @if($addresses->isEmpty())
                                        <div class="text-center py-100">
                                            <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-location.svg') }}" alt="Empty" style="width: 80px; opacity: 0.2; margin-bottom: 25px;">
                                            <h4 class="mb-10 address-header">Belum ada alamat</h4>
                                            <p class="text-muted mb-30">Tambahkan alamat pengiriman untuk mempercepat checkout.</p>
                                            <a href="{{ route('buyer.addresses.create') }}" class="btn">
                                                <i class="fi-rs-plus mr-5"></i> Tambah Alamat
                                            </a>
                                        </div>
                                    @else
                                        <div class="row">
                                            @foreach($addresses as $address)
                                                @php
                                                    $isShoppingSelected = $selectedShoppingAddressId === $address->id;
                                                @endphp
                                                <div class="col-12 mb-25">
                                                    <div class="address-card p-30 {{ $isShoppingSelected ? 'shopping-selected' : ($address->is_default ? 'active' : '') }}" data-address-id="{{ $address->id }}">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center gap-2 mb-15 flex-wrap">
                                                                    <span class="badge px-3 py-2 border-radius-8" style="background: #f1f1f1; color: #7E7E7E; font-weight: 600; font-size: 11px;">{{ strtoupper($address->label) }}</span>
                                                                    @if($address->is_default)
                                                                        <span class="badge px-3 py-2 border-radius-8 bg-maroon text-white" style="font-weight: 600; font-size: 11px;">UTAMA</span>
                                                                    @endif
                                                                    @if($isShoppingSelected)
                                                                        <span class="badge px-3 py-2 border-radius-8 shopping-badge" style="font-weight: 600; font-size: 11px;">ALAMAT BELANJA</span>
                                                                    @endif
                                                                </div>
                                                                <h5 class="mb-15 address-header" style="font-size: 20px;">{{ $address->recipient_name }}</h5>
                                                                <p class="font-sm text-dark font-weight-bold mb-10"><i class="fi-rs-phone-call mr-10 text-maroon"></i> {{ $address->phone }}</p>
                                                                <p class="font-md mb-5 address-detail">{{ $address->address_detail }}</p>
                                                                <p class="font-sm mb-0 address-location">
                                                                    <i class="fi-rs-marker mr-10 text-maroon"></i> 
                                                                    @if($address->village) {{ $address->village->name }}, @endif
                                                                    {{ $address->district?->name }}, {{ $address->regency?->name }}, {{ $address->province?->name }} 
                                                                    @if($address->postal_code) - {{ $address->postal_code }} @endif
                                                                </p>
                                                            </div>

                                                            <div class="desktop-actions d-none d-md-flex align-items-center gap-2 flex-wrap">
                                                                <a href="{{ route('buyer.addresses.edit', $address) }}" class="btn btn-sm btn-outline-rasa border-radius-8 py-2">
                                                                    <i class="fi-rs-edit mr-5"></i> Edit
                                                                </a>

                                                                @if(!$address->is_default)
                                                                    <form action="{{ route('buyer.addresses.set-default', $address) }}" method="POST">
                                                                        @csrf @method('PUT')
                                                                        <button type="submit" class="btn btn-sm btn-outline-rasa border-radius-8 py-2">
                                                                            <i class="fi-rs-star mr-5"></i> Set Utama
                                                                        </button>
                                                                    </form>
                                                                @endif

                                                                <form action="{{ route('buyer.addresses.destroy', $address) }}" method="POST"
                                                                    onsubmit="return confirm('Hapus alamat ini? Alamat tidak akan ditampilkan lagi, tetapi data tetap tersimpan untuk pesanan lama.');">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-radius-8 py-2">
                                                                        <i class="fi-rs-trash mr-5"></i> Hapus
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>

                                                        @if($addresses->count() > 1)
                                                            <div class="shopping-select-row mt-20 pt-20">
                                                                @if($isShoppingSelected)
                                                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                                        <span class="font-sm text-maroon font-weight-bold">
                                                                            <i class="fi-rs-check mr-5"></i> Digunakan untuk hub terdekat
                                                                            @if($selectedHubName)
                                                                                — {{ $selectedHubName }}
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-shopping-select w-100 border-radius-8 py-2 select-shopping-address-btn"
                                                                        data-address-id="{{ $address->id }}">
                                                                        <i class="fi-rs-marker mr-5"></i> Gunakan untuk Hub Terdekat
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        @endif

                                                        <div class="mobile-actions d-flex d-md-none">
                                                            <a href="{{ route('buyer.addresses.edit', $address) }}" class="btn btn-sm btn-outline-rasa flex-grow-1 border-radius-8 py-2">Edit</a>
                                                            @if(!$address->is_default)
                                                                <form action="{{ route('buyer.addresses.set-default', $address) }}" method="POST" class="flex-grow-1">
                                                                    @csrf @method('PUT')
                                                                    <button type="submit" class="btn btn-sm btn-outline-rasa w-100 border-radius-8 py-2">Utama</button>
                                                                </form>
                                                            @endif
                                                            <form action="{{ route('buyer.addresses.destroy', $address) }}" method="POST" class="flex-grow-1"
                                                                onsubmit="return confirm('Hapus alamat ini? Alamat tidak akan ditampilkan lagi, tetapi data tetap tersimpan untuk pesanan lama.');">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100 border-radius-8 py-2">Hapus</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
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
    .bg-cream { background-color: #F2EAE1; }
    .text-maroon { color: #6A1B1B !important; }
    .bg-maroon { background-color: #6A1B1B !important; }
    .border-maroon { border-color: #6A1B1B !important; }
    
    .address-card {
        background: #fff;
        border-radius: 20px;
        border: 1.5px solid #edf2f7;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.02);
    }
    .address-card:hover {
        box-shadow: 0 10px 30px rgba(106, 27, 27, 0.08);
        border-color: #6A1B1B88;
        transform: translateY(-3px);
    }
    .address-card.active {
        border-color: #6A1B1B;
        background-color: rgba(106, 27, 27, 0.02);
    }

    .address-card.shopping-selected {
        border-color: #253D4E;
        background-color: rgba(37, 61, 78, 0.03);
        box-shadow: 0 10px 30px rgba(37, 61, 78, 0.1);
    }

    .shopping-badge {
        background: #253D4E;
        color: #fff;
    }

    .shopping-address-info {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .shopping-select-row {
        border-top: 1px solid #edf2f7;
    }

    .btn-shopping-select {
        background: #253D4E;
        color: #fff;
        border: none;
    }

    .btn-shopping-select:hover {
        background: #1a2a38;
        color: #fff;
    }

    .btn-shopping-select:disabled {
        opacity: 0.7;
    }
    
    .address-header {
        font-family: 'Fira Sans', sans-serif !important;
        font-weight: 700;
        color: #253D4E;
    }

    .address-detail {
        color: #253D4E;
        font-weight: 500;
    }

    .address-location {
        color: #5f6b7a;
        font-weight: 500;
    }

    .mobile-actions {
        gap: 10px;
        margin-top: 15px;
        border-top: 1px solid #eee;
        padding-top: 15px;
    }

    @media (max-width: 767.98px) {
        .tab-content {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
    }
</style>

@if($addresses->count() > 1)
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.select-shopping-address-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const addressId = this.dataset.addressId;
            const button = this;
            button.disabled = true;
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fi-rs-spinner fi-spin mr-5"></i> Memproses...';

            fetch('{{ route("buyer.addresses.select-for-shopping") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ address_id: addressId }),
            })
            .then(function(res) { return res.json().then(function(data) { return { ok: res.ok, data: data }; }); })
            .then(function(result) {
                if (result.ok && result.data.success) {
                    window.location.reload();
                    return;
                }
                alert(result.data.message || 'Gagal memilih alamat belanja.');
                button.disabled = false;
                button.innerHTML = originalHtml;
            })
            .catch(function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
                button.disabled = false;
                button.innerHTML = originalHtml;
            });
        });
    });
});
</script>
@endif
@endsection

