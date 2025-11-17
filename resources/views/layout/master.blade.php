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
                        <!-- Notifications Bell Icon -->
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificationsBell">
                                <i class="bi bi-bell-fill"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display: none;">
                                    0
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown" id="notificationDropdown">
                                <li class="dropdown-header">
                                    <span>Notificações</span>
                                    <button class="btn btn-sm btn-link text-secondary" id="markAllReadBtn" style="display: none;">Marcar como lida</button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <div id="notificationsList" style="max-height: 400px; overflow-y: auto;">
                                    <li class="text-center text-muted py-3">Carregando...</li>
                                </div>
                                <li><hr class="dropdown-divider"></li>
                                <li class="text-center">
                                    <a href="javascript:void(0)" class="text-decoration-none text-primary" id="seeAllNotificationsBtn">Ver todas as notificações</a>
                                </li>
                            </ul>
                        </li>

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

<script>
    // Notification System
    let notificationRefreshInterval;

    function loadNotifications() {
        fetch('/api/notifications/unread')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = data.count;
                    const notifications = data.notifications;

                    // Update badge
                    const badge = document.getElementById('notificationBadge');
                    if (count > 0) {
                        badge.textContent = count > 9 ? '9+' : count;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }

                    // Update notifications list
                    const notificationsList = document.getElementById('notificationsList');
                    if (notifications.length === 0) {
                        notificationsList.innerHTML = '<li class="text-center text-muted py-3">Nenhuma notificação</li>';
                    } else {
                        notificationsList.innerHTML = notifications.map(notif => `
                            <li class="notification-item ${notif.read_at ? 'read' : 'unread'}" data-id="${notif.id}">
                                <a href="javascript:void(0)" class="dropdown-item d-flex justify-content-between align-items-start" onclick="handleNotificationClick(${notif.id}, '${notif.action_url}')">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">${notif.title}</div>
                                        <small class="text-muted">${notif.message}</small>
                                        <div class="text-muted small mt-1">${notif.created_at}</div>
                                    </div>
                                    <span class="badge bg-${notif.type_color} ms-2">${notif.type}</span>
                                </a>
                            </li>
                        `).join('');
                    }

                    // Show/hide mark all read button
                    const markAllBtn = document.getElementById('markAllReadBtn');
                    markAllBtn.style.display = count > 0 ? 'block' : 'none';
                }
            })
            .catch(error => {
                console.error('Erro ao carregar notificações:', error);
                const notificationsList = document.getElementById('notificationsList');
                notificationsList.innerHTML = '<li class="text-center text-danger py-3">Erro ao carregar notificações</li>';
            });
    }

    function handleNotificationClick(notificationId, actionUrl) {
        // Mark as read
        fetch(`/api/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            // Reload notifications
            loadNotifications();

            // Navigate if action_url exists
            if (actionUrl && actionUrl !== 'null') {
                window.location.href = actionUrl;
            }
        })
        .catch(error => console.error('Erro ao marcar notificação como lida:', error));
    }

    function markAllAsRead() {
        fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        })
        .catch(error => console.error('Erro ao marcar todas como lida:', error));
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Load notifications on page load
        loadNotifications();

        // Refresh notifications every 30 seconds
        notificationRefreshInterval = setInterval(loadNotifications, 30000);

        // Mark all read button
        const markAllBtn = document.getElementById('markAllReadBtn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', markAllAsRead);
        }

        // See all notifications button
        const seeAllBtn = document.getElementById('seeAllNotificationsBtn');
        if (seeAllBtn) {
            seeAllBtn.addEventListener('click', function() {
                // This will navigate to a full notifications page (can be created later)
                console.log('See all notifications - will implement full page later');
            });
        }
    });

    // Reload notifications when dropdown is opened
    const notificationsBell = document.getElementById('notificationsBell');
    if (notificationsBell) {
        notificationsBell.addEventListener('click', loadNotifications);
    }
</script>

<style>
    .notification-dropdown {
        min-width: 350px;
        max-width: 450px;
    }

    .notification-item {
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
    }

    .notification-item.unread {
        background-color: #f0f7ff;
    }

    .notification-item .dropdown-item {
        padding: 0.75rem 1rem;
        border: none;
    }

    .notification-item .dropdown-item:hover {
        background-color: transparent;
    }

    .notification-item:last-child {
        border-bottom: none;
    }
</style>

</body>
</html>
