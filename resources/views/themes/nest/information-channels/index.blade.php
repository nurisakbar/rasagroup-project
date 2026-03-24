@extends('themes.nest.layouts.app')

@section('content')
    <main class="main">
        <div class="page-header mt-30 mb-75">
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
        <div class="page-content mb-50">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8 col-lg-10 col-md-12 m-auto">
                        <div class="loop-grid loop-big">
                            <div class="row">
                                @forelse($channels as $channel)
                                    <article class="first-post mb-30 hover-up animated" style="visibility: visible">
                                        <div class="entry-content">
                                            <h2 class="post-title mb-20">
                                                <a href="{{ route('information-channels.show', $channel->slug) }}">{{ $channel->title }}</a>
                                            </h2>
                                            <div class="post-exerpt font-medium text-muted mb-30">
                                                {!! Str::limit(strip_tags($channel->description), 300) !!}
                                            </div>
                                            <div class="mb-20 entry-meta meta-2">
                                                <div class="entry-meta meta-1 mb-30">
                                                    <div class="font-sm">
                                                        <span class="post-on"><i class="fi-rs-clock"></i> {{ $channel->created_at->format('d M Y') }}</span>
                                                    </div>
                                                </div>
                                                <a href="{{ route('information-channels.show', $channel->slug) }}" class="btn btn-sm">Baca Selengkapnya<i class="fi-rs-arrow-right ml-10"></i></a>
                                            </div>
                                        </div>
                                    </article>
                                @empty
                                    <div class="col-12 text-center">
                                        <p>Belum ada informasi yang tersedia.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        <div class="pagination-area mt-15 mb-sm-5 mb-lg-0">
                            {{ $channels->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
