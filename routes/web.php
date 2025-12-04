<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\TabelaPrecoController;
use App\Http\Controllers\ProdutoTabelaController;
use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\FaturamentoController;
use App\Http\Controllers\ReciboProvisorioController;
use App\Http\Controllers\ConsultorHomeController;
use App\Http\Controllers\AdminHomeController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\RelatorioFechamentoController;
use App\Http\Controllers\PagamentoParcelaController;
use App\Http\Controllers\CondicaoPagamentoController;
use App\Http\Controllers\ProjetoController;
use App\Http\Controllers\RPSController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagerialDashboardController;
use App\Http\Controllers\ReportFilterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ClientHistoryController;
use App\Http\Controllers\EstadoCidadeController;

// ========== AUTH ==========
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ========== HOME (redirect por papel) ==========
Route::get('/', function () {
    $user = Auth::user();
    if (!$user) return redirect()->route('login');

    if ($user->papel === 'consultor') {
        return redirect()->route('consultor.home');
    }
    if ($user->papel === 'admin') {
        return redirect()->route('admin.home');
    }
    if ($user->papel === 'financeiro') {
        return redirect()->route('faturamento');
    }
    return view('home');
})->middleware('auth')->name('home');

// ========== DASHBOARD ANALÍTICO ==========
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/data', [DashboardController::class, 'getData']);
    Route::get('/api/dashboard/kpis', [DashboardController::class, 'getKPIs']);
    Route::get('/api/dashboard/charts', [DashboardController::class, 'getCharts']);
    Route::get('/api/dashboard/recent-orders', [DashboardController::class, 'getRecentOrders']);
    Route::get('/api/dashboard/consultant-stats', [DashboardController::class, 'getConsultantStats']);
});

// ========== DASHBOARD GERENCIAL (Admin) ==========
Route::middleware(['auth', RoleMiddleware::class.':admin'])->group(function () {
    Route::get('/dashboard-gerencial', [ManagerialDashboardController::class, 'index'])->name('dashboard.gerencial');
    Route::get('/api/dashboard-gerencial/data', [ManagerialDashboardController::class, 'getData']);
    Route::get('/api/dashboard-gerencial/kpis', [ManagerialDashboardController::class, 'getKPIs']);
    Route::get('/api/dashboard-gerencial/charts', [ManagerialDashboardController::class, 'getCharts']);
    Route::get('/api/dashboard-gerencial/reports', [ManagerialDashboardController::class, 'getReports']);
    Route::get('/api/dashboard-gerencial/relatorio-geral', [ManagerialDashboardController::class, 'getRelatorioGeral']);
    Route::get('/api/dashboard-gerencial/relatorio-clientes', [ManagerialDashboardController::class, 'getRelatorioClientes']);
    Route::get('/api/dashboard-gerencial/relatorio-consultores', [ManagerialDashboardController::class, 'getRelatorioConsultores']);

    // Filtros e Exportação de Relatórios
    Route::get('/api/reports/filter-options', [ReportFilterController::class, 'getFilterOptions']);
    Route::get('/api/reports/filtered', [ReportFilterController::class, 'getFiltered']);
    Route::post('/api/reports/export-excel', [ReportFilterController::class, 'exportExcel']);
    Route::post('/api/reports/export-pdf', [ReportFilterController::class, 'exportPdf']);
});

// ========== RESET DE SENHA (guest — opcional por e-mail) ==========
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetController::class,'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class,'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class,'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class,'resetPassword'])->name('password.update');
});

// ========== TROCAR SENHA MANUAL (sem e-mail) ==========
Route::middleware('auth')->group(function () {
    Route::get('/alterar-senha', [UserController::class, 'changePasswordForm'])->name('password.change');
    Route::post('/alterar-senha', [UserController::class, 'changePassword'])->name('password.update.manual');
});

// ========== HOME CONSULTOR ==========
Route::middleware(['auth', RoleMiddleware::class.':consultor'])->group(function () {
    Route::get('/consultor-home', [ConsultorHomeController::class, 'index'])->name('consultor.home');
    Route::get('/consultor/export-excel', [ConsultorHomeController::class, 'exportExcel'])->name('consultor.exportExcel');
    Route::get('/consultor/export-pdf', [ConsultorHomeController::class, 'exportPDF'])->name('consultor.exportPDF');
});

// ========== HOME ADMIN ==========
Route::middleware(['auth', RoleMiddleware::class.':admin'])->group(function () {
    Route::get('/admin-home', [AdminHomeController::class, 'index'])->name('admin.home');
});

