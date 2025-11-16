<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // os_consultor, os_cliente
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->json('filters')->nullable(); // dados de filtro/contexto
            $table->string('path')->nullable(); // caminho do PDF gerado
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->unsignedBigInteger('ordem_servico_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('ordem_servico_id')->references('id')->on('ordem_servico')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
