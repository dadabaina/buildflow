<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_types', function (Blueprint $table) {
            $table->foreignId('job_category_id')->nullable()->after('company_id')->constrained('job_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('job_category_id');
        });
    }
};
