<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Personalitec - Login</title>
    <link rel="icon" type="image/x-icon" href="/img/logo.ico">
    <link href="{{ asset('plugins/bootstrap/bootstrap.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet" />
  </head>
  <body>
    <div class="container">
      <div class="card card-login">
        <img src="{{ asset('img/logo.png') }}" class="card-img-top" alt="Personalitec">
        <div class="card-body">
          <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
              <div class="input-group">
                <span class="input-group-text bg-primary">
                  <i class="bi bi-person text-white"></i>
                </span>
                <div class="form-floating">
                  <input type="email" class="form-control" name="email" id="email" placeholder="Email" required autofocus>
                  <label for="email">Email</label>
                </div>
              </div>
              @error('email')
                <div class="custom-invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <div class="input-group">
                <span class="input-group-text bg-primary">
                  <i class="bi bi-key text-white"></i>
                </span>
                <div class="form-floating">
                  <input type="password" class="form-control" name="password" id="password" placeholder="Senha" required>
                  <label for="password">Senha</label>
                </div>
              </div>
              @error('password')
                <div class="custom-invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-check mb-3">
              <input type="checkbox" name="remember" id="remember" class="form-check-input" />
              <label for="remember" class="form-check-label">Lembrar de mim</label>
            </div>

            <div class="d-grid gap-2 col-6 mx-auto">
              <button type="submit" class="btn btn-primary">Entrar</button>
              <a class="btn btn-secondary w-100" href="{{ route('password.request') }}">Esqueci minha senha</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="{{ asset('plugins/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
  </body>
</html>
