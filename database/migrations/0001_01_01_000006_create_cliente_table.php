<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('cliente', function(Blueprint $table) {
            $table->id();
            $table->string('codigo');
            $table->string('loja');
            $table->string('nome');
            $table->string('nome_fantasia')->nullable();
            $table->string('tipo')->nullable();
            $table->string('cgc')->nullable();
            $table->string('contato')->nullable();
            $table->string('endereco')->nullable();
            $table->string('municipio')->nullable();
            $table->string('estado')->nullable();
            $table->string('km')->nullable();
            $table->string('deslocamento')->nullable();
            $table->unsignedBigInteger('tabela_preco_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }

};