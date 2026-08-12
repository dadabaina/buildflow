<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('task_id')->nullable()->after('employee_id')->constrained()->nullOnDelete();
            $table->string('task_note')->nullable()->after('task_id');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropColumn(['task_id', 'task_note']);
        });
    }
};
