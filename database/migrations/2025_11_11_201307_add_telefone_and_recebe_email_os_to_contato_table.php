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
        Schema::table('contato', function (Blueprint $table) {
            $table->string('telefone')->nullable()->after('email');
            $table->boolean('recebe_email_os')->default(true)->after('telefone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contato', function (Blueprint $table) {
            $table->dropColumn(['telefone', 'recebe_email_os']);
        });
    }
};
