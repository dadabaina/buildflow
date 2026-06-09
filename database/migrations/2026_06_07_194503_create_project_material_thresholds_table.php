<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_material_thresholds', function (Blueprint $row) {
            $row->id();
            $row->foreignId('company_id')->constrained()->onDelete('cascade');
            $row->foreignId('project_id')->constrained()->onDelete('cascade');
            $row->foreignId('material_id')->constrained()->onDelete('cascade');
            $row->decimal('min_threshold', 15, 3)->default(0);
            $row->timestamps();
            
            $row->unique(['project_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_material_thresholds');
    }
};
