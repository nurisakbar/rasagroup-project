<div class="dashboard-menu">
    <ul class="nav flex-column" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ Route::is('buyer.dashboard') ? 'active' : '' }}" href="{{ route('buyer.dashboard') }}">
                <i class="fi-rs-settings-sliders mr-10"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('buyer.orders.*') ? 'active' : '' }}" href="{{ route('buyer.orders.index') }}">
                <i class="fi-rs-shopping-bag mr-10"></i>Pesanan Saya
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('buyer.addresses.*') ? 'active' : '' }}" href="{{ route('buyer.addresses.index') }}">
                <i class="fi-rs-marker mr-10"></i>Alamat Saya
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('buyer.profile') ? 'active' : '' }}" href="{{ route('buyer.profile') }}">
                <i class="fi-rs-user mr-10"></i>Detail Akun
            </a>
        </li>

        @if(Auth::user()->isDistributor())
            <li class="nav-item mt-3 mb-2">
                <span class="ps-3 font-xs text-muted text-uppercase fw-bold">Distributor</span>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('distributor.manage-orders.*') ? 'active' : '' }}" href="{{ route('distributor.manage-orders.index') }}">
                    <i class="fi-rs-shopping-bag mr-10"></i>Kelola Pesanan Masuk
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('distributor.stock.*') ? 'active' : '' }}" href="{{ route('distributor.stock.index') }}">
                    <i class="fi-rs-box mr-10"></i>Kelola Stock
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('distributor.pos.*') ? 'active' : '' }}" href="{{ route('distributor.pos.index') }}">
                    <i class="fi-rs-computer mr-10"></i>POS (Penjualan Toko)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('distributor.orders.products') || Route::is('distributor.orders.cart') || Route::is('distributor.orders.checkout') ? 'active' : '' }}" href="{{ route('distributor.orders.products') }}">
                    <i class="fi-rs-shopping-cart-check mr-10"></i>Restock Produk (Pusat)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('distributor.orders.history') || Route::is('distributor.orders.show') ? 'active' : '' }}" href="{{ route('distributor.orders.history') }}">
                    <i class="fi-rs-history mr-10"></i>Riwayat Order Pusat
                </a>
            </li>
        @endif

        <li class="nav-item mt-3">
            <form method="POST" action="{{ route('logout') }}" id="logout-form-sidebar">
                @csrf
                <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                    <i class="fi-rs-sign-out mr-10"></i>Keluar
                </a>
            </form>
        </li>
    </ul>
</div>
