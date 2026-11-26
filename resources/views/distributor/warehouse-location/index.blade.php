@extends('layouts.shop')

@section('title', 'Lokasi Gudang')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> <a href="{{ route('distributor.profile') }}">Profil Distributor</a>
            <span></span> Lokasi Gudang
        </div>
    </div>
</div>

<div class="page-content account pt-50 pb-80" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-lg-8">
                <div class="card border-0 border-radius-20 overflow-hidden" style="background-color: #ffffff; border: 1px solid #E3D6C8 !important; box-shadow: 0 10px 30px rgba(106, 27, 27, 0.12);">
                    <div class="card-header bg-white border-bottom p-30 wl-card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="mb-0 wl-title" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Lokasi Gudang</h3>
                            <a href="{{ route('distributor.warehouse-location.create') }}" class="btn wl-add-btn"><i class="fi-rs-plus mr-5"></i>Tambah Gudang</a>
                        </div>
                        <p class="mb-0 mt-10 text-muted font-sm">Gudang adalah sumber pengiriman dan titik ambil barang oleh subdist Anda. Anda boleh punya lebih dari satu gudang, dan subdist memilihnya saat checkout. Perubahan langsung berlaku.</p>
                    </div>
                    <div class="card-body p-30 wl-card-body">
                        @if (session('success'))
                            <div class="alert alert-success border-0 border-radius-12 mb-25">{{ session('success') }}</div>
                        @endif

                        @forelse ($warehouses as $wh)
                            @php
                                $isPrimary = (string) $wh->id === (string) $primaryId;
                                $incomplete = blank($wh->district_id);
                            @endphp
                            <div class="wl-item mb-20 {{ $wh->is_active ? '' : 'wl-item-off' }}">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-10">
                                    <div>
                                        <strong style="font-size: 17px; color: #253D4E;">{{ $wh->name }}</strong>
                                        @if($isPrimary)<span class="wl-badge wl-badge-main">UTAMA</span>@endif
                                        <span class="wl-badge {{ $wh->is_active ? 'wl-badge-on' : 'wl-badge-off' }}">{{ $wh->is_active ? 'AKTIF' : 'NONAKTIF' }}</span>
                                    </div>
                                </div>
                                <p class="mb-5 font-sm" style="color:#5B6B7B;">{{ $wh->address ?: 'Alamat belum diisi' }}</p>
                                @if($wh->phone)<p class="mb-5 font-sm" style="color:#5B6B7B;"><i class="fi-rs-phone-call mr-5"></i>{{ $wh->phone }}</p>@endif
                                @if($incomplete)
                                    <p class="mb-10 font-sm text-danger"><i class="fi-rs-info mr-5"></i>Kecamatan belum diisi, gudang ini belum bisa dipilih subdist.</p>
                                @endif
                                <div class="d-flex flex-wrap gap-2 mt-10">
                                    <a href="{{ route('distributor.warehouse-location.edit', $wh->id) }}" class="btn wl-btn-sm wl-btn-main">Ubah</a>
                                    <a href="{{ route('distributor.stock.index', ['warehouse_id' => $wh->id]) }}" class="btn wl-btn-sm wl-btn-out">Atur Stok</a>
                                    <form action="{{ route('distributor.warehouse-location.toggle', $wh->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn wl-btn-sm wl-btn-out">{{ $wh->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Belum ada gudang. Klik "Tambah Gudang" untuk membuatnya.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .wl-title { font-size: clamp(20px, 5.5vw, 28px); line-height: 1.2; }
    .wl-add-btn { background: #6A1B1B !important; color: #fff !important; border: none !important; border-radius: 12px !important; height: 46px; padding: 0 20px !important; font-weight: 700; display: inline-flex !important; align-items: center; }
    .wl-item { border: 1.5px solid #E3D6C8; border-radius: 14px; padding: 18px; background: #FFFDFB; }
    .wl-item-off { background: #F6F3F0; opacity: .85; }
    .wl-badge { display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; margin-left: 6px; vertical-align: middle; }
    .wl-badge-main { background: #6A1B1B; color: #fff; }
    .wl-badge-on { background: #E7F4EC; color: #1E7B45; }
    .wl-badge-off { background: #ECECEC; color: #6B6B6B; }
    .wl-btn-sm { height: 40px; padding: 0 18px !important; border-radius: 10px !important; font-weight: 700; font-size: 14px; display: inline-flex !important; align-items: center; box-shadow: none !important; }
    .wl-btn-main { background: #6A1B1B !important; color: #fff !important; border: 2px solid #6A1B1B !important; }
    .wl-btn-out { background: #fff !important; color: #6A1B1B !important; border: 2px solid #6A1B1B !important; }
    @media (max-width: 575px) { .wl-card-header { padding: 18px !important; } .wl-card-body { padding: 18px 14px !important; } }
</style>
@endsection
