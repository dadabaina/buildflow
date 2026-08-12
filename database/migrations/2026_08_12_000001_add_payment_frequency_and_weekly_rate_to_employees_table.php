<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('payment_frequency', 20)->default('journalier')->after('contract_type');
            $table->decimal('weekly_rate', 12, 2)->nullable()->after('daily_rate');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['payment_frequency', 'weekly_rate']);
        });
    }
};
