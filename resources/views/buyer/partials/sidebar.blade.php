@php
    $isDistributor = Auth::user()->isDistributor();
    $isDistributorPage = request()->is('distributor*') || $isDistributor;
    $layout = $layout ?? 'vertical';
@endphp

<div class="dashboard-menu {{ $layout === 'horizontal' ? 'dashboard-menu-horizontal mb-4' : '' }}">
    <ul class="nav {{ $layout === 'horizontal' ? 'flex-row' : 'flex-column' }}" role="tablist">
        @if($isDistributorPage)
            {{-- DISTRIBUTOR MODE MENUS --}}
            @if($layout === 'vertical')
                <li class="nav-item">
                    <span class="ps-3 font-xs text-muted text-uppercase fw-bold mb-2 d-block">Distributor Mode</span>
                </li>
            @endif
            <li class="nav-item">
                <a class="nav-link {{ Route::is('distributor.manage-orders.*') ? 'active' : '' }}" href="{{ route('distributor.manage-orders.index') }}">
                    <i class="fi-rs-shopping-bag {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>{{ $layout === 'horizontal' ? 'Pesanan Masuk' : 'Pesanan Masuk' }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('distributor.stock.*') ? 'active' : '' }}" href="{{ route('distributor.stock.index') }}">
                    <i class="fi-rs-box {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Stok
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('distributor.pos.*') ? 'active' : '' }}" href="{{ route('distributor.pos.index') }}">
                    <i class="fi-rs-computer {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>POS
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('distributor.orders.products') || Route::is('distributor.orders.cart') || Route::is('distributor.orders.checkout') ? 'active' : '' }}" href="{{ route('distributor.orders.products') }}">
                    <i class="fi-rs-shopping-cart-check {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Restock
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('distributor.orders.history') || Route::is('distributor.orders.show') ? 'active' : '' }}" href="{{ route('distributor.orders.history') }}">
                    <i class="fi-rs-history {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Riwayat
                </a>
            </li>
            @if(!$isDistributor)
                <li class="nav-item {{ $layout === 'vertical' ? 'mt-3' : '' }}">
                    <a class="nav-link mode-switch buyer-mode text-brand fw-bold" href="{{ route('buyer.dashboard') }}">
                        <i class="fi-rs-user {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i> {{ $layout === 'horizontal' ? 'Mode Pembeli' : 'Mode Pembeli' }}
                    </a>
                </li>
            @endif
        @else
            {{-- BUYER MODE MENUS --}}
            <li class="nav-item">
                <a class="nav-link {{ Route::is('buyer.dashboard') ? 'active' : '' }}" href="{{ route('buyer.dashboard') }}">
                    <i class="fi-rs-settings-sliders {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('buyer.orders.*') ? 'active' : '' }}" href="{{ route('buyer.orders.index') }}">
                    <i class="fi-rs-shopping-bag {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Pesanan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('buyer.addresses.*') ? 'active' : '' }}" href="{{ route('buyer.addresses.index') }}">
                    <i class="fi-rs-marker {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Alamat
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('buyer.profile') ? 'active' : '' }}" href="{{ route('buyer.profile') }}">
                    <i class="fi-rs-user {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Profil
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('buyer.affiliate.*') || Route::is('buyer.point-withdrawals.*') ? 'active' : '' }}" href="{{ route('buyer.affiliate.index') }}">
                    <i class="fi-rs-users {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Afiliasi
                </a>
            </li>
            @if($isDistributor)
                <li class="nav-item {{ $layout === 'vertical' ? 'mt-3' : '' }}">
                    <a class="nav-link mode-switch distributor-mode text-warning fw-bold" href="{{ route('distributor.manage-orders.index') }}">
                        <i class="fi-rs-marker {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i> Mode Distributor
                    </a>
                </li>
            @endif
        @endif

        <li class="nav-item {{ $layout === 'vertical' ? 'mt-3' : 'ms-auto' }}">
            <form method="POST" action="{{ route('logout') }}" id="logout-form-sidebar">
                @csrf
                <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                    <i class="fi-rs-sign-out {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Keluar
                </a>
            </form>
        </li>
    </ul>
</div>

<style>
    .dashboard-menu {
        background: #fff;
        border-radius: 20px;
        padding: 30px 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        border: 1px solid #edf2f7;
    }
    .dashboard-menu .nav-link {
        border: 1px solid transparent;
        border-radius: 12px;
        margin-bottom: 8px;
        padding: 14px 20px;
        color: #253D4E;
        font-family: 'Fira Sans', sans-serif;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }
    .dashboard-menu .nav-link:hover, .dashboard-menu .nav-link.active {
        background-color: #6A1B1B !important;
        color: #fff !important;
        border-color: #6A1B1B !important;
        box-shadow: 0 8px 20px rgba(106, 27, 27, 0.2);
    }
    .dashboard-menu .nav-link i {
        margin-right: 12px;
        font-size: 18px;
    }
    .dashboard-menu .nav-link.mode-switch {
        border: 2px dashed;
    }
    .dashboard-menu .nav-link.distributor-mode {
        border-color: #ff9800;
        color: #ff9800 !important;
    }
    .dashboard-menu .nav-link.distributor-mode:hover {
        background-color: #ff9800 !important;
        color: #fff !important;
        border-color: #ff9800 !important;
        box-shadow: 0 8px 20px rgba(255, 152, 0, 0.2);
    }

    /* Horizontal Specific Styles */
    .dashboard-menu-horizontal {
        padding: 10px;
    }
    .dashboard-menu-horizontal ul.nav {
        flex-wrap: wrap;
        gap: 8px;
    }
    .dashboard-menu-horizontal .nav-item {
        margin-bottom: 0 !important;
    }
    .dashboard-menu-horizontal .nav-link {
        margin-bottom: 0 !important;
        padding: 10px 18px;
        font-size: 13px;
    }
</style>
