@extends('themes.nest.layouts.app')

@section('title', $menu->nama_menu)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($menu->deskripsi ?? $menu->nama_menu), 160))

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span>
            <a href="{{ route('menus.index') }}">Menu paket</a>
            <span></span>
            {{ $menu->nama_menu }}
        </div>
    </div>
</div>
<section class="section-padding pt-40 pb-80">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 mb-lg-0 mb-40">
                @php
                    $menuHeroImg = $menu->image_url
                        ? (\Illuminate\Support\Str::startsWith($menu->image_url, ['http://', 'https://'])
                            ? $menu->image_url
                            : url($menu->image_url))
                        : null;
                @endphp
                @if($menuHeroImg)
                    <img src="{{ $menuHeroImg }}" alt="{{ $menu->nama_menu }}" class="border-radius-15 img-fluid w-100" style="object-fit: cover; max-height: 420px;">
                @else
                    <div class="border-radius-15 bg-light d-flex align-items-center justify-content-center" style="min-height: 280px;">
                        <span class="text-muted"><i class="fi-rs-picture fs-1"></i></span>
                    </div>
                @endif
            </div>
            <div class="col-lg-7 ps-lg-50">
                <h2 class="mb-15" style="color: #253D4E;">{{ $menu->nama_menu }}</h2>
                @if($menu->tampil_mulai || $menu->tampil_sampai)
                    <p class="font-sm text-muted mb-20">
                        @if($menu->tampil_mulai)
                            <span>Berlaku mulai {{ $menu->tampil_mulai->format('d M Y H:i') }}</span>
                        @endif
                        @if($menu->tampil_sampai)
                            <span class="ms-2">— s/d {{ $menu->tampil_sampai->format('d M Y H:i') }}</span>
                        @endif
                    </p>
                @endif
                <div class="font-md mb-30 menu-detail-description">{!! $menu->deskripsi !!}</div>

                <div class="border-top pt-25 mb-25">
                    <h5 class="mb-15">Komposisi</h5>
                    @if($selectedHub)
                        <p class="font-sm text-muted mb-15 mb-0">Stok tersedia di hub: <strong class="text-body">{{ $selectedHub->name }}</strong></p>
                    @else
                        <p class="font-sm text-muted mb-15 mb-0">Pilih hub pengirim di situs untuk menampilkan stok per produk di hub tersebut.</p>
                    @endif
                    <div class="table-responsive border-radius-10 border mt-15">
                        <table class="table table-striped mb-0 font-sm">
                            <thead>
                                <tr>
                                    <th class="ps-4">Produk</th>
                                    <th width="88" class="text-center">Qty</th>
                                    <th width="110" class="text-center">Stok</th>
                                    <th width="128" class="text-end pe-4">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($menu->details as $detail)
                                    @php
                                        $p = $detail->product;
                                        $unit = $p ? (float) $p->price : 0;
                                        $sub = $unit * (int) $detail->jumlah;
                                        $needQty = (int) $detail->jumlah;
                                        $stockAvail = ($p && $selectedHub) ? (int) $p->current_stock : null;
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            @if($p)
                                                <a href="{{ route('products.show', $p) }}" class="text-brand fw-semibold">{{ $p->display_name ?? $p->name }}</a>
                                            @else
                                                <span class="text-muted">Produk tidak tersedia</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ number_format($detail->jumlah) }}</td>
                                        <td class="text-center">
                                            @if($p && $selectedHub)
                                                @if($stockAvail >= $needQty)
                                                    <span class="text-success">{{ number_format($stockAvail) }}</span>
                                                @elseif($stockAvail > 0)
                                                    <span class="text-warning" title="Stok kurang dari kebutuhan menu">{{ number_format($stockAvail) }}</span>
                                                @else
                                                    <span class="text-danger">0</span>
                                                @endif
                                                @if($p->unit)
                                                    <span class="d-block font-xs text-muted">{{ $p->unit }}</span>
                                                @endif
                                            @elseif($p)
                                                <span class="text-muted">—</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">Rp {{ number_format($sub, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold bg-light">
                                    <td colspan="3" class="ps-4 text-end">Estimasi total paket</td>
                                    <td class="text-end pe-4 text-brand">Rp {{ number_format($menu->bundledPrice(), 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p class="font-xs text-muted mt-10 mb-0">Harga mengikuti master produk saat Anda memesan; estimasi ini untuk referensi.</p>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('menus.index') }}" class="btn btn-outline-secondary rounded-pill px-4"><i class="fi-rs-arrow-left mr-5"></i>Kembali</a>
                    <form action="{{ route('menus.add-composition-to-cart', $menu->slug) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn rounded-pill px-4 text-white border-0" style="background-color: #6A1B1B;"><i class="fi-rs-shopping-bag mr-5"></i>Belanja produk</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
