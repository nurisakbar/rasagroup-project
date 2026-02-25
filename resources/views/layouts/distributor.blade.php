<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <title>@yield('title', 'Distributor Panel') | {{ config('app.name', 'Laravel') }}</title>

  <!-- Bootstrap 3.4.1 -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/bootstrap/css/bootstrap.min.css') }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte/css/AdminLTE.min.css') }}">
  <!-- AdminLTE Skins -->
  <link rel="stylesheet" href="{{ asset('adminlte/css/skins/skin-yellow.min.css') }}">
  
  @stack('styles')
</head>
<body class="hold-transition skin-yellow sidebar-mini">
<div class="wrapper">

  <!-- Main Header -->
  <header class="main-header">
    <!-- Logo -->
    <a href="{{ route('distributor.dashboard') }}" class="logo">
      <span class="logo-mini"><b>D</b>S</span>
      <span class="logo-lg"><b>Distributor</b></span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Points Display -->
          <li>
            <a href="{{ route('distributor.orders.products') }}" style="color: #f39c12;">
              <i class="fa fa-star"></i> <strong>{{ number_format(Auth::user()->points ?? 0) }}</strong> Poin
            </a>
          </li>
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="{{ asset('adminlte/img/user2-160x160.jpg') }}" class="user-image" alt="User Image">
              <span class="hidden-xs">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="{{ asset('adminlte/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image">
                <p>
                  {{ Auth::user()->name }}
                  <small>Distributor - {{ Auth::user()->warehouse->name ?? '' }}</small>
                </p>
              </li>
              <li class="user-footer">
                <div class="pull-left">
                  <a href="#" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <form method="POST" action="{{ route('distributor.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-default btn-flat">Sign out</button>
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
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="{{ asset('adminlte/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>{{ Auth::user()->name }}</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN MENU</li>
        <li class="{{ request()->routeIs('distributor.dashboard') ? 'active' : '' }}">
          <a href="{{ route('distributor.dashboard') }}">
            <i class="fa fa-dashboard"></i> <span>DASHBOARD</span>
          </a>
        </li>
        <li class="{{ request()->routeIs('distributor.stock.*') ? 'active' : '' }}">
          <a href="{{ route('distributor.stock.index') }}">
            <i class="fa fa-cubes"></i> <span>KELOLA STOCK</span>
          </a>
        </li>
        <li class="{{ request()->routeIs('distributor.manage-orders.*') ? 'active' : '' }}">
          <a href="{{ route('distributor.manage-orders.index') }}">
            <i class="fa fa-shopping-bag"></i> <span>KELOLA PESANAN</span>
          </a>
        </li>
        <li class="header">TRANSAKSI</li>
        <li class="{{ request()->routeIs('distributor.orders.products') || request()->routeIs('distributor.orders.cart') || request()->routeIs('distributor.orders.checkout') ? 'active' : '' }}">
          <a href="{{ route('distributor.orders.products') }}">
            <i class="fa fa-shopping-cart"></i> <span>ORDER PRODUK</span>
          </a>
        </li>
        <li class="{{ request()->routeIs('distributor.orders.history') || request()->routeIs('distributor.orders.show') ? 'active' : '' }}">
          <a href="{{ route('distributor.orders.history') }}">
            <i class="fa fa-history"></i> <span>RIWAYAT ORDER</span>
          </a>
        </li>
        <li class="{{ request()->routeIs('distributor.pos.*') ? 'active' : '' }}">
          <a href="{{ route('distributor.pos.index') }}">
            <i class="fa fa-cash-register"></i> <span>POINT OF SALES</span>
          </a>
        </li>
        <li class="header">NAVIGASI</li>
        <li>
          <a href="{{ route('buyer.dashboard') }}">
            <i class="fa fa-arrow-left"></i> <span>PANEL BUYER (TOKO)</span>
          </a>
        </li>
      </ul>
    </section>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        @yield('page-title', 'Dashboard')
        <small>@yield('page-description', 'Distributor Panel')</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ route('distributor.dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        @yield('breadcrumb')
      </ol>
    </section>

    <section class="content">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa fa-check"></i> Success!</h4>
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

      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa fa-ban"></i> Validation Error!</h4>
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @yield('content')
    </section>
  </div>

  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      Distributor Panel
    </div>
    <strong>Copyright &copy; 2025 <a href="#">{{ config('app.name', 'Laravel') }}</a>.</strong> All rights reserved.
  </footer>
</div>

<!-- jQuery -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap -->
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>

<!-- SweetAlert v1 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<script>
  $(document).ready(function() {
    // Global delete confirmation
    $(document).on('submit', '.delete-form', function(e) {
      e.preventDefault();
      var form = this;
      
      swal({
        title: "Apakah Anda yakin?",
        text: "Data yang dihapus tidak dapat dikembalikan!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Ya, hapus!",
        cancelButtonText: "Batal",
        closeOnConfirm: false
      }, function() {
        form.submit();
      });
    });

    // Success notification from session
    @if(session('success'))
      swal({
        title: "Berhasil!",
        text: "{{ session('success') }}",
        type: "success",
        timer: 3000,
        showConfirmButton: false
      });
    @endif

    // Error notification from session
    @if(session('error'))
      swal({
        title: "Gagal!",
        text: "{{ session('error') }}",
        type: "error"
      });
    @endif
  });
</script>

@stack('scripts')

</body>
</html>

