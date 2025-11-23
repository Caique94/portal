<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tabela_preco', function(Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->date('data_inicio')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabela_preco');
    }

};