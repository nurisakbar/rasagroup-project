@extends('themes.nest.layouts.app')

@section('title', 'Halaman Tidak Ditemukan - Rasa Group')

@section('content')
<main class="main page-404">
    <div class="page-content pt-100 pb-100" style="min-height: 70vh; display: flex; align-items: center;">
        <div class="container">
            <div class="row">
                <div class="col-xl-8 col-lg-10 col-md-12 m-auto text-center">
                    <h1 class="display-2 mb-10" style="font-size: 120px; color: #6B1D1D; font-weight: 900; line-height: 1;">404</h1>
                    <h2 class="mb-20" style="font-weight: 700;">Halaman Tidak Ditemukan</h2>
                    <p class="font-lg text-grey-700 mb-40">
                        Maaf, halaman yang Anda cari tidak dapat ditemukan.<br>
                        Halaman ini mungkin telah dihapus, diubah namanya, atau tidak tersedia sementara.
                    </p>
                    
                    <a href="{{ route('home') }}" class="btn" style="background-color: #6B1D1D; color: #FFFFFF; border-radius: 12px; font-weight: 700; padding: 15px 30px; border: none; display: inline-block;">
                        <i class="fi-rs-home mr-5"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
