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
        Schema::create('relatorio_fechamento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultor_id')->index();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->integer('total_os')->default(0);
            $table->enum('status', ['rascunho', 'enviado', 'aprovado', 'rejeitado'])->default('rascunho');
            $table->dateTime('data_envio_email')->nullable();
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('aprovado_por')->nullable()->index();
            $table->dateTime('data_aprovacao')->nullable();
            $table->timestamps();

            $table->foreign('consultor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('aprovado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relatorio_fechamento');
    }
};
