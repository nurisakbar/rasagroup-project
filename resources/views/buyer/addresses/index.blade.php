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
                                                <div class="col-12 mb-15">
                                                    <div class="address-card p-20" data-address-id="{{ $address->id }}">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center gap-2 mb-10 flex-wrap">
                                                                    <span class="badge px-3 py-1 border-radius-8" style="background: #f1f1f1; color: #7E7E7E; font-weight: 600; font-size: 10px;">{{ strtoupper($address->label) }}</span>
                                                                    @if($address->is_default)
                                                                        <span class="badge px-3 py-1 border-radius-8 bg-maroon text-white" style="font-weight: 600; font-size: 10px;">UTAMA</span>
                                                                    @endif

                                                                </div>
                                                                <h5 class="mb-5 address-header" style="font-size: 16px;">{{ $address->recipient_name }}</h5>
                                                                <p class="font-sm text-dark font-weight-bold mb-5"><i class="fi-rs-phone-call mr-5 text-maroon"></i> {{ $address->phone }}</p>
                                                                <p class="font-sm mb-5 address-detail">{{ $address->address_detail }}</p>
                                                                <p class="font-sm mb-0 address-location">
                                                                    <i class="fi-rs-marker mr-5 text-maroon"></i> 
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


@endsection

