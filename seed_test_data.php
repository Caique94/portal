<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "========================================\n";
echo "🌱 Criando dados de teste...\n";
echo "========================================\n\n";

try {
    // 1. Criar consultores
    echo "1. Criando/buscando consultores...\n";

    $consultor1 = App\Models\User::firstOrCreate(
        ['email' => 'joao@test.com'],
        [
            'name' => 'João Consultor',
            'password' => bcrypt('password'),
            'papel' => 'consultor',
            'data_nasc' => '1990-01-15',
            'valor_hora' => 150.00,
            'valor_km' => 2.50,
        ]
    );
    echo "   ✅ João Consultor (ID: {$consultor1->id})\n";

    $consultor2 = App\Models\User::firstOrCreate(
        ['email' => 'maria@test.com'],
        [
            'name' => 'Maria Consultora',
            'password' => bcrypt('password'),
            'papel' => 'consultor',
            'data_nasc' => '1992-03-20',
            'valor_hora' => 180.00,
            'valor_km' => 3.00,
        ]
    );
    echo "   ✅ Maria Consultora (ID: {$consultor2->id})\n\n";

    // 2. Criar clientes
    echo "2. Criando/buscando clientes...\n";

    $cliente1 = App\Models\Cliente::firstOrCreate(
        ['cgc' => '12345678000199'],
        [
            'codigo' => 'CLI001',
            'loja' => '01',
            'nome' => 'Empresa Teste LTDA',
            'tipo' => 'J',
            'endereco' => 'Rua Teste, 123',
            'municipio' => 'São Paulo',
            'estado' => 'SP',
        ]
    );
    echo "   ✅ Empresa Teste LTDA (ID: {$cliente1->id})\n";

    $cliente2 = App\Models\Cliente::firstOrCreate(
        ['cgc' => '98765432000188'],
        [
            'codigo' => 'CLI002',
            'loja' => '01',
            'nome' => 'Comércio XYZ SA',
            'tipo' => 'J',
            'endereco' => 'Av. Principal, 456',
            'municipio' => 'Rio de Janeiro',
            'estado' => 'RJ',
        ]
    );
    echo "   ✅ Comércio XYZ SA (ID: {$cliente2->id})\n\n";

    // 3. Criar tabela de preços
    echo "3. Criando tabela de preços...\n";

    $tabelaPreco = App\Models\TabelaPreco::create([
        'descricao' => 'Tabela Padrão',
        'data_inicio' => '2025-01-01',
        'data_fim' => '2025-12-31',
    ]);
    echo "   ✅ Criada: Tabela Padrão (ID: {$tabelaPreco->id})\n\n";

    // 4. Criar produto
    echo "4. Criando produto...\n";

    $produto = App\Models\Produto::create([
        'codigo' => 'PROD001',
        'descricao' => 'Consultoria em TI',
        'unidade' => 'HORA',
    ]);
    echo "   ✅ Criado: Consultoria em TI (ID: {$produto->id})\n\n";

    // 5. Vincular produto à tabela de preços
    echo "5. Vinculando produto à tabela de preços...\n";

    $produtoTabela = App\Models\ProdutoTabela::create([
        'tabela_preco_id' => $tabelaPreco->id,
        'produto_id' => $produto->id,
        'preco' => 200.00, // Preço administrativo
    ]);
    echo "   ✅ Vinculado com preço R$ 200,00/hora\n\n";

    // 6. Atualizar clientes com tabela de preços
    $cliente1->update(['tabela_preco_id' => $tabelaPreco->id]);
    $cliente2->update(['tabela_preco_id' => $tabelaPreco->id]);

    // 7. Criar Ordens de Serviço
    echo "6. Criando Ordens de Serviço...\n";

    $os1 = App\Models\OrdemServico::create([
        'numero_os' => 'OS-001',
        'consultor_id' => $consultor1->id,
        'cliente_id' => $cliente1->id,
        'produto_tabela_id' => $produtoTabela->id,
        'preco_produto' => 200.00,
        'data_emissao' => '2025-11-15',
        'data_servico' => '2025-11-15',
        'horas_trabalhadas' => 8,
        'is_presencial' => true,
        'km' => 50,
        'deslocamento' => 2,
        'valor_despesa' => 150.00,
        'status' => 5,
        'created_at' => '2025-11-15 10:00:00',
    ]);
    echo "   ✅ OS-001: João p/ Empresa Teste (8h, 50km, presencial)\n";
    echo "      - Tipo Consultor: R$ " . number_format((8 * 150) + (50 * 2.50) + (2 * 150) + 150, 2, ',', '.') . "\n";
    echo "      - Tipo Cliente: R$ " . number_format((8 * 200) + (50 * 2.50) + (2 * 150) + 150, 2, ',', '.') . "\n";

    $os2 = App\Models\OrdemServico::create([
        'numero_os' => 'OS-002',
        'consultor_id' => $consultor1->id,
        'cliente_id' => $cliente2->id,
        'produto_tabela_id' => $produtoTabela->id,
        'preco_produto' => 200.00,
        'data_emissao' => '2025-11-20',
        'data_servico' => '2025-11-20',
        'horas_trabalhadas' => 4,
        'is_presencial' => false,
        'valor_despesa' => 0,
        'status' => 5,
        'created_at' => '2025-11-20 14:00:00',
    ]);
    echo "   ✅ OS-002: João p/ Comércio XYZ (4h, remoto)\n";
    echo "      - Tipo Consultor: R$ " . number_format(4 * 150, 2, ',', '.') . "\n";
    echo "      - Tipo Cliente: R$ " . number_format(4 * 200, 2, ',', '.') . "\n";

    $os3 = App\Models\OrdemServico::create([
        'numero_os' => 'OS-003',
        'consultor_id' => $consultor2->id,
        'cliente_id' => $cliente1->id,
        'produto_tabela_id' => $produtoTabela->id,
        'preco_produto' => 200.00,
        'data_emissao' => '2025-11-25',
        'data_servico' => '2025-11-25',
        'horas_trabalhadas' => 6,
        'is_presencial' => true,
        'km' => 30,
        'deslocamento' => 1,
        'valor_despesa' => 80.00,
        'status' => 5,
        'created_at' => '2025-11-25 09:00:00',
    ]);
    echo "   ✅ OS-003: Maria p/ Empresa Teste (6h, 30km, presencial)\n";
    echo "      - Tipo Consultor: R$ " . number_format((6 * 180) + (30 * 3) + (1 * 180) + 80, 2, ',', '.') . "\n";
    echo "      - Tipo Cliente: R$ " . number_format((6 * 200) + (30 * 3) + (1 * 180) + 80, 2, ',', '.') . "\n\n";

    echo "========================================\n";
    echo "✅ Dados de teste criados com sucesso!\n";
    echo "========================================\n\n";

    echo "📊 Resumo:\n";
    echo "   - 2 Consultores\n";
    echo "   - 2 Clientes\n";
    echo "   - 3 Ordens de Serviço (Nov/2025)\n\n";

    echo "🧪 Para testar:\n";
    echo "   1. Acesse: http://localhost:8001/relatorio-fechamento/criar\n";
    echo "   2. Período: 01/11/2025 até 30/11/2025\n";
    echo "   3. Teste diferentes combinações:\n";
    echo "      - Consultor específico\n";
    echo "      - Todos os consultores\n";
    echo "      - Cliente específico\n";
    echo "      - Tipo: Consultor vs Cliente\n\n";

} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
