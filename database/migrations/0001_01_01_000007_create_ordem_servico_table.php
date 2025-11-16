<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('ordem_servico', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultor_id');
            $table->unsignedBigInteger('cliente_id');
            $table->string('data_emissao');
            $table->string('tipo_despesa')->nullable();
            $table->string('valor_despesa')->nullable();
            $table->string('detalhamento_despesa')->nullable();
            $table->integer('status');
            $table->text('motivo_contestacao')->nullable();
            $table->string('assunto')->nullable();
            $table->string('projeto')->nullable();
            $table->string('nr_atendimento')->nullable();
            $table->string('preco_produto')->nullable();
            $table->string('valor_total')->nullable();

            $table->unsignedBigInteger('produto_tabela_id');
            $table->string('hora_inicio')->nullable();
            $table->string('hora_final')->nullable();
            $table->string('hora_desconto')->nullable();
            $table->string('qtde_total')->nullable();
            $table->text('detalhamento')->nullable();

            $table->integer('nr_rps')->nullable();
            $table->string('cond_pagto')->nullable();
            $table->string('valor_rps')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordem_servico');
    }

};