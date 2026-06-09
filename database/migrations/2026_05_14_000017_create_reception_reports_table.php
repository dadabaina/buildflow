<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reception_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('reference', 30)->nullable();
            $table->date('reception_date');
            $table->string('client_name', 200)->nullable();
            $table->text('reserves')->nullable();   // description des réserves
            $table->decimal('rg_amount', 15, 2)->default(0);
            $table->date('rg_release_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['brouillon', 'signe', 'rg_libere'])->default('brouillon');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reception_reports');
    }
};
