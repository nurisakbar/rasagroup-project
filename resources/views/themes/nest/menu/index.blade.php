@extends('themes.nest.layouts.app')

@section('title', 'Menu Paket')
@section('meta_description', 'Paket menu pilihan Multi Citra Rasa — komposisi produk dan harga estimasi.')

@section('content')
    <div class="page-header mt-30 mb-75 rg-archive-header-maroon">
        <div class="container">
            <div class="archive-header">
                <div class="row align-items-center">
                    <div class="col-xl-12">
                        <h1 class="mb-15">Menu Paket</h1>
                        <div class="breadcrumb">
                            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Halaman Utama</a>
                            <span></span> Menu Paket
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content pt-50 pb-50">
        <div class="container">
            <div class="row mb-40">
                <div class="col-lg-8 col-md-7">
                    <div class="section-title style-2 wow animate__animated animate__fadeIn mb-0">
                        <h3>Daftar paket</h3>
                        <p class="mb-0">{{ $menus->total() }} menu tersedia.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 mt-30 mt-md-0">
                    <form action="{{ route('menus.index') }}" method="GET" class="d-flex w-100 align-items-stretch">
                        <input type="text" name="q" value="{{ $q }}" class="form-control mr-10 flex-grow-1" placeholder="Cari nama menu…" />
                        <button type="submit" class="btn btn-default" aria-label="Cari"><i class="fi-rs-search"></i></button>
                    </form>
                </div>
            </div>

            @if($menus->isEmpty())
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <p class="text-muted font-lg">
                            @if($q !== '')
                                Tidak ada menu yang cocok dengan pencarian tersebut.
                            @else
                                Belum ada menu yang ditampilkan saat ini. Silakan kembali lagi nanti.
                            @endif
                        </p>
                        <a href="{{ route('home') }}" class="btn btn-sm mt-20">Kembali ke beranda <i class="fi-rs-arrow-small-right"></i></a>
                    </div>
                </div>
            @else
                <div class="row">
                    @foreach($menus as $menu)
                        @include('themes.nest.partials.menu-card', [
                            'menu' => $menu,
                            'delay' => $loop->first ? '0' : '.' . min($loop->index % 5, 4) . 's',
                        ])
                    @endforeach
                </div>

                @if($menus->hasPages())
                    <div class="pagination-area mt-40 mb-sm-5 mb-lg-0">
                        <nav aria-label="Navigasi halaman menu">
                            {{ $menus->links('vendor.pagination.nest') }}
                        </nav>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
