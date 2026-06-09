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
        Schema::create('employee_job_type', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('employee_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('job_type_id')->constrained()->onDelete('cascade');
            $blueprint->timestamps();

            $blueprint->unique(['employee_id', 'job_type_id']);
        });

        // Optionnel : On garde job_type_id sur employees comme "Poste principal" 
        // ou on peut décider de le supprimer plus tard.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_job_type');
    }
};
