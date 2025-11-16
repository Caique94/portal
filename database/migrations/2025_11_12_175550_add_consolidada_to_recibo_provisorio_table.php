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
        Schema::table('recibo_provisorio', function (Blueprint $table) {
            $table->boolean('consolidada')->default(false)->comment('Indica se esta RPS é uma consolidação de múltiplas ordens');
            $table->text('ordens_consolidadas')->nullable()->comment('IDs das ordens consolidadas nesta RPS (formato JSON ou CSV)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recibo_provisorio', function (Blueprint $table) {
            $table->dropColumn(['consolidada', 'ordens_consolidadas']);
        });
    }
};
