{{-- resources/views/layout/master.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Portal Personalitec @yield('title', '')</title>
    <link rel="icon" type="image/x-icon" href="/img/logo.ico">

    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('plugins/bootstrap/bootstrap.min.css') }}" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables/datatables.min.css') }}">

    <!-- Design Tokens (CSS Variables) -->
    <link href="{{ asset('css/design-tokens.css') }}" rel="stylesheet" />

    <!-- Reusable Components -->
    <link href="{{ asset('css/components.css') }}" rel="stylesheet" />

    <!-- Custom App CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
    @stack('styles')
</head>
<body>
<main id="main-wrapper">

    <aside class="left-sidebar">
        <div class="banner text-center">
            <a href="{{ url('/') }}">
                <img src="{{ asset('img/logo.png') }}" height="120px" alt="Personalitec" />
            </a>
        </div>
        <div class="sidebar-menu">
            @php($u = Auth::user())
            <ul>
                @if ($u && ($u->papel === 'consultor' || $u->papel === 'admin'))
                <li class="sidebar-item {{ (Request::is('ordem-servico') ? 'active' : '') }}">
                    <a class="sidebar-link" href="{{ url('/ordem-servico') }}">Ordem de Servi&ccedil;o</a>
                </li>
                @endif

                @if ($u && ($u->papel === 'financeiro' || $u->papel === 'admin'))
                <li class="sidebar-item {{ (Request::is('faturamento') ? 'active' : '') }}">
                    <a class="sidebar-link" href="{{ url('/faturamento') }}">Faturamento</a>
                </li>
                <li class="sidebar-item {{ (Request::is('recibo-provisorio') ? 'active' : '') }}">
                    <a class="sidebar-link" href="{{ url('/recibo-provisorio') }}">Recibo Provis&oacute;rio</a>
                </li>
                <li class="sidebar-item {{ (Request::is('relatorio-fechamento') ? 'active' : '') }}">
                    <a class="sidebar-link" href="{{ url('/relatorio-fechamento') }}">Fechamento Consultores</a>
                </li>
                @endif

                @if ($u && $u->papel === 'admin')
                <li class="sidebar-item {{ (Request::is('dashboard-gerencial') ? 'active' : '') }}">
                    <a class="sidebar-link" href="{{ url('/dashboard-gerencial') }}"><i class="bi bi-graph-up me-2"></i>Dashboard Gerencial</a>
                </li>
                <li class="sidebar-item {{ (Request::is('projetos*') ? 'active' : '') }}">
                    <a class="sidebar-link" href="{{ url('/projetos') }}">Projetos</a>
                </li>
                <li class="sidebar-item">
                    <span class="sidebar-cap">Cadastros</span>
                </li>
                <li class="sidebar-item {{ (Request::is('cadastros/usuarios') ? 'active' : '') }}">
                    <a class="sidebar-link" href="{{ url('/cadastros/usuarios') }}">Usu&aacute;rios</a>
                </li>
                <li class="sidebar-item {{ (Request::is('cadastros/clientes') ? 'active' : '') }}">
                    <a class="sidebar-link" href="{{ url('/cadastros/clientes') }}">Clientes</a>
                </li>
                <li class="sidebar-item {{ (Request::is('cadastros/fornecedores') ? 'active' : '') }}">
                    <a class="sidebar-link" href="{{ url('/cadastros/fornecedores') }}">Fornecedores</a>
                </li>
                <li class="sidebar-item {{ (Request::is('cadastros/produtos') ? 'active' : '') }}">
                    <a class="sidebar-link" href="{{ url('/cadastros/produtos') }}">Produtos</a>
                </li>
                <li class="sidebar-item {{ (Request::is('cadastros/tabela-precos') ? 'active' : '') }}">
                    <a class="sidebar-link" href="{{ url('/cadastros/tabela-precos') }}">Tabela de Pre&ccedil;os</a>
                </li>
                <li class="sidebar-item {{ (Request::is('cadastros/condicoes-pagamento') ? 'active' : '') }}">
                    <a class="sidebar-link" href="{{ url('/cadastros/condicoes-pagamento') }}">Condi&ccedil;&otilde;es de Pagamento</a>
                </li>
                @endif
            </ul>
        </div>
    </aside>

    <header class="topbar">
        <nav class="navbar navbar-expand-lg top-navbar">
            <div class="container-fluid">
                <!-- Hamburger Menu Toggle for Mobile -->
                <button class="navbar-toggler d-lg-none" type="button" id="sidebarToggle" aria-label="Menu">
                    <i class="bi bi-list"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarScroll">
                    <div class="navbar-nav ms-auto text-white">Ol&aacute; {{ Auth::user()->name ?? 'Usu&aacute;rio' }}</div>

                    <ul class="navbar-nav my-2 my-lg-0" style="--bs-scroll-height: 100px;">
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a class="dropdown-item btn-redefinir-senha" href="javascript:void(0)">Redefinir senha</a></li>
                                <li>
                                    <form method="POST" action="{{ url('logout') }}">
                                        @csrf
                                        <button class="dropdown-item text-danger">Sair</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>

                </div>
            </div>
        </nav>
    </header>

    <div class="page-wrapper">
        <div class="container-fluid">
            @yield('content')
        </div>

        <footer class="footer text-center">
            &copy;2025 Personalitec Solu&ccedil;&otilde;es
        </footer>
    </div>

</main>

<!-- Scripts -->
<script src="{{ asset('plugins/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('plugins/jquery-mask-plugin/jquery.mask.min.js') }}"></script>

<!-- DataTables JS -->
<script src="{{ asset('plugins/datatables/datatables.min.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/permissoes-filtro.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>

@stack('scripts')
@yield('modal')

</body>
</html>
