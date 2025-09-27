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
        Schema::table('consumers', function (Blueprint $table) {
            $table->string('passport_issued_by', 70)->nullable()->after('passport')->comment('Кем выдан паспорт');
            $table->date('passport_issued_date')->nullable()->after('passport_issued_by')->comment('Дата выдачи паспорта');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumers', function (Blueprint $table) {
            $table->dropColumn(['passport_issued_by', 'passport_issued_date']);
        });
    }
};
