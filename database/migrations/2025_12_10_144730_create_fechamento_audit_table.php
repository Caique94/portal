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
        Schema::create('fechamento_audit', function (Blueprint $table) {
            $table->id();
            $table->uuid('job_id')->nullable();
            $table->foreign('job_id')->references('id')->on('fechamento_jobs')->onDelete('set null');
            $table->string('action', 50); // gerar, baixar, reprocessar, aprovar, rejeitar
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->jsonb('details')->nullable();
            $table->timestamp('timestamp')->useCurrent();

            $table->index(['job_id', 'action']);
            $table->index(['user_id', 'timestamp']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fechamento_audit');
    }
};
