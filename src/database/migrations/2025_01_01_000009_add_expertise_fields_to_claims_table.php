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
            $table->boolean('expertiseConducted')->nullable()->after('current_defect')->comment('Проводилась ли экспертиза');
            $table->text('expertiseData')->nullable()->after('expertiseConducted')->comment('Данные о проведенной экспертизе');
            $table->text('expertiseDefect')->nullable()->after('expertiseData')->comment('Недостаток согласно экспертизе');
            $table->text('actualDefect')->nullable()->after('expertiseDefect')->comment('Настоящий недостаток');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['expertiseConducted', 'expertiseData', 'expertiseDefect', 'actualDefect']);
        });
    }
};
