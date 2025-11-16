@extends('layout.master')
@section('title','- Definir nova senha')
@section('content')
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="mb-3">Definir nova senha</h5>
        <form method="POST" action="{{ route('password.update') }}">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" required value="{{ request('email') }}">
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror

          <label class="form-label mt-3">Nova senha</label>
          <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
          @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror

          <label class="form-label mt-3">Confirmar senha</label>
          <input type="password" name="password_confirmation" class="form-control" required>

          <div class="d-grid mt-3">
            <button class="btn btn-success">Salvar nova senha</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
