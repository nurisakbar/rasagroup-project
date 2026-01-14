<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hub Login | {{ config('app.name', 'Laravel') }}</title>

  <!-- Bootstrap 3.4.1 -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/bootstrap/css/bootstrap.min.css') }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte/css/AdminLTE.min.css') }}">

  <style>
    body {
      background: linear-gradient(135deg, #00a65a 0%, #00875a 100%);
      min-height: 100vh;
    }
    .login-box {
      margin-top: 5%;
    }
    .login-logo a {
      color: #fff;
    }
    .login-box-body {
      border-radius: 5px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="#"><b>Hub</b> Panel</a>
  </div>

  <div class="login-box-body">
    <p class="login-box-msg">Login untuk mengelola hub Anda</p>

    @if ($errors->any())
      <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
          <p style="margin: 0;">{{ $error }}</p>
        @endforeach
      </div>
    @endif

    <form action="{{ route('warehouse.login') }}" method="POST">
      @csrf
      <div class="form-group has-feedback">
        <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required autofocus>
        <span class="fa fa-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <span class="fa fa-lock form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-8">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="remember"> Ingat Saya
            </label>
          </div>
        </div>
        <div class="col-xs-4">
          <button type="submit" class="btn btn-success btn-block btn-flat">Login</button>
        </div>
      </div>
    </form>

    <hr>
    <p class="text-center text-muted" style="font-size: 12px;">
      <i class="fa fa-info-circle"></i> Khusus untuk staff hub
    </p>
  </div>
</div>

<!-- jQuery -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap -->
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
