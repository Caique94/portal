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
        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['client', 'consultant'])->index();
            $table->string('cron_expr', 50)->comment('Expressão cron: 0 0 1 * *');
            $table->jsonb('filters')->nullable()->comment('Filtros aplicados ao job');
            $table->timestamp('last_run')->nullable();
            $table->timestamp('next_run')->nullable()->index();
            $table->boolean('enabled')->default(true)->index();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['type', 'enabled']);
            $table->index(['enabled', 'next_run']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_tasks');
    }
};
