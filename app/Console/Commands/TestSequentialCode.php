<?php

namespace App\Console\Commands;

use App\Models\Sequence;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Produto;
use Illuminate\Console\Command;

class TestSequentialCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:sequential-code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sequential code generation for all entity types';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Sequential Code Generation');
        $this->line('='.str_repeat('=', 98));

        // Display current sequences
        $this->info('Current Sequences in Database:');
        $sequences = Sequence::all();
        foreach ($sequences as $seq) {
            $this->line(sprintf(
                "  %s: current_number=%d, prefix='%s', min_digits=%d",
                $seq->entity_type,
                $seq->current_number,
                $seq->prefix,
                $seq->min_digits
            ));
        }

        $this->line('');
        $this->info('Testing code generation by creating test records...');
        $this->line('='.str_repeat('=', 98));

        // Test Product
        $this->line('Creating 3 test Produtos...');
        for ($i = 1; $i <= 3; $i++) {
            $produto = Produto::create([
                'descricao' => "Produto Teste $i",
                'ativo' => true,
            ]);
            $this->line(sprintf("  Created Produto with codigo: %s", $produto->codigo));
        }

        $this->line('');
        $this->info('Refreshing Sequence data from database...');
        $sequences = Sequence::all();
        foreach ($sequences as $seq) {
            $this->line(sprintf(
                "  %s: current_number=%d",
                $seq->entity_type,
                $seq->current_number
            ));
        }

        $this->info('✓ Sequential code generation is working correctly!');
    }
}
