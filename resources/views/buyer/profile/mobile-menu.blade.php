@extends('layouts.shop')

@section('title', 'Menu Akun')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Akun
        </div>
    </div>
</div>

<div class="page-content pt-15 pb-150" style="background-color: #F2EAE1; min-height: 80vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                
                @if(auth()->user()->isDistributor())
                    {{-- Distributor: menu dikelompokkan per peran (penjual & pembeli), sama dengan sidebar desktop. --}}
                    @foreach(\App\Support\DistributorMenu::groups() as $group)
                        <div class="mobile-menu-heading {{ $loop->first ? 'mt-10' : 'mt-20' }}">
                            <span>{{ $group['title'] }}</span>
                            @if($group['hint'])<small>{{ $group['hint'] }}</small>@endif
                        </div>
                        <div class="mobile-menu-card">
                            <ul class="nav flex-column">
                                @foreach($group['items'] as $item)
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ $item['url'] }}">
                                            <i class="{{ $item['icon'] }} mr-15"></i>
                                            <span class="flex-grow-1">{{ $item['label'] }}</span>
                                            @if(! empty($item['badge']))
                                                <span class="mobile-menu-badge">{{ $item['badge'] }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                    <div class="mobile-menu-card mt-20">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('buyer.profile.password.edit') }}">
                                    <i class="fi-rs-lock mr-15"></i>Ubah Password
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="https://wa.me/6282151988440?text={{ urlencode('Halo Rasa Group, saya ingin bertanya') }}" target="_blank" rel="noopener noreferrer">
                                    <i class="fi-rs-headphones mr-15"></i>Hubungi CS
                                </a>
                            </li>
                            <li class="nav-item">
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-mobile-page">
                                    @csrf
                                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-mobile-page').submit();">
                                        <i class="fi-rs-sign-out mr-15"></i>Keluar
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                <div class="mobile-menu-card mt-10">
                    <ul class="nav flex-column" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('buyer.profile') }}">
                                <i class="fi-rs-user mr-15"></i>Profile Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('buyer.addresses.index') }}">
                                <i class="fi-rs-marker mr-15"></i>Alamat Pengiriman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('buyer.profile.password.edit') }}">
                                <i class="fi-rs-lock mr-15"></i>Ubah Password
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('buyer.affiliate.index') }}">
                                <i class="fi-rs-users mr-15"></i>Affiliasi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://wa.me/6282151988440?text={{ urlencode('Halo Rasa Group, saya ingin bertanya') }}" target="_blank" rel="noopener noreferrer">
                                <i class="fi-rs-headphones mr-15"></i>Hubungi CS
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" id="logout-form-mobile-page">
                                @csrf
                                <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-mobile-page').submit();">
                                    <i class="fi-rs-sign-out mr-15"></i>Keluar
                                </a>
                            </form>
                        </li>
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.mobile-menu-heading { padding: 0 6px 8px; font-family: 'Fira Sans', sans-serif; }
.mobile-menu-heading span { display: block; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #6A1B1B; }
.mobile-menu-heading small { display: block; font-size: 12px; color: #8A7B70; }
.mobile-menu-badge { min-width: 22px; height: 22px; padding: 0 7px; border-radius: 11px; background: #E53935; color: #fff; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
.mobile-menu-card {
    background-color: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}
.mobile-menu-card .nav-link {
    padding: 18px 20px;
    color: #253D4E;
    font-family: 'Fira Sans', sans-serif;
    font-weight: 600;
    font-size: 15px;
    display: flex;
    align-items: center;
    border-bottom: 1px solid #edf2f7;
    transition: background-color 0.2s ease;
}
.mobile-menu-card .nav-item:last-child .nav-link {
    border-bottom: none;
}
.mobile-menu-card .nav-link i {
    font-size: 22px;
    color: #7E7E7E;
}
.mobile-menu-card .nav-link:hover, .mobile-menu-card .nav-link:active {
    background-color: #f8f9fa;
    color: var(--primary-rasa, #6A1B1B);
}
.mobile-menu-card .nav-link:hover i, .mobile-menu-card .nav-link:active i {
    color: var(--primary-rasa, #6A1B1B);
}
.mobile-menu-card .nav-link.text-danger {
    color: #dc3545 !important;
}
.mobile-menu-card .nav-link.text-danger i {
    color: #dc3545 !important;
}
.mobile-menu-card .nav-link.text-danger:hover, .mobile-menu-card .nav-link.text-danger:active {
    background-color: #fff5f5;
}
</style>
@endsection
