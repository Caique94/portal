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
        Schema::create('fechamento_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['client', 'consultant'])->index();
            $table->date('period_start');
            $table->date('period_end');
            $table->jsonb('filters')->nullable();
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['queued', 'processing', 'success', 'failed'])->default('queued')->index();
            $table->text('error_message')->nullable();
            $table->string('pdf_url')->nullable();
            $table->string('pdf_checksum', 64)->nullable();
            $table->integer('version')->default(1);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['requested_by', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fechamento_jobs');
    }
};
