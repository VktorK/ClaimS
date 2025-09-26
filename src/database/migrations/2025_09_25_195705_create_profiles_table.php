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
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Персональная информация
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            
            // Адресная информация
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            
            // Профессиональная информация
            $table->string('job_title')->nullable();
            $table->string('company')->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            
            // Социальные сети
            $table->string('website')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            
            // Настройки профиля
            $table->boolean('is_public')->default(true);
            $table->boolean('show_email')->default(false);
            $table->boolean('show_phone')->default(false);
            $table->json('preferences')->nullable();
            
            $table->timestamps();
            
            // Индексы
            $table->index(['user_id']);
            $table->index(['is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
