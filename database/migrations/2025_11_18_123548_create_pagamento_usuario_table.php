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
        Schema::create('pagamento_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('titular_conta');
            $table->string('cpf_cnpj_titular')->nullable();
            $table->string('banco');
            $table->string('agencia');
            $table->string('conta');
            $table->enum('tipo_conta', ['corrente', 'poupanca'])->default('corrente');
            $table->string('pix_key')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->unique(['user_id', 'conta']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamento_usuario');
    }
};
