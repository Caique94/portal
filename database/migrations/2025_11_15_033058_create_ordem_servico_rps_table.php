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
        Schema::create('ordem_servico_rps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ordem_servico_id');
            $table->unsignedBigInteger('rps_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('ordem_servico_id')->references('id')->on('ordem_servico')->onDelete('cascade');
            $table->foreign('rps_id')->references('id')->on('rps')->onDelete('cascade');

            // Unique constraint - each OS can be linked to RPS only once
            $table->unique(['ordem_servico_id', 'rps_id']);

            // Indexes
            $table->index('ordem_servico_id');
            $table->index('rps_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordem_servico_rps');
    }
};
