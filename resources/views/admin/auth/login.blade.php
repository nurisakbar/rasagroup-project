<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <title>Admin Login | {{ config('app.name', 'Laravel') }}</title>

  <!-- Bootstrap 3.4.1 -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/bootstrap/css/bootstrap.min.css') }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body.login-page {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background-image: url('https://www.rasagroup.co.id/assets/articles/RESIZED_SERVICES_TOP_IMAGES-min.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      position: relative;
    }
    
    body.login-page::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.4);
      z-index: 0;
    }
    
    .login-container {
      width: 100%;
      max-width: 420px;
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      padding: 40px;
      position: relative;
      z-index: 1;
    }
    
    .login-header {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .login-logo {
      margin-bottom: 20px;
    }
    
    .login-logo img {
      max-width: 120px;
      height: auto;
      display: inline-block;
    }
    
    .cf-turnstile-wrapper {
      display: flex;
      justify-content: center;
      margin: 20px 0;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #2d3748;
      margin-bottom: 8px;
    }
    
    .form-control {
      width: 100%;
      height: 48px;
      padding: 12px 16px;
      font-size: 14px;
      color: #2d3748;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      transition: all 0.2s ease;
      font-family: 'Inter', sans-serif;
    }
    
    .form-control:focus {
      outline: none;
      border-color: #3182ce;
      box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
    }
    
    .form-control::placeholder {
      color: #a0aec0;
    }
    
    .btn-login {
      width: 100%;
      height: 48px;
      background: #3182ce;
      color: #ffffff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      font-family: 'Inter', sans-serif;
      margin-top: 24px;
    }
    
    .btn-login:hover {
      background: #2c5282;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(49, 130, 206, 0.3);
    }
    
    .btn-login:active {
      transform: translateY(0);
    }
    
    .alert-danger {
      background: #fed7d7;
      color: #c53030;
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 14px;
      margin-bottom: 20px;
      border-left: 4px solid #c53030;
    }
    
    .alert-danger ul {
      margin: 0;
      padding-left: 20px;
    }
    
    .alert-danger li {
      margin-bottom: 4px;
    }
    
    .alert-danger li:last-child {
      margin-bottom: 0;
    }
    
    @media (max-width: 480px) {
      .login-container {
        padding: 30px 24px;
      }
      
      .login-logo img {
        max-width: 100px;
      }
    }
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-container">
  <div class="login-header">
    <div class="login-logo">
      <img src="https://storage.bkkbisa.net/company_logo/TCWIgRnCaBXaYQdZIoobV1y0sQb4l4.jpg" alt="Logo Perusahaan">
    </div>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.login') }}" method="post">
    @csrf
    <div class="form-group">
      <label class="form-label" for="email">Email</label>
      <input 
        type="email" 
        id="email"
        name="email" 
        class="form-control" 
        placeholder="Masukan email Anda" 
        value="{{ old('email') }}" 
        required 
        autofocus
      >
    </div>
    
    <div class="form-group">
      <label class="form-label" for="password">Password</label>
      <input 
        type="password" 
        id="password"
        name="password" 
        class="form-control" 
        placeholder="Masukan password Anda" 
        required
      >
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
    
    <button type="submit" class="btn-login">Login</button>
  </form>
</div>

@if(env('CLOUDFLARE_TURNSTILE_ENABLED', 'false') === 'true')
<!-- Cloudflare Turnstile -->
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif

<!-- jQuery 3.6.0 -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 3.4.1 -->
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
</body>
</html>








