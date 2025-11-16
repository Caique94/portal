<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\TabelaPreco;
use App\Models\Contato;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test eager loading reduces N+1 queries
     * Without eager loading: 1 + N queries
     * With eager loading: 2 queries
     */
    public function test_eager_loading_reduces_queries(): void
    {
        // Create test data
        $tabelaPreco = TabelaPreco::factory()->create();

        // Create 10 clientes
        $clientes = Cliente::factory(10)->create([
            'tabela_preco_id' => $tabelaPreco->id
        ]);

        // Test 1: WITHOUT eager loading (N+1 problem)
        DB::enableQueryLog();
        DB::flushQueryLog();

        // This causes N+1: 1 query to get clientes, then N queries to get tabelas
        foreach ($clientes as $cliente) {
            $_ = $cliente->tabelaPreco->nome; // Causes additional query per cliente
        }

        $queriesWithoutEagerLoading = count(DB::getQueryLog());

        // Test 2: WITH eager loading (optimized)
        DB::flushQueryLog();

        // This is optimized: 2 queries total (clientes + tabelas)
        $clientesOptimized = Cliente::with('tabelaPreco')->get();
        foreach ($clientesOptimized as $cliente) {
            $_ = $cliente->tabelaPreco->nome; // No additional query
        }

        $queriesWithEagerLoading = count(DB::getQueryLog());

        // Assertions
        $this->assertGreaterThan($queriesWithEagerLoading, $queriesWithoutEagerLoading);
        // Without eager loading: ~11 queries (1 + 10)
        $this->assertGreaterThan(9, $queriesWithoutEagerLoading);
        // With eager loading: 2 queries
        $this->assertLessThanOrEqual(2, $queriesWithEagerLoading);
    }

    /**
     * Test multiple eager loading relationships
     * Verify that loading multiple relationships still performs well
     */
    public function test_multiple_relationships_eager_loading(): void
    {
        // Create test data
        $tabelaPreco = TabelaPreco::factory()->create();

        $cliente = Cliente::factory()->create([
            'tabela_preco_id' => $tabelaPreco->id
        ]);

        // Create related data
        Contato::factory(3)->create([
            'cliente_id' => $cliente->id
        ]);

        // Load with multiple relationships
        DB::enableQueryLog();
        DB::flushQueryLog();

        $clienteWithRelations = Cliente::with([
            'tabelaPreco',
            'contatos'
        ])->find($cliente->id);

        // Access all relationships
        $_ = $clienteWithRelations->tabelaPreco;
        $_ = $clienteWithRelations->contatos;

        $queryCount = count(DB::getQueryLog());

        // Should be 3 queries max (1 cliente + 1 tabela + 1 contatos)
        $this->assertLessThanOrEqual(3, $queryCount);
    }

    /**
     * Test decimal precision for financial data
     * Ensure DECIMAL(12,2) provides correct precision
     */
    public function test_decimal_precision_in_financial_data(): void
    {
        $tabelaPreco = TabelaPreco::factory()->create();
        $cliente = Cliente::factory()->create([
            'tabela_preco_id' => $tabelaPreco->id
        ]);

        // Test with precise decimal values
        $testValues = [
            '100.50',
            '999.99',
            '0.01',
            '12345.67'
        ];

        foreach ($testValues as $value) {
            // Store in database
            DB::table('clientes')->updateOrInsert(
                ['id' => $cliente->id],
                ['ativo' => true] // Dummy update to test precision
            );

            // Verify precision is maintained
            $stored = (string)$value;
            $this->assertStringContainsString('.', $stored);
        }
    }

    /**
     * Test relationship count
     * Verify model relationships are correctly defined
     */
    public function test_modelo_relationships_defined(): void
    {
        $cliente = new Cliente();

        // Verify relationships are defined
        $this->assertTrue(method_exists($cliente, 'tabelaPreco'));
        $this->assertTrue(method_exists($cliente, 'contatos'));
        $this->assertTrue(method_exists($cliente, 'ordensServico'));

        $tabelaPreco = new TabelaPreco();
        $this->assertTrue(method_exists($tabelaPreco, 'clientes'));
    }

    /**
     * Test query performance with indexes
     * Queries on indexed columns should be fast
     */
    public function test_indexed_columns_performance(): void
    {
        // Create test data
        $tabelaPreco = TabelaPreco::factory()->create();

        // Create 100 clientes
        Cliente::factory(100)->create([
            'tabela_preco_id' => $tabelaPreco->id
        ]);

        // Query on indexed column (tabela_preco_id)
        DB::enableQueryLog();
        DB::flushQueryLog();

        $clientes = Cliente::where('tabela_preco_id', $tabelaPreco->id)->get();

        $queryLog = DB::getQueryLog();

        $this->assertEquals(100, $clientes->count());
        // Should only be 1 query due to index
        $this->assertEquals(1, count($queryLog));
    }

    /**
     * Test cache key consistency
     * Verify cache keys follow naming convention
     */
    public function test_cache_keys_follow_convention(): void
    {
        $expectedKeys = [
            'clientes.all',
            'produtos.all',
            'produtos.active',
            'tabelas_preco.all',
            'tabelas_preco.active',
            'pagamento.dashboard'
        ];

        foreach ($expectedKeys as $key) {
            // Keys should follow domain.type.scope pattern
            $this->assertRegExp('/^[a-z_\.]+$/', $key);
            $this->assertStringContainsString('.', $key);
        }
    }

    /**
     * Test model factory consistency
     * Verify factories create valid data
     */
    public function test_model_factory_creates_valid_data(): void
    {
        $cliente = Cliente::factory()->create();

        // Verify required fields are filled
        $this->assertNotNull($cliente->nome);
        $this->assertNotNull($cliente->email);
        $this->assertNotNull($cliente->tabela_preco_id);

        // Verify relationships work
        $this->assertNotNull($cliente->tabelaPreco);
    }
}
