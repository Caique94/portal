<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('recibo_provisorio', function(Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('cliente')->onDelete('cascade');
            $table->integer('numero')->unique();
            $table->integer('serie')->nullable();
            $table->string('data_emissao')->nullable();
            $table->string('cond_pagto');
            $table->string('valor');

            $table->timestamps();
        });
    }

    public function down():void
    {
        Schema::dropIfExists('recibo_provisorio');
    }

};