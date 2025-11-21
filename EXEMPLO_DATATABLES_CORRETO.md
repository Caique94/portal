# Exemplo Completo de DataTables Correto

## Padrão Recomendado para Todos os DataTables

Este documento mostra exemplos completos que podem ser aplicados em todos os seus cadastros.

---

## 1️⃣ EXEMPLO: Cadastro de Clientes

### Frontend - `public/js/cadastros/clientes.js`

```javascript
$(function () {

  const $tbl = $('#tblClientes');

  // Inicializar DataTable com configuração correta
  const tblClientes = $tbl.DataTable({
    // CONFIGURAÇÃO AJAX COM HEADERS E ERROR HANDLER
    ajax: {
      url: '/listar-clientes',
      type: 'GET',
      dataSrc: 'data',  // Espera { data: [...] }

      // Headers necessários
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },

      // Tratamento detalhado de erros
      error: function(xhr, status, error) {
        console.error('DataTables AJAX Error - Clientes:', {
          status: xhr.status,
          statusText: xhr.statusText,
          error: error,
          responseText: xhr.responseText.substring(0, 300)
        });

        let errorMsg = 'Erro ao carregar dados de clientes';
        let icon = 'error';

        switch(xhr.status) {
          case 401:
            errorMsg = 'Sessão expirada. Faça login novamente.';
            // Opcional: redirecionar após alguns segundos
            // setTimeout(() => window.location.href = '/login', 2000);
            break;
          case 403:
            errorMsg = 'Você não tem permissão para acessar clientes';
            break;
          case 404:
            errorMsg = 'Rota /listar-clientes não encontrada';
            break;
          case 500:
            errorMsg = 'Erro no servidor ao carregar clientes';
            break;
          case 0:
            // Erro de conexão (sem resposta do servidor)
            errorMsg = 'Erro de conexão com o servidor';
            break;
        }

        Toast.fire({
          icon: icon,
          title: errorMsg
        });
      }
    },

    // COLUNAS DA TABELA
    columns: [
      { title: 'Nome',           data: 'nome_fantasia',   defaultContent: '' },
      { title: 'Razão Social',   data: 'razao_social',    defaultContent: '' },
      { title: 'CNPJ',           data: 'cnpj',            defaultContent: '' },
      { title: 'Email',          data: 'email',           defaultContent: '' },
      { title: 'Telefone',       data: 'telefone',        defaultContent: '' },
      {
        title: 'Ativo',
        data: 'ativo',
        className: 'dt-center',
        orderable: false,
        width: '80px',
        render: (data) => `
          <span class="badge ${data ? 'bg-success' : 'bg-danger'}">
            ${data ? 'Ativo' : 'Inativo'}
          </span>
        `
      },
      {
        title: 'Ações',
        data: null,
        className: 'dt-center',
        orderable: false,
        width: '120px',
        render: (data, type, row) => `
          <div class="btn-group" role="group">
            <button class="btn btn-sm btn-info btn-visualizar"
                    title="Visualizar"
                    data-id="${row.id}">
              <i class="bi bi-eye"></i>
            </button>
            <button class="btn btn-sm btn-warning btn-editar"
                    title="Editar"
                    data-id="${row.id}">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-danger btn-excluir"
                    title="Excluir"
                    data-id="${row.id}">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        `
      }
    ],

    // LAYOUT E PROCESSAMENTO
    processing: true,
    dom: "<'row'<'col-sm-6'B><'col-sm-6 text-end'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    buttons: [
      {
        text: '<i class="bi bi-plus-circle"></i> Adicionar',
        className: 'btn btn-primary dt-button-styled',
        action: function () {
          abrirModalAdicionar();
        }
      },
      {
        extend: 'excel',
        className: 'btn btn-primary dt-button-styled'
      }
    ]
  });

  // ==================== EVENT HANDLERS ====================

  // Visualizar cliente
  $tbl.on('click', '.btn-visualizar', function () {
    const clienteId = $(this).data('id');
    console.log('Visualizar cliente:', clienteId);
    // Implementar lógica de visualização
  });

  // Editar cliente
  $tbl.on('click', '.btn-editar', function () {
    const clienteId = $(this).data('id');
    console.log('Editar cliente:', clienteId);
    // Implementar lógica de edição
  });

  // Excluir cliente
  $tbl.on('click', '.btn-excluir', function () {
    const clienteId = $(this).data('id');
    const nomeCliente = $(this).closest('tr').find('td').eq(0).text();

    Swal.fire({
      title: 'Confirmar exclusão?',
      html: `Excluir cliente <strong>${nomeCliente}</strong>?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Excluir',
      cancelButtonText: 'Cancelar',
      backdrop: false,
      customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-secondary'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `/excluir-cliente/${clienteId}`,
          type: 'DELETE',
          success: function(response) {
            Toast.fire({
              icon: 'success',
              title: 'Cliente excluído com sucesso!'
            });
            tblClientes.ajax.reload(null, false);
          },
          error: function(xhr) {
            Toast.fire({
              icon: 'error',
              title: 'Erro ao excluir cliente'
            });
            console.error('Erro DELETE:', xhr);
          }
        });
      }
    });
  });

  // Adicionar cliente
  function abrirModalAdicionar() {
    console.log('Abrir modal de adicionar cliente');
    // Implementar lógica de abertura de modal
  }

  // Salvar cliente
  function salvarCliente(dados) {
    $.ajax({
      url: '/salvar-cliente',
      type: 'POST',
      data: dados,
      success: function(response) {
        Toast.fire({
          icon: 'success',
          title: response.message || 'Cliente salvo com sucesso!'
        });
        tblClientes.ajax.reload(null, false);
      },
      error: function(xhr) {
        let msg = 'Erro ao salvar cliente';
        if (xhr.status === 422 && xhr.responseJSON.errors) {
          const errors = xhr.responseJSON.errors;
          msg = Object.values(errors).flat().join(', ');
        }
        Toast.fire({
          icon: 'error',
          title: msg
        });
        console.error('Erro POST:', xhr);
      }
    });
  }

});
```

### Backend - Controller (Já deveria estar assim)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Lista todos os clientes em formato JSON para DataTables
     */
    public function list()
    {
        try {
            $clientes = Cliente::query()
                ->select([
                    'id',
                    'nome_fantasia',
                    'razao_social',
                    'cnpj',
                    'email',
                    'telefone',
                    'ativo'
                ])
                ->orderBy('nome_fantasia')
                ->get()
                ->map(function ($cliente) {
                    return [
                        'id'              => (int)$cliente->id,
                        'nome_fantasia'   => (string)($cliente->nome_fantasia ?? ''),
                        'razao_social'    => (string)($cliente->razao_social ?? ''),
                        'cnpj'            => (string)($cliente->cnpj ?? ''),
                        'email'           => (string)($cliente->email ?? ''),
                        'telefone'        => (string)($cliente->telefone ?? ''),
                        'ativo'           => (bool)($cliente->ativo ?? false),
                    ];
                })
                ->values()
                ->all();

            // Retornar no formato esperado pelo DataTables
            return response()->json([
                'data' => $clientes
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erro ao listar clientes: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar clientes',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Salvar novo cliente ou atualizar existente
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome_fantasia'   => 'required|string|max:255',
                'razao_social'    => 'required|string|max:255',
                'cnpj'            => 'required|string|max:20',
                'email'           => 'required|email|max:255',
                'telefone'        => 'nullable|string|max:20',
            ]);

            $clienteId = $request->input('id');

            if ($clienteId) {
                // Atualizar
                $cliente = Cliente::findOrFail($clienteId);
                $cliente->update($validated);
                $message = 'Cliente atualizado com sucesso';
            } else {
                // Criar novo
                $cliente = Cliente::create($validated);
                $message = 'Cliente criado com sucesso';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $cliente
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Erro ao salvar cliente: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar cliente',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir cliente
     */
    public function delete(Request $request, string $id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            $cliente->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cliente excluído com sucesso'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erro ao excluir cliente: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir cliente',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 2️⃣ EXEMPLO: Cadastro de Produtos

### Frontend Simplificado - `public/js/cadastros/produtos.js`

```javascript
$(function () {
  const $tbl = $('#tblProdutos');

  const tblProdutos = $tbl.DataTable({
    ajax: {
      url: '/listar-produtos',
      type: 'GET',
      dataSrc: 'data',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      error: function(xhr) {
        console.error('Erro DataTables:', xhr.status, xhr.statusText);
        const msg = xhr.status === 401
          ? 'Sessão expirada'
          : 'Erro ao carregar produtos';
        Toast.fire({ icon: 'error', title: msg });
      }
    },
    columns: [
      { title: 'Nome',      data: 'nome' },
      { title: 'Descrição', data: 'descricao' },
      { title: 'Valor',     data: 'valor' },
      { title: 'Ativo',     data: 'ativo',
        render: (data) => data ? '✓' : '✗'
      },
      { title: 'Ações',     data: null,
        render: (data, type, row) => `
          <button class="btn btn-sm btn-info" onclick="editarProduto(${row.id})">
            Editar
          </button>
        `
      }
    ]
  });
});

