@extends('layout.master')
@section('title','Alterar Senha')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h5 class="mb-3">Alterar Senha</h5>

        @if (session('status'))
          <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.update.manual') }}">
          @csrf

          <div class="mb-3">
            <label class="form-label">Senha atual</label>
            <input type="password" name="current_password"
                   class="form-control @error('current_password') is-invalid @enderror" required>
            @error('current_password') <div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Nova senha</label>
            <input type="password" name="new_password"
                   class="form-control @error('new_password') is-invalid @enderror" required>
            @error('new_password') <div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Confirmar nova senha</label>
            <input type="password" name="new_password_confirmation" class="form-control" required>
          </div>

          <div class="d-grid">
            <button class="btn btn-primary">Salvar nova senha</button>
          </div>

          <a href="{{ route('home') }}" class="d-block text-center mt-3">Voltar</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
