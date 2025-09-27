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
        Schema::table('claims', function (Blueprint $table) {
            $table->boolean('was_in_repair')->nullable()->after('type');
            $table->text('service_center_documents')->nullable()->after('was_in_repair');
            $table->text('previous_defect')->nullable()->after('service_center_documents');
            $table->text('current_defect')->nullable()->after('previous_defect');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['was_in_repair', 'service_center_documents', 'previous_defect', 'current_defect']);
        });
    }
};
