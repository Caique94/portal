<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordem_servico', function (Blueprint $table) {
            $table->string('km')->nullable()->after('valor_total');
            $table->decimal('deslocamento', 10, 2)->nullable()->after('km');
            $table->text('observacao')->nullable()->after('deslocamento');
            $table->text('descricao')->nullable()->after('observacao');
        });
    }

    public function down(): void
    {
        Schema::table('ordem_servico', function (Blueprint $table) {
            $table->dropColumn(['km', 'deslocamento', 'observacao', 'descricao']);
        });
    }
};
