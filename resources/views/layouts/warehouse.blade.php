<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <title>@yield('title', 'Hub Panel') | {{ config('app.name', 'Laravel') }}</title>

  <!-- Bootstrap 3.4.1 -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/bootstrap/css/bootstrap.min.css') }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte/css/AdminLTE.min.css') }}">
  <!-- AdminLTE Skins -->
  <link rel="stylesheet" href="{{ asset('adminlte/css/skins/skin-green.min.css') }}">
  
  @stack('styles')
</head>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <!-- Main Header -->
  <header class="main-header">
    <!-- Logo -->
    <a href="{{ route('warehouse.dashboard') }}" class="logo">
      <span class="logo-mini"><b>H</b>UB</span>
      <span class="logo-lg"><b>Hub</b> Panel</span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="{{ asset('adminlte/img/avatar5.png') }}" class="user-image" alt="User Image">
              <span class="hidden-xs">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="{{ asset('adminlte/img/avatar5.png') }}" class="img-circle" alt="User Image">
                <p>
                  {{ Auth::user()->name }}
                  <small>{{ Auth::user()->warehouse->name ?? 'Hub Staff' }}</small>
                </p>
              </li>
              <li class="user-footer">
                <div class="pull-right">
                  <form method="POST" action="{{ route('warehouse.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-default btn-flat">Logout</button>
                  </form>
                </div>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>

  <!-- Left side column -->
  <aside class="main-sidebar">
    <section class="sidebar">
      <!-- User Panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="{{ asset('adminlte/img/avatar5.png') }}" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>{{ Auth::user()->name }}</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>

      <!-- Warehouse Info -->
      <div class="sidebar-form" style="padding: 10px; background: rgba(0,0,0,0.1); margin: 10px;">
        <div style="color: #b8c7ce; font-size: 11px;">WAREHOUSE</div>
        <div style="color: #fff; font-weight: bold;">{{ Auth::user()->warehouse->name ?? '-' }}</div>
        <div style="color: #b8c7ce; font-size: 11px;">{{ Auth::user()->warehouse->full_location ?? '' }}</div>
      </div>

      <!-- Sidebar Menu -->
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN MENU</li>
        <li class="{{ request()->routeIs('warehouse.dashboard') ? 'active' : '' }}">
          <a href="{{ route('warehouse.dashboard') }}">
            <i class="fa fa-dashboard"></i> <span>DASHBOARD</span>
          </a>
        </li>
        <li class="{{ request()->routeIs('warehouse.stock.*') ? 'active' : '' }}">
          <a href="{{ route('warehouse.stock.index') }}">
            <i class="fa fa-cubes"></i> <span>KELOLA STOCK</span>
          </a>
        </li>
        <li class="{{ request()->routeIs('warehouse.orders.*') ? 'active' : '' }}">
          <a href="{{ route('warehouse.orders.index') }}">
            <i class="fa fa-shopping-cart"></i> <span>KELOLA PESANAN</span>
          </a>
        </li>
      </ul>
    </section>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
      <h1>
        @yield('page-title', 'Dashboard')
        <small>@yield('page-description', '')</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ route('warehouse.dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        @yield('breadcrumb')
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa fa-check"></i> Sukses!</h4>
          {{ session('success') }}
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa fa-ban"></i> Error!</h4>
          {{ session('error') }}
        </div>
      @endif

      @if(session('info'))
        <div class="alert alert-info alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa fa-info"></i> Info</h4>
          {{ session('info') }}
        </div>
      @endif

      @yield('content')
    </section>
  </div>

  <!-- Footer -->
  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      Warehouse Panel v1.0
    </div>
    <strong>Copyright &copy; 2025 {{ config('app.name', 'Laravel') }}.</strong>
  </footer>
</div>

<!-- jQuery -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap -->
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>

@stack('scripts')

</body>
</html>

