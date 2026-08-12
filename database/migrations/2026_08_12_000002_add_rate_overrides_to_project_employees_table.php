<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_employees', function (Blueprint $table) {
            $table->string('payment_frequency', 20)->nullable()->after('is_active');
            $table->decimal('daily_rate', 12, 2)->nullable()->after('payment_frequency');
            $table->decimal('weekly_rate', 12, 2)->nullable()->after('daily_rate');
            $table->decimal('monthly_salary', 12, 2)->nullable()->after('weekly_rate');
        });
    }

    public function down(): void
    {
        Schema::table('project_employees', function (Blueprint $table) {
            $table->dropColumn(['payment_frequency', 'daily_rate', 'weekly_rate', 'monthly_salary']);
        });
    }
};