// ========== CONSULTOR + ADMIN → OS ==========
Route::middleware(['auth', RoleMiddleware::class.':consultor,admin'])->group(function () {
    Route::get('/ordem-servico', [OrdemServicoController::class, 'view'])->name('ordem-servico');
});

// ========== ADMIN + FINANCEIRO → Faturamento / RPS / Relatórios Fechamento ==========
Route::middleware(['auth', RoleMiddleware::class.':admin,financeiro'])->group(function () {
    Route::get('/faturamento', [FaturamentoController::class, 'view'])->name('faturamento');
    Route::get('/recibo-provisorio', [ReciboProvisorioController::class, 'view'])->name('recibo-provisorio');

    // Relatórios de Fechamento para Consultores
    Route::get('/relatorio-fechamento', [RelatorioFechamentoController::class, 'index'])->name('relatorio-fechamento.index');
    Route::get('/relatorio-fechamento/criar', [RelatorioFechamentoController::class, 'create'])->name('relatorio-fechamento.create');
    Route::post('/relatorio-fechamento', [RelatorioFechamentoController::class, 'store'])->name('relatorio-fechamento.store');
    Route::get('/relatorio-fechamento/{relatorioFechamento}', [RelatorioFechamentoController::class, 'show'])->name('relatorio-fechamento.show');
    Route::get('/relatorio-fechamento/{relatorioFechamento}/pdf', [RelatorioFechamentoController::class, 'pdf'])->name('relatorio-fechamento.pdf');
    Route::post('/relatorio-fechamento/{relatorioFechamento}/enviar-aprovacao', [RelatorioFechamentoController::class, 'enviarAprovacao'])->name('relatorio-fechamento.enviar-aprovacao');
    Route::post('/relatorio-fechamento/{relatorioFechamento}/aprovar', [RelatorioFechamentoController::class, 'aprovar'])->name('relatorio-fechamento.aprovar');
    Route::post('/relatorio-fechamento/{relatorioFechamento}/rejeitar', [RelatorioFechamentoController::class, 'rejeitar'])->name('relatorio-fechamento.rejeitar');
    Route::post('/relatorio-fechamento/{relatorioFechamento}/enviar-email', [RelatorioFechamentoController::class, 'enviarEmail'])->name('relatorio-fechamento.enviar-email');
    Route::delete('/relatorio-fechamento/{relatorioFechamento}', [RelatorioFechamentoController::class, 'destroy'])->name('relatorio-fechamento.destroy');
});

// ========== SÓ ADMIN → Cadastros (views) ==========
Route::middleware(['auth', RoleMiddleware::class.':admin'])->group(function () {
    Route::get('/cadastros', fn() => view('cadastros'))->name('cadastros');
    Route::get('/cadastros/clientes', fn() => view('cadastros.clientes'));
    Route::get('/cadastros/fornecedores', fn() => view('cadastros.fornecedores'));
    Route::get('/cadastros/produtos', fn() => view('cadastros.produtos'));
    Route::get('/cadastros/tabela-precos', fn() => view('cadastros.tabela-precos'));
    Route::get('/cadastros/usuarios', fn() => view('cadastros.usuarios'));
    Route::get('/cadastros/condicoes-pagamento', fn() => view('cadastros.condicoes-pagamento'));

    // PROJETOS
    Route::resource('projetos', ProjetoController::class);

    // RELATÓRIOS
    Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios');
});

