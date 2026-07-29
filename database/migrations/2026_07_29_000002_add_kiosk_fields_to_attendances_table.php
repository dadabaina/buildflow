<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('photo_path_in')->nullable()->after('photo_path');
            $table->string('photo_path_out')->nullable()->after('photo_path_in');
            $table->foreignId('checked_in_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('checked_out_by')->nullable()->after('checked_in_by')->constrained('users')->nullOnDelete();
            $table->unique(['employee_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'work_date']);
            $table->dropConstrainedForeignId('checked_out_by');
            $table->dropConstrainedForeignId('checked_in_by');
            $table->dropColumn(['photo_path_in', 'photo_path_out']);
        });
    }
};
