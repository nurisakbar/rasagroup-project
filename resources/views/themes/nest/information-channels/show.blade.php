@extends('themes.nest.layouts.app')

@section('content')
    <main class="main">
        <div class="page-header mt-30 mb-75 rg-archive-header-maroon">
            <div class="container">
                <div class="archive-header">
                    <div class="row align-items-center">
                        <div class="col-xl-12">
                            <h1 class="mb-15">{{ $channel->title }}</h1>
                            <div class="breadcrumb">
                                <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Halaman Utama</a>
                                <span></span> <a href="{{ route('information-channels.index') }}">Saluran Informasi</a>
                                <span></span> Detail
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-content mb-50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9 m-auto">
                        <div class="single-page">
                            <div class="single-header style-2">
                                <div class="row">
                                    <div class="col-xl-10 col-lg-12 m-auto">
                                        <h2 class="mb-10">{{ $channel->title }}</h2>
                                        <div class="single-header-meta">
                                            <div class="entry-meta meta-1 font-xs mt-15 mb-15">
                                                <span class="post-on has-dot"><i class="fi-rs-clock mr-5"></i> {{ $channel->created_at->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="single-content">
                                <div class="row">
                                    <div class="col-xl-10 col-lg-12 m-auto">
                                        @if($channel->image_url)
                                            <figure class="single-thumbnail mb-40 border-radius-15 overflow-hidden">
                                                <img src="{{ $channel->image_url }}" alt="{{ $channel->title }}" class="w-100" style="max-height: 420px; object-fit: cover;" loading="lazy" />
                                            </figure>
                                        @endif
                                        <div class="entry-main-content">
                                            {!! $channel->description !!}
                                        </div>
                                        
                                        <div class="entry-bottom mt-50 mb-30">
                                            <div class="social-icons single-share">
                                                <ul class="text-grey-5 d-inline-block">
                                                    <li><strong class="mr-10">Bagikan:</strong></li>
                                                    <li class="social-facebook"><a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-facebook.svg') }}" alt=""></a></li>
                                                    <li class="social-twitter"> <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($channel->title) }}" target="_blank"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-twitter.svg') }}" alt=""></a></li>
                                                    <li class="social-instagram"><a href="#"><img src="{{ asset('themes/nest-frontend/assets/imgs/theme/icons/icon-instagram.svg') }}" alt=""></a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- Comments Section -->
                                        <div class="comments-area mt-50 mb-30 border-top pt-30">
                                            <h4 class="mb-30">Komentar ({{ $channel->comments->count() }})</h4>
                                            <div class="comment-list">
                                                @forelse($channel->comments as $comment)
                                                    <div class="single-comment justify-content-between d-flex mb-30 pb-30 border-bottom">
                                                        <div class="user justify-content-between d-flex">
                                                            <div class="thumb mr-15">
                                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=3BB77E&color=fff" alt="" class="rounded-circle shadow-sm" style="width: 50px; height: 50px;">
                                                            </div>
                                                            <div class="desc">
                                                                <div class="d-flex justify-content-between mb-5">
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="font-heading text-brand mr-10 fw-bold">{{ $comment->user->name }}</span>
                                                                        <span class="font-xs text-muted">{{ $comment->created_at->diffForHumans() }}</span>
                                                                    </div>
                                                                </div>
                                                                <p class="mb-0 text-muted font-sm">{{ $comment->content }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-muted font-sm">Belum ada komentar. Jadilah yang pertama memberikan komentar!</p>
                                                @endforelse
                                            </div>
                                        </div>

                                        <div class="comment-form mt-50 mb-100">
                                            <h4 class="mb-15">Tinggalkan Komentar</h4>
                                            @auth
                                                <div class="row">
                                                    <div class="col-lg-12 col-md-12">
                                                        <form class="form-contact comment_form" action="{{ route('information-channels.comment', $channel->slug) }}" method="POST">
                                                            @csrf
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="form-group mb-3">
                                                                        <textarea class="form-control w-100 border-radius-10 p-3" name="content" cols="30" rows="5" placeholder="Tulis komentar Anda di sini..." required></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <button type="submit" class="btn btn-brand rounded-pill px-5">Kirim Komentar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="alert alert-info border-radius-10 p-3 d-flex align-items-center">
                                                    <i class="fi-rs-info mr-10"></i>
                                                    <span>Anda harus <a href="{{ route('login') }}" class="text-brand fw-bold">Masuk</a> terlebih dahulu untuk memberikan komentar.</span>
                                                </div>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
