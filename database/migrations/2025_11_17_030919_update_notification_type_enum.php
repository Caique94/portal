<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert enum column to varchar to allow more values
        Schema::table('notifications', function (Blueprint $table) {
            // Drop the check constraint first
            DB::statement('ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check');
            // Change column type from enum to varchar
            DB::statement('ALTER TABLE notifications ALTER COLUMN type TYPE varchar(255)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to enum
        Schema::table('notifications', function (Blueprint $table) {
            DB::statement("ALTER TABLE notifications ALTER COLUMN type TYPE varchar(255)");
        });
    }
};
