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
        Schema::table('notifications', function (Blueprint $table) {
            // Add new fields if they don't exist
            if (!Schema::hasColumn('notifications', 'ordem_servico_id')) {
                $table->unsignedBigInteger('ordem_servico_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('notifications', 'action_url')) {
                $table->string('action_url')->nullable()->after('type');
            }
            if (!Schema::hasColumn('notifications', 'data')) {
                $table->json('data')->nullable()->after('action_url');
            }
            if (!Schema::hasColumn('notifications', 'email_sent')) {
                $table->boolean('email_sent')->default(false)->after('data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumnIfExists('ordem_servico_id');
            $table->dropColumnIfExists('action_url');
            $table->dropColumnIfExists('data');
            $table->dropColumnIfExists('email_sent');
        });
    }
};
