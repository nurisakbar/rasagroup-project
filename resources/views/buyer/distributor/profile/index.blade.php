@extends('layouts.shop')

@section('title', 'Profil Distributor')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Profil Distributor
        </div>
    </div>
</div>

<div class="container mb-80 mt-50">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4">
                    @include('buyer.partials.sidebar')
                </div>
                <div class="col-lg-8">
                    <div class="tab-content account dashboard-content pl-50">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <div class="card border-0 shadow-sm border-radius-10">
                                <div class="card-header bg-white border-bottom p-4">
                                    <h3 class="mb-0">Profil distributor</h3>
                                    <p class="text-muted font-sm mb-0">Data akun toko online, hub warehouse, dan tautan untuk mengubah profil serta alamat pengiriman.</p>
                                </div>
                                <div class="card-body p-4">
                                    @php
                                        $wh = $user->warehouse;
                                    @endphp
                                    <div class="mb-4 d-flex flex-wrap gap-2">
                                        <a href="{{ route('buyer.profile') }}" class="btn btn-sm rounded-pill buyer-btn-maroon-outline">
                                            <i class="fi-rs-eye mr-5"></i>Detail profil lengkap
                                        </a>
                                        <a href="{{ route('buyer.profile.edit') }}" class="btn btn-sm btn-brand rounded-pill">
                                            <i class="fi-rs-edit mr-5"></i>Update profil
                                        </a>
                                        <a href="{{ route('buyer.addresses.index') }}" class="btn btn-sm rounded-pill" style="border: 2px solid #253D4E; color: #253D4E; background: #fff; font-weight: 600;">
                                            <i class="fi-rs-marker mr-5"></i>Kelola alamat
                                        </a>
                                        <a href="{{ route('distributor.orders.history') }}" class="btn btn-sm rounded-pill" style="border: 2px solid #3BB77E; color: #3BB77E; background: #fff; font-weight: 600;">
                                            <i class="fi-rs-history mr-5"></i>Riwayat Pembelian
                                        </a>
                                    </div>

                                    <div class="p-4 border-radius-10 mb-4" style="background: linear-gradient(135deg, #faf6f4 0%, #fff 100%); border: 1px solid #ece7e4;">
                                        <h5 class="mb-3 font-sm" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;">Akun</h5>
                                        <div class="row g-3 font-sm">
                                            <div class="col-md-4">
                                                <span class="text-muted d-block font-xs">Nama</span>
                                                <strong class="text-heading">{{ $user->name }}</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="text-muted d-block font-xs">Email</span>
                                                <strong class="text-heading">{{ $user->email }}</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="text-muted d-block font-xs">Telepon</span>
                                                <strong class="text-heading">{{ $user->phone ?? '—' }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    @if($wh)
                                        <div class="p-4 border-radius-10" style="background-color: #fffaf8; border: 1.5px solid #edd6d0;">
                                            <h5 class="mb-3 font-sm" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;">
                                                <i class="fi-rs-building mr-5"></i>Hub / warehouse
                                            </h5>
                                            <p class="font-xs text-muted mb-3">Data hub dari admin (stok &amp; pesanan masuk). Alamat pengiriman belanja di menu <strong>Kelola alamat</strong>.</p>
                                            <div class="row g-3 font-sm">
                                                <div class="col-md-12">
                                                    <span class="text-muted d-block font-xs">Nama hub</span>
                                                    <strong class="text-heading">{{ $wh->name }}</strong>
                                                </div>
                                                <div class="col-md-12">
                                                    <span class="text-muted d-block font-xs">Wilayah</span>
                                                    <strong class="text-heading">{{ $wh->full_location ?: '—' }}</strong>
                                                </div>
                                                @if($wh->address)
                                                    <div class="col-md-12">
                                                        <span class="text-muted d-block font-xs">Alamat</span>
                                                        <p class="mb-0 text-heading">{{ $wh->address }}</p>
                                                    </div>
                                                @endif
                                                @if($wh->postal_code)
                                                    <div class="col-md-6">
                                                        <span class="text-muted d-block font-xs">Kode pos</span>
                                                        <strong class="text-heading">{{ $wh->postal_code }}</strong>
                                                    </div>
                                                @endif
                                                @if($wh->phone)
                                                    <div class="col-md-6">
                                                        <span class="text-muted d-block font-xs">Telepon hub</span>
                                                        <strong class="text-heading">{{ $wh->phone }}</strong>
                                                    </div>
                                                @endif
                                            </div>
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
    .buyer-btn-maroon-outline {
        color: #6A1B1B !important;
        border: 2px solid #6A1B1B !important;
        background: transparent !important;
        font-weight: 600;
    }
    .buyer-btn-maroon-outline:hover {
        background: #6A1B1B !important;
        color: #fff !important;
    }
</style>
@endsection