function editarProduto(id) {
  console.log('Editar produto:', id);
  // Implementar lógica
}
```

---

## 3️⃣ HTML Template Correto

### Blade Template - `resources/views/cadastros/clientes.blade.php`

```blade
@extends('layout.master')

@section('title', 'Cadastro de Clientes')

@push('styles')
<link href="{{ asset('plugins/datatables/datatables.min.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2>Clientes</h2>
        </div>
        <div class="col text-end">
            <button class="btn btn-primary" id="btnAdicionarCliente">
                <i class="bi bi-plus"></i> Adicionar Cliente
            </button>
        </div>
    </div>

    <!-- DataTable -->
    <div class="table-responsive">
        <table id="tblClientes" class="table table-striped table-hover w-100">
            <thead class="table-dark">
                <tr>
                    <th>Nome</th>
                    <th>Razão Social</th>
                    <th>CNPJ</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/cadastros/clientes.js') }}"></script>
@endpush
```

---

## 4️⃣ Rotas Corretas

### `routes/web.php`

```php
// API Interna protegida com autenticação
Route::middleware('auth')->group(function () {

    // Clientes
    Route::get('/listar-clientes', [ClienteController::class, 'list']);
    Route::post('/salvar-cliente', [ClienteController::class, 'store']);
    Route::delete('/excluir-cliente/{id}', [ClienteController::class, 'delete']);

    // Produtos
    Route::get('/listar-produtos', [ProdutoController::class, 'list']);
    Route::post('/salvar-produto', [ProdutoController::class, 'store']);
    Route::delete('/excluir-produto/{id}', [ProdutoController::class, 'delete']);

    // ... outras rotas
});
```

---

## 5️⃣ Configuração Global no app.js

```javascript
$(document).ready(function() {

    // ========== AJAX SETUP GLOBAL ==========
    // Aplicado a TODAS as requisições AJAX automaticamente
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'  // ← Crucial para DataTables
        },
        error: function(xhr, status, error) {
            console.error('AJAX Global Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                error: error
            });
        }
    });

    // ... resto do código
});
```

---

## 6️⃣ Resposta JSON Esperada

### Sucesso:
```json
{
  "data": [
    {
      "id": 1,
      "nome_fantasia": "Empresa A",
      "razao_social": "Empresa A LTDA",
      "cnpj": "12.345.678/0001-90",
      "email": "contato@empresa.com",
      "telefone": "(11) 1234-5678",
      "ativo": true
    },
    {
      "id": 2,
      "nome_fantasia": "Empresa B",
      "razao_social": "Empresa B LTDA",
      "cnpj": "98.765.432/0001-10",
      "email": "contato@empresa2.com",
      "telefone": "(11) 8765-4321",
      "ativo": false
    }
  ]
}
```

### Erro 401:
```json
{
  "success": false,
  "message": "Não autenticado",
  "code": 401
}
```

### Erro 422:
```json
{
  "success": false,
  "message": "Erro na validação dos dados",
  "errors": {
    "email": ["O campo email deve ser um email válido"],
    "cnpj": ["O campo cnpj é obrigatório"]
  },
  "code": 422
}
```

---

## ✅ Checklist de Implementação

- [ ] Adicionar `'Accept': 'application/json'` no `$.ajaxSetup()` em app.js
- [ ] Adicionar headers no DataTables AJAX config
- [ ] Implementar error handler no DataTables
- [ ] Verificar que Controller retorna `{ data: [...] }`
- [ ] Verificar que rota está protegida com `middleware('auth')`
- [ ] Testar com F12 Network tab
- [ ] Confirmar resposta JSON para erro 401
- [ ] Replicar padrão em todos os DataTables

---

## 🧪 Como Testar no Console (F12)

```javascript
// Teste manual de requisição AJAX
$.ajax({
  url: '/listar-clientes',
  type: 'GET',
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  success: function(response) {
    console.log('Sucesso:', response);
  },
  error: function(xhr) {
    console.error('Erro:', xhr.status, xhr.responseJSON);
  }
});
```

Se receber JSON válido → ✓ Está funcionando!
Se receber HTML → ✗ Há problema na configuração.
