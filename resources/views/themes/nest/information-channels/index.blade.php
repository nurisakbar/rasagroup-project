@extends('themes.nest.layouts.app')

@section('title', 'Saluran Informasi')

@section('content')
    <div class="page-header mt-30 mb-75 rg-archive-header-maroon">
        <div class="container">
            <div class="archive-header">
                <div class="row align-items-center">
                    <div class="col-xl-12">
                        <h1 class="mb-15">Saluran Informasi</h1>
                        <div class="breadcrumb">
                            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Halaman Utama</a>
                            <span></span> Saluran Informasi
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content pt-50 pb-50">
        <div class="container">
            <div class="section-title style-2 wow animate__animated animate__fadeIn mb-40">
                <h3>Pengumuman &amp; informasi</h3>
                <p class="mb-0">Berita dan kebijakan untuk mitra serta pelanggan Multi Citra Rasa.</p>
            </div>

            <div class="row">
                @forelse($channels as $channel)
                    <div class="col-lg-6 col-md-6 mb-30 wow animate__animated animate__fadeInUp" data-wow-delay="{{ min($loop->index, 5) * 0.1 }}s">
                        <article class="border-radius-10 border overflow-hidden h-100 hover-up" style="background: #fff;">
                            @if($channel->image_url)
                                <div class="post-thumb overflow-hidden">
                                    <a href="{{ route('information-channels.show', $channel->slug) }}">
                                        <img src="{{ $channel->image_url }}" alt="{{ $channel->title }}" class="w-100 d-block" style="height: 220px; object-fit: cover;" loading="lazy" />
                                    </a>
                                </div>
                            @endif
                            <div class="p-30">
                                <h4 class="mb-15">
                                    <a href="{{ route('information-channels.show', $channel->slug) }}" class="text-heading">{{ $channel->title }}</a>
                                </h4>
                                <p class="font-sm text-muted mb-20" style="line-height: 1.6;">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($channel->description ?? ''), 220) }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="font-xs text-muted"><i class="fi-rs-clock mr-5"></i>{{ $channel->created_at->format('d M Y') }}</span>
                                    <a href="{{ route('information-channels.show', $channel->slug) }}" class="btn btn-sm btn-default">Baca selengkapnya<i class="fi-rs-arrow-right ml-10"></i></a>
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted font-lg">Belum ada informasi yang tersedia.</p>
                        <a href="{{ route('home') }}" class="btn btn-sm mt-20">Kembali ke beranda <i class="fi-rs-arrow-small-right"></i></a>
                    </div>
                @endforelse
            </div>

            @if($channels->hasPages())
                <div class="pagination-area mt-30 mb-sm-5 mb-lg-0">
                    <nav aria-label="Navigasi halaman saluran informasi">
                        {{ $channels->links('vendor.pagination.nest') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
@endsection
