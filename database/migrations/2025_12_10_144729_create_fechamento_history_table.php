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
        Schema::create('fechamento_history', function (Blueprint $table) {
            $table->id();
            $table->uuid('fechamento_job_id');
            $table->foreign('fechamento_job_id')->references('id')->on('fechamento_jobs')->onDelete('cascade');
            $table->jsonb('data');
            $table->string('tag', 100)->nullable()->comment('e.g., agregação por cliente/consultor');
            $table->timestamps();

            $table->index('fechamento_job_id');
            $table->index('tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fechamento_history');
    }
};
