@extends('layout.master')
@section('title','- Esqueci minha senha')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h5 class="mb-3">Recuperar senha</h5>

        @if (session('status'))
          <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="d-grid">
            <button class="btn btn-primary">Enviar link de redefinição</button>
          </div>
          <a class="d-block text-center mt-3" href="{{ route('login') }}">Voltar ao login</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