// ========== API INTERNA (auth) ==========
Route::middleware('auth')->group(function () {

    // CLIENTE
    Route::get('/listar-clientes', [ClienteController::class, 'list']);
    Route::get('/gerar-proximo-codigo-cliente', [ClienteController::class, 'gerarProximoCodigo']);
    Route::post('/salvar-cliente', [ClienteController::class, 'store']);
    Route::delete('/excluir-cliente/{id}', [ClienteController::class, 'delete']);

    // CLIENT HISTORY
    Route::get('/cliente/{id}/historico', [ClientHistoryController::class, 'show'])->name('cliente.historico');
    Route::get('/api/cliente/{id}/historico/timeline', [ClientHistoryController::class, 'timelineJson']);
    Route::get('/api/cliente/{id}/historico/spent-by-period', [ClientHistoryController::class, 'spentByPeriodJson']);
    Route::get('/api/cliente/{id}/historico/patterns', [ClientHistoryController::class, 'patternsJson']);
    Route::get('/api/cliente/{id}/historico/suggestions', [ClientHistoryController::class, 'suggestionsJson']);
    Route::get('/api/cliente/{id}/historico/overview', [ClientHistoryController::class, 'overviewJson']);

    // FORNECEDOR
    Route::get('/listar-fornecedores', [FornecedorController::class, 'list']);
    Route::post('/salvar-fornecedor', [FornecedorController::class, 'store']);
    Route::delete('/excluir-fornecedor/{id}', [FornecedorController::class, 'delete']);

    // CONTATO
    Route::get('/listar-contatos', [ContatoController::class, 'list']);
    Route::post('/salvar-contato', [ContatoController::class, 'store']);
    Route::delete('/remover-contato/{id}', [ContatoController::class, 'delete']);

    // ESTADOS E CIDADES
    Route::get('/listar-estados', [EstadoCidadeController::class, 'listarEstados']);
    Route::get('/listar-cidades/{estadoId}', [EstadoCidadeController::class, 'listarCidades']);
    Route::get('/buscar-cidades', [EstadoCidadeController::class, 'buscarCidades']);
    Route::get('/buscar-estado', [EstadoCidadeController::class, 'buscarEstado']);
    Route::post('/buscar-cep', [EstadoCidadeController::class, 'buscarCEP']);

    // PRODUTO
    Route::get('/listar-produtos', [ProdutoController::class, 'list']);
    Route::get('/listar-produtos-ativos', [ProdutoController::class, 'active_list']);
    Route::get('/gerar-proximo-codigo-produto', [ProdutoController::class, 'gerarProximoCodigo']);
    Route::post('/salvar-produto', [ProdutoController::class, 'store']);
    Route::get('/toggle-produto/{id}', [ProdutoController::class, 'toggle']);
    Route::delete('/excluir-produto/{id}', [ProdutoController::class, 'delete']);

    // TABELA DE PREÇOS
    Route::get('/listar-tabelas-precos', [TabelaPrecoController::class, 'list']);
    Route::get('/listar-tabelas-precos-ativos', [TabelaPrecoController::class, 'active_list']);
    Route::post('/salvar-tabela-precos', [TabelaPrecoController::class, 'store']);
    Route::post('/editar-tabela-precos/{id}', [TabelaPrecoController::class, 'update']);
    Route::get('/toggle-tabela-precos/{id}', [TabelaPrecoController::class, 'toggle']);
    Route::delete('/excluir-tabela-precos/{id}', [TabelaPrecoController::class, 'delete']);

    // PRODUTO X TABELA
    Route::get('/listar-produtos-tabela', [ProdutoTabelaController::class, 'list']);
    Route::post('/salvar-produto-tabela', [ProdutoTabelaController::class, 'store']);
    Route::get('/toggle-produto-tabela/{id}', [ProdutoTabelaController::class, 'toggle']);
    Route::get('/listar-produtos-por-cliente/{id}', [ProdutoTabelaController::class, 'list_by_client']);

    // ORDEM SERVIÇO
    Route::get('/listar-ordens-servico', [OrdemServicoController::class, 'list']);
    Route::post('/salvar-ordem-servico', [OrdemServicoController::class, 'store']);
    Route::get('/toggle-status-ordem-servico/{id}/{status}', [OrdemServicoController::class, 'toggle_status']);
    Route::post('/toggle-ordem-servico/{id}/{status}', [OrdemServicoController::class, 'toggle_status']);
    Route::post('/contestar-ordem-servico', [OrdemServicoController::class, 'contest']);
    Route::delete('/deletar-ordem-servico/{id}', [OrdemServicoController::class, 'destroy']);
    Route::get('/listar-ordens-faturamento', [OrdemServicoController::class, 'list_invoice']);
    Route::get('/clientes-com-ordens-rps', [OrdemServicoController::class, 'clientesComOrdensRPS']);
    Route::get('/clientes-com-ordens-faturar', [OrdemServicoController::class, 'clientesComOrdensParaFaturar']);
    Route::post('/faturar-ordens-servico', [OrdemServicoController::class, 'invoice_orders']);
    Route::post('/salvar_rps', [OrdemServicoController::class, 'rps_orders']);

    // NEW OS WORKFLOW ENDPOINTS (with state machine)
    Route::post('/os/{id}/solicitar-aprovacao', [OrdemServicoController::class, 'requestApproval']);
    Route::post('/os/{id}/aprovar', [OrdemServicoController::class, 'approve']);
    Route::post('/os/{id}/contestar', [OrdemServicoController::class, 'contest']);
    Route::post('/os/{id}/faturar', [OrdemServicoController::class, 'bill']);
    Route::post('/os/{id}/reenviar-email', [OrdemServicoController::class, 'resendEmail']);
    Route::get('/os/aguardando-rps', [OrdemServicoController::class, 'listForRps']);
    Route::get('/os/{id}/auditoria', [OrdemServicoController::class, 'getAuditTrail']);
    Route::get('/os/{id}/permissoes', [OrdemServicoController::class, 'getPermissions']);
    Route::get('/os/{id}/totalizador-data', [OrdemServicoController::class, 'getTotalizadorData']);

    // RPS ENDPOINTS
    Route::get('/rps', [RPSController::class, 'index']);
    Route::post('/rps', [RPSController::class, 'store']);
    Route::get('/rps/{id}', [RPSController::class, 'show']);
    Route::post('/rps/{id}/vincular-ordens', [RPSController::class, 'linkOrdensServico']);
    Route::post('/rps/{id}/cancelar', [RPSController::class, 'cancel']);
    Route::post('/rps/{id}/reverter', [RPSController::class, 'revert']);
    Route::get('/rps/cliente/{clienteId}', [RPSController::class, 'listByClient']);
    Route::get('/rps/cliente/{clienteId}/ordens-aguardando', [RPSController::class, 'listOrdensReadyForRps']);
    Route::get('/rps/{id}/auditoria', [RPSController::class, 'getAuditTrail']);
    Route::get('/rps/{id}/exportar-pdf', [RPSController::class, 'exportPdf']);

    // USUÁRIOS
    Route::get('/listar-usuarios', [UserController::class, 'list']);
    Route::post('/salvar-usuario', [UserController::class, 'store']);
    Route::get('/toggle-usuario/{id}', [UserController::class, 'toggle']);
    Route::post('/enviar-senha-usuario/{id}', [UserController::class, 'sendPasswordEmail']);

    // RECIBO PROVISÓRIO
    Route::get('/listar-recibos-provisorios', [ReciboProvisorioController::class, 'list']);

    // PARCELAS DE PAGAMENTO
    Route::get('/listar-parcelas', [PagamentoParcelaController::class, 'list']);
    Route::post('/criar-parcelas', [PagamentoParcelaController::class, 'store']);
    Route::post('/marcar-parcela-paga/{id}', [PagamentoParcelaController::class, 'marcarPaga']);
    Route::put('/atualizar-parcela/{id}', [PagamentoParcelaController::class, 'update']);
    Route::delete('/deletar-parcela/{id}', [PagamentoParcelaController::class, 'delete']);
    Route::get('/dashboard-parcelas', [PagamentoParcelaController::class, 'dashboard']);

    // CONDIÇÕES DE PAGAMENTO
    Route::get('/listar-condicoes-pagamento', [CondicaoPagamentoController::class, 'list']);
    Route::get('/todas-condicoes-pagamento', [CondicaoPagamentoController::class, 'allCondicoes']);
    Route::post('/salvar-condicao-pagamento', [CondicaoPagamentoController::class, 'store']);
    Route::put('/atualizar-condicao-pagamento/{id}', [CondicaoPagamentoController::class, 'update']);
    Route::delete('/deletar-condicao-pagamento/{id}', [CondicaoPagamentoController::class, 'delete']);

    // PROJETOS API
    Route::get('/cliente/{clienteId}/projetos', [ProjetoController::class, 'getClienteProjetos']);

    // RELATÓRIOS API
    Route::get('/relatorio-fechamento-cliente', [RelatorioController::class, 'fechamentoCliente']);
    Route::get('/relatorio-fechamento-consultor', [RelatorioController::class, 'fechamentoConsultor']);
    Route::get('/relatorio-fechamento-geral', [RelatorioController::class, 'fechamentoGeral']);
    Route::get('/relatorio-ordem-por-status', [RelatorioController::class, 'ordemPorStatus']);

    // NOTIFICATIONS API
    Route::get('/api/notifications/unread', [NotificationController::class, 'getUnread']);
    Route::get('/api/notifications', [NotificationController::class, 'index']);
    Route::post('/api/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/api/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/api/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::get('/api/notifications/count', [NotificationController::class, 'getCount']);
    Route::get('/api/notifications/type/{type}', [NotificationController::class, 'getByType']);
});
