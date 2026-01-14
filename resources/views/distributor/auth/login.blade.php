<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Distributor Login | {{ config('app.name', 'Laravel') }}</title>

  <!-- Bootstrap 3.4.1 -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/bootstrap/css/bootstrap.min.css') }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte/css/AdminLTE.min.css') }}">

  <style>
    body {
      background-color: #f39c12;
      background-image: url('https://s3.bukalapak.com/attachment/398722/pengertian_distributor_image_2.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
      min-height: 100vh;
      position: relative;
    }
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.4);
      z-index: 0;
    }
    .login-box {
      margin-top: 5%;
      position: relative;
      z-index: 1;
    }
    .login-logo a {
      color: #fff;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }
    .login-box-body {
      border-radius: 10px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      background: rgba(255, 255, 255, 0.95);
    }
    .btn-orange {
      background-color: #f39c12;
      border-color: #e67e22;
      color: #fff;
    }
    .btn-orange:hover {
      background-color: #e67e22;
      border-color: #d35400;
      color: #fff;
    }
    .cf-turnstile-wrapper {
      display: flex;
      justify-content: center;
      margin: 20px 0;
    }
  </style>
</head>
<body class="hold-transition login-page" style="background-image: url('https://s3.bukalapak.com/attachment/398722/pengertian_distributor_image_2.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed;">
<div class="login-box">
  <div class="login-logo">
    <a href="#"><b>Distributor</b> Panel</a>
  </div>

  <div class="login-box-body">
    <p class="login-box-msg">Login ke akun Distributor Anda</p>

    @if ($errors->any())
      <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
          <p style="margin: 0;">{{ $error }}</p>
        @endforeach
      </div>
    @endif

    <form action="{{ route('distributor.login') }}" method="POST">
      @csrf
      <div class="form-group has-feedback">
        <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required autofocus>
        <span class="fa fa-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <span class="fa fa-lock form-control-feedback"></span>
      </div>
      
      @if(env('CLOUDFLARE_TURNSTILE_ENABLED', 'false') === 'true')
      <div class="cf-turnstile-wrapper">
        <div class="cf-turnstile" 
             data-sitekey="{{ env('CLOUDFLARE_TURNSTILE_SITE_KEY', '1x00000000000000000000AA') }}"
             data-callback="onTurnstileSuccess"
             data-error-callback="onTurnstileError"
             data-expired-callback="onTurnstileExpired"></div>
      </div>
      
      <input type="hidden" name="cf-turnstile-response" id="cf-turnstile-response">
      @endif
      
      <div class="row">
        <div class="col-xs-8">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="remember"> Ingat Saya
            </label>
          </div>
        </div>
        <div class="col-xs-4">
          <button type="submit" class="btn btn-orange btn-block btn-flat">Login</button>
        </div>
      </div>
    </form>

    <hr>
    <p class="text-center text-muted" style="font-size: 12px;">
      <i class="fa fa-info-circle"></i> Khusus untuk Distributor yang sudah disetujui
    </p>
    <p class="text-center" style="font-size: 12px;">
      Ingin menjadi distributor? <a href="{{ route('login') }}" style="color: #f39c12;">Daftar di sini</a>
    </p>
  </div>
</div>

@if(env('CLOUDFLARE_TURNSTILE_ENABLED', 'false') === 'true')
<!-- Cloudflare Turnstile -->
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif

<!-- jQuery -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap -->
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.min.js') }}"></script>

@if(env('CLOUDFLARE_TURNSTILE_ENABLED', 'false') === 'true')
<script>
  // Cloudflare Turnstile callbacks
  function onTurnstileSuccess(token) {
    document.getElementById('cf-turnstile-response').value = token;
  }
  
  function onTurnstileError() {
    document.getElementById('cf-turnstile-response').value = '';
  }
  
  function onTurnstileExpired() {
    document.getElementById('cf-turnstile-response').value = '';
  }
  
  // Prevent form submission if captcha is not completed
  window.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form').addEventListener('submit', function(e) {
      var captchaResponse = document.getElementById('cf-turnstile-response');
      if (captchaResponse && !captchaResponse.value) {
        e.preventDefault();
        alert('Harap selesaikan captcha terlebih dahulu');
        return false;
      }
    });
  });
</script>
@endif

<script>
  // Preload background image to ensure it loads
  (function() {
    var img = new Image();
    img.onload = function() {
      // Image loaded successfully
      document.body.style.backgroundImage = 'url("https://s3.bukalapak.com/attachment/398722/pengertian_distributor_image_2.jpg")';
    };
    img.onerror = function() {
      // If image fails to load, use fallback gradient
      document.body.style.backgroundImage = 'linear-gradient(135deg, #f39c12 0%, #e67e22 100%)';
      console.log('Background image failed to load, using fallback gradient');
    };
    img.src = 'https://s3.bukalapak.com/attachment/398722/pengertian_distributor_image_2.jpg';
  })();
</script>
</body>
</html>

