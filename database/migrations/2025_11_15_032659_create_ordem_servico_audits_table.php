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
        Schema::create('ordem_servico_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ordem_servico_id');
            $table->string('event'); // 'created', 'updated', 'status_changed', 'deleted'
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // e.g., 'create', 'edit', 'transition', 'delete'
            $table->json('old_values')->nullable(); // Before state
            $table->json('new_values')->nullable(); // After state
            $table->json('changed_fields')->nullable(); // Only changed fields
            $table->string('status_from')->nullable(); // Previous status if applicable
            $table->string('status_to')->nullable(); // New status if applicable
            $table->text('description')->nullable(); // Human-readable description
            $table->string('ip_address')->nullable(); // IP of requester
            $table->string('user_agent')->nullable(); // Browser info
            $table->timestamps();

            // Foreign keys
            $table->foreign('ordem_servico_id')->references('id')->on('ordem_servico')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes for queries
            $table->index('ordem_servico_id');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['ordem_servico_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordem_servico_audits');
    }
};
