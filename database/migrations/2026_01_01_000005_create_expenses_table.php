<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('description');
            $table->date('expense_date');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->string('unit', 30)->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 15, 2)->storedAs('quantity * unit_price');
            $table->string('payment_mode', 50)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['saisie', 'validee', 'rejetee'])->default('saisie');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'project_id']);
            $table->index(['company_id', 'expense_date']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
