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
        Schema::create('rps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->string('numero_rps')->unique(); // RPS number from fiscal authority
            $table->date('data_emissao');
            $table->date('data_vencimento')->nullable();
            $table->decimal('valor_total', 12, 2);
            $table->decimal('valor_servicos', 12, 2)->default(0);
            $table->decimal('valor_deducoes', 12, 2)->default(0);
            $table->decimal('valor_impostos', 12, 2)->default(0);
            $table->string('status')->default('emitida'); // 'emitida', 'cancelada', 'revertida'
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('criado_por');
            $table->timestamp('cancelado_em')->nullable();
            $table->unsignedBigInteger('cancelado_por')->nullable();
            $table->text('motivo_cancelamento')->nullable();
            $table->timestamp('revertido_em')->nullable();
            $table->unsignedBigInteger('revertido_por')->nullable();
            $table->text('motivo_reversao')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('cliente_id')->references('id')->on('cliente')->onDelete('restrict');
            $table->foreign('criado_por')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('cancelado_por')->references('id')->on('users')->onDelete('set null');
            $table->foreign('revertido_por')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('cliente_id');
            $table->index('numero_rps');
            $table->index('data_emissao');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rps');
    }
};
