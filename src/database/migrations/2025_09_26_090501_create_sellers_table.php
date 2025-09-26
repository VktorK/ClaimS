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
        Schema::create('sellers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('address')->nullable();
            $table->string('ogrn')->nullable();
            $table->timestamps();
            
            // Индексы для оптимизации запросов
            $table->index(['title']);
            $table->index(['ogrn']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
