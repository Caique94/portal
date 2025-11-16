<?php

namespace Database\Seeders;

use App\Models\Sequence;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SequenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sequences for each entity type
        $sequences = [
            [
                'entity_type' => 'user',
                'current_number' => 0,
                'prefix' => 'USR-',
                'min_digits' => 5,
            ],
            [
                'entity_type' => 'cliente',
                'current_number' => 0,
                'prefix' => 'CLI-',
                'min_digits' => 4,
            ],
            [
                'entity_type' => 'fornecedor',
                'current_number' => 0,
                'prefix' => 'FOR-',
                'min_digits' => 4,
            ],
            [
                'entity_type' => 'produto',
                'current_number' => 0,
                'prefix' => 'PRD-',
                'min_digits' => 4,
            ],
            [
                'entity_type' => 'ordem_servico',
                'current_number' => 0,
                'prefix' => 'OS-',
                'min_digits' => 5,
            ],
            [
                'entity_type' => 'projeto',
                'current_number' => 0,
                'prefix' => 'PRJ-',
                'min_digits' => 4,
            ],
        ];

        foreach ($sequences as $sequence) {
            Sequence::updateOrCreate(
                ['entity_type' => $sequence['entity_type']],
                $sequence
            );
        }
    }
}
