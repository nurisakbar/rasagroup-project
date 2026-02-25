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
                            <div class="card border-0 shadow-sm border-radius-15 overflow-hidden">
                                <div class="card-header border-bottom bg-white p-4 d-flex justify-content-between align-items-center">
                                    <h3 class="mb-0 heading-3">Alamat Pengiriman</h3>
                                    <a href="{{ route('buyer.addresses.create') }}" class="btn btn-sm btn-fill-out rounded-pill">
                                        <i class="fi-rs-plus mr-5"></i> Tambah Alamat Baru
                                    </a>
                                </div>
                                <div class="card-body p-4 p-md-5">
                                    @if(session('success'))
                                        <div class="alert alert-success alert-dismissible border-0 fade show mb-4" role="alert">
                                            <i class="fi-rs-check mr-5"></i> {{ session('success') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif

                                    @if($addresses->isEmpty())
                                        <div class="text-center py-5">
                                            <div class="mb-4">
                                                <img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-location.svg') }}" alt="Empty" style="width: 80px; opacity: 0.5;">
                                            </div>
                                            <h4 class="mb-3 text-muted">Belum ada alamat</h4>
                                            <p class="text-muted mb-4">Tambahkan alamat pengiriman untuk memudahkan proses checkout pesanan Anda.</p>
                                            <a href="{{ route('buyer.addresses.create') }}" class="btn btn-fill-out rounded-pill">
                                                <i class="fi-rs-plus mr-5"></i> Tambah Alamat Pertama
                                            </a>
                                        </div>
                                    @else
                                        <div class="row">
                                            @foreach($addresses as $address)
                                                <div class="col-md-12 mb-4">
                                                    <div class="card h-100 border-radius-15 {{ $address->is_default ? 'border-brand shadow-sm' : 'border-light' }}" style="border-width: 1px;">
                                                        <div class="card-body p-4">
                                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                                <div>
                                                                    <div class="d-flex align-items-center mb-2">
                                                                        <span class="badge rounded-pill bg-light text-muted font-xs px-3 py-2 mr-10">{{ strtoupper($address->label) }}</span>
                                                                        @if($address->is_default)
                                                                            <span class="badge rounded-pill bg-brand text-white font-xs px-3 py-2">Utama</span>
                                                                        @endif
                                                                    </div>
                                                                    <h5 class="mb-1 text-brand">{{ $address->recipient_name }}</h5>
                                                                </div>
                                                                <div class="dropdown">
                                                                    <button class="btn btn-sm btn-outline-secondary p-2 border-0 rounded-circle" type="button" data-bs-toggle="dropdown">
                                                                        <i class="fi-rs-menu-dots-vertical"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 border-radius-10">
                                                                        <li>
                                                                            <a class="dropdown-item font-sm py-2" href="{{ route('buyer.addresses.edit', $address) }}">
                                                                                <i class="fi-rs-edit mr-10"></i> Edit Alamat
                                                                            </a>
                                                                        </li>
                                                                        @if(!$address->is_default)
                                                                            <li>
                                                                                <form action="{{ route('buyer.addresses.set-default', $address) }}" method="POST">
                                                                                    @csrf
                                                                                    @method('PUT')
                                                                                    <button type="submit" class="dropdown-item font-sm py-2">
                                                                                        <i class="fi-rs-star mr-10"></i> Jadikan Utama
                                                                                    </button>
                                                                                </form>
                                                                            </li>
                                                                            <li><hr class="dropdown-divider"></li>
                                                                            <li>
                                                                                <form action="{{ route('buyer.addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('Hapus alamat ini?');">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit" class="dropdown-item font-sm py-2 text-danger">
                                                                                        <i class="fi-rs-trash mr-10"></i> Hapus
                                                                                    </button>
                                                                                </form>
                                                                            </li>
                                                                        @endif
                                                                    </ul>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <p class="font-sm text-heading font-weight-bold mb-2">
                                                                        <i class="fi-rs-phone-call mr-10 text-brand"></i> {{ $address->phone }}
                                                                    </p>
                                                                    
                                                                    <p class="font-md text-muted mb-2">
                                                                        {{ $address->address_detail }}
                                                                    </p>
                                                                    
                                                                    <p class="font-sm text-muted mb-3">
                                                                        <i class="fi-rs-marker mr-10 text-brand"></i> 
                                                                        @if($address->village)
                                                                            Ds/Kel. {{ $address->village->name }},
                                                                        @endif
                                                                        Kec. {{ $address->district?->name }}, 
                                                                        {{ $address->regency?->name }}, 
                                                                        {{ $address->province?->name }} 
                                                                        @if($address->postal_code)
                                                                            - {{ $address->postal_code }}
                                                                        @endif
                                                                    </p>

                                                                    @if($address->notes)
                                                                        <div class="bg-light p-3 border-radius-10">
                                                                            <p class="font-xs text-muted mb-0">
                                                                                <strong class="text-heading">Catatan:</strong> {{ $address->notes }}
                                                                            </p>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
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
    .border-radius-15 { border-radius: 15px !important; }
    .border-radius-10 { border-radius: 10px !important; }
    .dashboard-menu { position: sticky; top: 100px; }
    .nav-link.active { font-weight: 700; color: #3BB77E !important; }
    .text-brand { color: #3BB77E !important; }
    .border-brand { border-color: #3BB77E !important; }
    .bg-brand { background-color: #3BB77E !important; }
    .btn-fill-out {
        background-color: #3BB77E !important;
        border: 1px solid #3BB77E !important;
        color: #fff !important;
    }
    .btn-fill-out:hover {
        background-color: #29a56c !important;
        border-color: #29a56c !important;
    }
    .card-body h5 { font-family: 'Quicksand', sans-serif; font-weight: 700; }
    .badge.bg-brand { padding: 5px 12px; }
</style>
@endsection

