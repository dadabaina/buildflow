<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('reference')->unique(); // SIT-YYYY-NNN-Sxx
            $table->string('title');
            $table->unsignedTinyInteger('situation_number')->default(1);
            $table->date('billing_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['brouillon', 'envoye', 'valide', 'facture', 'annule'])->default('brouillon');
            $table->decimal('subtotal_ht', 15, 2)->default(0);
            $table->decimal('rg_rate', 5, 2)->default(5);    // retenue de garantie %
            $table->decimal('rg_amount', 15, 2)->default(0);
            $table->decimal('tva_rate', 5, 2)->default(20);
            $table->decimal('tva_amount', 15, 2)->default(0);
            $table->decimal('total_ttc', 15, 2)->default(0);
            $table->decimal('net_to_pay', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'project_id']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('progress_billing_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_billing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('quote_quantity', 12, 3)->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('unit', 30)->nullable();
            $table->decimal('previous_pct', 6, 2)->default(0);    // % cumulé situations précédentes
            $table->decimal('current_pct', 6, 2)->default(0);     // % cette situation
            $table->decimal('cumulative_pct', 6, 2)->storedAs('previous_pct + current_pct');
            $table->decimal('current_amount', 15, 2)->storedAs('ROUND((current_pct / 100) * (quote_quantity * unit_price), 2)');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_billing_lines');
        Schema::dropIfExists('progress_billings');
    }
};
