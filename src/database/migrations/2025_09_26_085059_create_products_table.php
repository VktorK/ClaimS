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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->date('date_of_buying')->nullable();
            $table->uuid('seller_id')->nullable();
            $table->uuid('claim_id')->nullable();
            $table->timestamps();
            
            // Индексы для оптимизации запросов
            $table->index(['seller_id']);
            $table->index(['claim_id']);
            $table->index(['serial_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
