<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pessoa_juridica_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('cnpj')->unique();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('inscricao_estadual')->nullable();
            $table->string('inscricao_municipal')->nullable();
            $table->string('endereco');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('cidade');
            $table->string('estado', 2);
            $table->string('cep');
            $table->string('telefone');
            $table->string('email');
            $table->string('site')->nullable();
            $table->string('ramo_atividade')->nullable();
            $table->date('data_constituicao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('cnpj');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pessoa_juridica_usuario');
    }
};
