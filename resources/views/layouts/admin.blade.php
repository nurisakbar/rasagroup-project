<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <title>@yield('title', 'Admin Dashboard') | {{ config('app.name', 'Rasa Group') }}</title>

  <!-- Bootstrap 3.4.1 -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/bootstrap/css/bootstrap.min.css') }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte/css/AdminLTE.min.css') }}">
  <!-- AdminLTE Skins. Choose a skin from the css/skins folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="{{ asset('adminlte/css/skins/skin-blue.min.css') }}">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
  
  <style>
    :root {
      --rasa-maroon: rgba(111, 23, 21, 1);
      --rasa-maroon-dark: rgba(89, 18, 17, 1);
    }

    /* Override AdminLTE skin-blue header to Rasa maroon */
    .skin-blue .main-header .navbar,
    .skin-blue .main-header .logo {
      background-color: var(--rasa-maroon) !important;
    }

    .skin-blue .main-header .logo:hover,
    .skin-blue .main-header .navbar .sidebar-toggle:hover {
      background-color: var(--rasa-maroon-dark) !important;
    }

    .skin-blue .main-header .navbar .nav > li > a:hover,
    .skin-blue .main-header .navbar .nav > li > a:active,
    .skin-blue .main-header .navbar .nav > li > a:focus,
    .skin-blue .main-header .navbar .nav .open > a,
    .skin-blue .main-header .navbar .nav .open > a:hover,
    .skin-blue .main-header .navbar .nav .open > a:focus,
    .skin-blue .main-header .navbar .nav > .active > a {
      background: rgba(0, 0, 0, 0.12) !important;
      color: #fff !important;
    }

    .skin-blue .main-header li.user-header {
      background-color: var(--rasa-maroon) !important;
    }
  </style>

  @stack('styles')
  
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <!-- Main Header -->
  <header class="main-header">
    <!-- Logo -->
    <a href="{{ route('admin.dashboard') }}" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><img src="{{ asset('logorasa.png') }}" style="max-height: 40px; max-width: 40px;" alt="Logo"></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><img src="{{ asset('logorasa.png') }}" style="max-height: 40px;" alt="Logo"></span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>
      <!-- Navbar Right Menu -->
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- User Account Menu -->
          <li class="dropdown user user-menu">
            <!-- Menu Toggle Button -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <!-- The user image in the navbar-->
              <img src="{{ asset('adminlte/img/user2-160x160.jpg') }}" class="user-image" alt="User Image">
              <!-- hidden-xs hides the username on small devices so only the image appears. -->
              <span class="hidden-xs">{{ Auth::user()->name ?? 'Admin' }}</span>
            </a>
            <ul class="dropdown-menu">
              <!-- The user image in the menu -->
              <li class="user-header">
                <img src="{{ asset('adminlte/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image">
                <p>
                  {{ Auth::user()->name ?? 'Administrator' }} - {{ Auth::user()->role_label }}
                  <small>Member since {{ Auth::user()->created_at->format('M Y') ?? 'Jan 2025' }}</small>
                </p>
              </li>
              <!-- Menu Body -->
              <li class="user-body">
                <div class="row">
                  <div class="col-xs-4 text-center">
                    <a href="#">Followers</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="#">Sales</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="#">Friends</a>
                  </div>
                </div>
                <!-- /.row -->
              </li>
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="{{ route('admin.profile.edit') }}" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <form method="POST" action="{{ route('logout') }}">
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
  
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="{{ asset('adminlte/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>{{ Auth::user()->name ?? 'Admin' }}</p>
          <a href="#"><i class="fa fa-circle text-success"></i> {{ Auth::user()->role_label }}</a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN MENU</li>
        @php
            // Menu dibangun dari modul di database (menu Hak Akses): hanya modul yang boleh dilihat role ini.
            $adminMenu = \App\Support\AdminAccess\AdminAccess::menuFor(auth()->user());
            $currentModule = request()->route()?->getName()
                ? \App\Support\AdminAccess\AdminAccess::moduleKeyForRoute(request()->route()->getName())
                : null;
            $renderedGroups = [];
        @endphp

        <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <a href="{{ route('admin.dashboard') }}">
            <i class="fa fa-dashboard"></i> <span>DASHBOARD</span>
          </a>
        </li>

        @foreach($adminMenu as $item)
          @if(!empty($item['group']))
            @continue(in_array($item['group'], $renderedGroups, true))
            @php
                $renderedGroups[] = $item['group'];
                $groupItems = array_values(array_filter($adminMenu, fn ($x) => ($x['group'] ?? null) === $item['group']));
                $groupActive = in_array($currentModule, array_column($groupItems, 'key'), true);
            @endphp
            <li class="treeview {{ $groupActive ? 'active menu-open' : '' }}">
              <a href="#">
                <i class="fa {{ $groupItems[0]['icon'] ?? 'fa-cube' }}"></i> <span>{{ $item['group'] }}</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                @foreach($groupItems as $sub)
                  <li class="{{ $currentModule === $sub['key'] ? 'active' : '' }}">
                    <a href="{{ route($sub['menu_route']) }}"><i class="fa {{ $sub['icon'] }}"></i> {{ $sub['name'] }}</a>
                  </li>
                @endforeach
              </ul>
            </li>
          @else
            <li class="{{ $currentModule === $item['key'] ? 'active' : '' }}">
              <a href="{{ route($item['menu_route']) }}">
                <i class="fa {{ $item['icon'] }}"></i> <span>{{ $item['name'] }}</span>
              </a>
            </li>
          @endif
        @endforeach
      </ul>
      <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        @yield('page-title', 'Dashboard')
        <small>@yield('page-description', 'Control panel')</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        @yield('breadcrumb')
      </ol>
    </section>

    <!-- Main content -->
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
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <!-- To the right -->
    <div class="pull-right hidden-xs">
      Version 1.0
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; 2025 <a href="#">{{ config('app.name', 'Rasa Group') }}</a>.</strong> All rights reserved.
  </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery 3.6.0 -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 3.4.1 -->
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<!-- Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
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

@include('admin.partials.sync-progress-modal')

@stack('scripts')

</body>
</html>

