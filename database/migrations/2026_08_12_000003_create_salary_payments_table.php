<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('reference', 30)->nullable();
            $table->date('payment_date');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('payment_mode', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'payment_date']);
            $table->index(['company_id', 'employee_id']);
        });

        Schema::create('salary_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->unique(['salary_payment_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payment_allocations');
        Schema::dropIfExists('salary_payments');
    }
};
