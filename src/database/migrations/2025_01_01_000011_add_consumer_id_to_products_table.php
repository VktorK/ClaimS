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
        Schema::table('products', function (Blueprint $table) {
            $table->uuid('consumer_id')->nullable()->after('seller_id')->comment('ID потребителя');
            
            $table->foreign('consumer_id')->references('id')->on('consumers')->onDelete('set null');
            $table->index('consumer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['consumer_id']);
            $table->dropIndex(['consumer_id']);
            $table->dropColumn('consumer_id');
        });
    }
};
