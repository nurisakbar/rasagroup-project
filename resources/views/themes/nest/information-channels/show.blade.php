@extends('themes.nest.layouts.app')

@section('content')
    <main class="main">
        <div class="page-header mt-30 mb-75">
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
