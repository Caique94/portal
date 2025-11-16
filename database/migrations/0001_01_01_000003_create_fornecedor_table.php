<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('fornecedor', function(Blueprint $table) {
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
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fornecedor');
    }

};