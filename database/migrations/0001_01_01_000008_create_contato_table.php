<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('contato', function(Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('cliente')->onDelete('cascade');
            $table->string('nome');
            $table->string('email');
            $table->string('aniversario');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contato');
    }

};