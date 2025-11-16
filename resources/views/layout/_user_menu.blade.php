{{-- resources/views/layout/_user_menu.blade.php --}}
<div class="dropdown ms-3">
  <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
    {{ auth()->user()->name ?? 'Usuário' }}
  </button>
  <ul class="dropdown-menu dropdown-menu-end">
    <li><a class="dropdown-item" href="{{ route('password.change') }}">Alterar senha</a></li>
    <li><hr class="dropdown-divider"></li>
    <li>
      <form method="POST" action="{{ route('logout') }}" class="px-3">
        @csrf
        <button class="btn btn-outline-danger w-100">Sair</button>
      </form>
    </li>
  </ul>
</div>
