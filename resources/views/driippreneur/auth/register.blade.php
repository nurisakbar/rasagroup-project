<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar DRiiPPreneur | {{ config('app.name', 'Laravel') }}</title>

  <!-- Bootstrap 3.4.1 -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/bootstrap/css/bootstrap.min.css') }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte/css/AdminLTE.min.css') }}">

  <style>
    body {
      background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
      min-height: 100vh;
    }
    .register-box {
      width: 450px;
      margin: 3% auto;
    }
    .register-logo a {
      color: #fff;
    }
    .register-box-body {
      border-radius: 10px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      padding: 30px;
    }
    .btn-purple {
      background-color: #9b59b6;
      border-color: #8e44ad;
      color: #fff;
    }
    .btn-purple:hover {
      background-color: #8e44ad;
      border-color: #7d3c98;
      color: #fff;
    }
    .benefits-box {
      background: rgba(255,255,255,0.1);
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      color: #fff;
    }
    .benefits-box h4 {
      margin-top: 0;
      color: #fff;
    }
    .benefits-box ul {
      padding-left: 20px;
      margin-bottom: 0;
    }
    .benefits-box li {
      margin-bottom: 8px;
    }
  </style>
</head>
<body class="hold-transition register-page">
<div class="register-box">
  <div class="register-logo">
    <a href="#"><b>DRiiP</b>Preneur</a>
  </div>

  <div class="benefits-box">
    <h4><i class="fa fa-star"></i> Keuntungan Menjadi DRiiPPreneur</h4>
    <ul>
      <li>Kelola stock produk sendiri</li>
      <li>Akses dashboard khusus</li>
      <li>Pantau penjualan real-time</li>
      <li>Dukungan dari tim kami</li>
    </ul>
  </div>

  <div class="register-box-body">
    <p class="login-box-msg"><strong>Daftar sebagai DRiiPPreneur</strong></p>

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul style="margin: 0; padding-left: 20px;">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('driippreneur.register') }}" method="POST">
      @csrf
      <div class="form-group has-feedback">
        <input type="text" name="name" class="form-control" placeholder="Nama Lengkap" value="{{ old('name') }}" required autofocus>
        <span class="fa fa-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required>
        <span class="fa fa-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="text" name="phone" class="form-control" placeholder="Nomor HP (contoh: 08123456789)" value="{{ old('phone') }}" required>
        <span class="fa fa-phone form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" name="password" class="form-control" placeholder="Password (minimal 8 karakter)" required>
        <span class="fa fa-lock form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi Password" required>
        <span class="fa fa-lock form-control-feedback"></span>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <button type="submit" class="btn btn-purple btn-block btn-lg btn-flat">
            <i class="fa fa-rocket"></i> Daftar Sekarang
          </button>
        </div>
      </div>
    </form>

    <hr>
    <p class="text-center">
      Sudah punya akun? <a href="{{ route('driippreneur.login') }}" style="color: #9b59b6; font-weight: bold;">Login di sini</a>
    </p>
  </div>
</div>

<!-- jQuery -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap -->
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>

