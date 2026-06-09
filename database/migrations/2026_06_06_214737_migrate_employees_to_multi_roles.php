<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $employees = DB::table('employees')->whereNotNull('job_type_id')->get();

        foreach ($employees as $employee) {
            DB::table('employee_job_type')->insertOrIgnore([
                'employee_id' => $employee->id,
                'job_type_id' => $employee->job_type_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('employee_job_type')->truncate();
    }
};
