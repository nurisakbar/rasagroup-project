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
            <div class="card border-0 shadow-sm border-radius-10">
                <div class="card-header border-bottom-0 bg-white p-4 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0 heading-2">Alamat Pengiriman</h3>
                    <a href="{{ route('buyer.addresses.create') }}" class="btn btn-sm btn-primary">
                        <i class="fi-rs-plus mr-5"></i> Tambah Alamat Baru
                    </a>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
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
                            <a href="{{ route('buyer.addresses.create') }}" class="btn btn-primary">
                                <i class="fi-rs-plus mr-5"></i> Tambah Alamat Pertama
                            </a>
                        </div>
                    @else
                        <div class="row">
                            @foreach($addresses as $address)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 {{ $address->is_default ? 'border-brand' : 'border-light' }}" style="border-width: 1.5px;">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <span class="badge rounded-pill bg-light text-muted font-xs mb-2">{{ strtoupper($address->label) }}</span>
                                                    @if($address->is_default)
                                                        <span class="badge rounded-pill bg-brand text-white font-xs mb-2 ms-1">Utama</span>
                                                    @endif
                                                    <h5 class="mb-0">{{ $address->recipient_name }}</h5>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary p-1 border-0" type="button" data-bs-toggle="dropdown">
                                                        <i class="fi-rs-menu-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                        <li>
                                                            <a class="dropdown-item font-sm" href="{{ route('buyer.addresses.edit', $address) }}">
                                                                <i class="fi-rs-edit mr-5"></i> Edit
                                                            </a>
                                                        </li>
                                                        @if(!$address->is_default)
                                                            <li>
                                                                <form action="{{ route('buyer.addresses.set-default', $address) }}" method="POST">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <button type="submit" class="dropdown-item font-sm">
                                                                        <i class="fi-rs-star mr-5"></i> Jadikan Utama
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('buyer.addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('Hapus alamat ini?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item font-sm text-danger">
                                                                    <i class="fi-rs-trash mr-5"></i> Hapus
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <p class="font-sm text-muted mb-2">
                                                <i class="fi-rs-phone-call mr-5"></i> {{ $address->phone }}
                                            </p>
                                            
                                            <p class="font-md mb-2">
                                                {{ $address->address_detail }}
                                            </p>
                                            
                                            <p class="font-sm text-muted mb-3">
                                                <i class="fi-rs-marker mr-5"></i> 
                                                Kec. {{ $address->district?->name }}, 
                                                {{ $address->regency?->name }}, 
                                                {{ $address->province?->name }} 
                                                @if($address->postal_code)
                                                    - {{ $address->postal_code }}
                                                @endif
                                            </p>

                                            @if($address->notes)
                                                <div class="bg-light p-2 rounded">
                                                    <p class="font-xs text-muted mb-0">
                                                        <strong>Catatan:</strong> {{ $address->notes }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="mt-4 border-top pt-4">
                        <a href="{{ route('buyer.dashboard') }}" class="btn btn-outline-primary btn-sm rounded font-sm">
                            <i class="fi-rs-arrow-left mr-5"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

