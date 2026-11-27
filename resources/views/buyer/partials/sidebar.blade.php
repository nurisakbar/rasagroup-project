@php
    $isDistributor = Auth::user()->isDistributor();
    $isDistributorPage = request()->is('distributor*') || $isDistributor;
    $layout = $layout ?? 'vertical';
@endphp

<div class="dashboard-menu d-none d-lg-block {{ $layout === 'horizontal' ? 'dashboard-menu-horizontal mb-4' : '' }}">
    <ul class="nav {{ $layout === 'horizontal' ? 'flex-row' : 'flex-column' }}" role="tablist">
        @if($isDistributorPage)
            {{--
                Distributor berperan sebagai PENJUAL (ke outlet & subdist) dan PEMBELI (ke Rasa Group).
                Menu dikelompokkan per peran agar mudah dipahami. Kelompok & isinya ada di distributor-menu.
            --}}
            @foreach(\App\Support\DistributorMenu::groups() as $group)
                @if($layout === 'vertical')
                    <li class="nav-item dashboard-menu-heading {{ $loop->first ? '' : 'mt-3' }}">
                        <span>{{ $group['title'] }}</span>
                        @if($group['hint'])<small>{{ $group['hint'] }}</small>@endif
                    </li>
                @endif
                @foreach($group['items'] as $item)
                    <li class="nav-item">
                        <a class="nav-link {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}">
                            <i class="{{ $item['icon'] }} {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>
                            <span class="flex-grow-1">{{ $item['label'] }}</span>
                            @if(! empty($item['badge']))
                                <span class="dashboard-menu-badge">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            @endforeach
        @else
            {{-- BUYER MODE MENUS --}}
            <li class="nav-item">
                <a class="nav-link {{ Route::is('buyer.profile') ? 'active' : '' }}" href="{{ route('buyer.profile') }}">
                    <i class="fi-rs-user {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Akun Saya
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('buyer.orders.*') || Route::is('buyer.dashboard') ? 'active' : '' }}" href="{{ route('buyer.orders.index') }}">
                    <i class="fi-rs-shopping-bag {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Pesanan Saya
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('buyer.addresses.*') ? 'active' : '' }}" href="{{ route('buyer.addresses.index') }}">
                    <i class="fi-rs-marker {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Alamat Pengiriman
                </a>
            </li>
            @if($isDistributor)
            <li class="nav-item">
                <a class="nav-link {{ Route::is('buyer.profile.operational-hours') ? 'active' : '' }}" href="{{ route('buyer.profile.operational-hours') }}">
                    <i class="fi-rs-clock {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Jadwal Operasional
                </a>
            </li>
            @endif
            <li class="nav-item">
                <a class="nav-link {{ Route::is('buyer.affiliate.*') || Route::is('buyer.point-withdrawals.*') ? 'active' : '' }}" href="{{ route('buyer.affiliate.index') }}">
                    <i class="fi-rs-users {{ $layout === 'vertical' ? 'mr-10' : '' }}"></i>Program Afiliasi
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

    .dashboard-menu ul li.dashboard-menu-heading {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        border-radius: 0;
        margin-bottom: 0;
    }
    .dashboard-menu .dashboard-menu-heading {
        padding: 4px 20px 6px;
        font-family: 'Fira Sans', sans-serif;
    }
    .dashboard-menu .dashboard-menu-heading span {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #6A1B1B;
    }
    .dashboard-menu .dashboard-menu-heading small {
        display: block;
        font-size: 11px;
        color: #8A7B70;
        line-height: 1.3;
    }
    .dashboard-menu .dashboard-menu-badge {
        min-width: 22px;
        height: 22px;
        padding: 0 7px;
        border-radius: 11px;
        background: #E53935;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .dashboard-menu .nav-link.active .dashboard-menu-badge,
    .dashboard-menu .nav-link:hover .dashboard-menu-badge {
        background: #fff;
        color: #6A1B1B;
    }
</style>
