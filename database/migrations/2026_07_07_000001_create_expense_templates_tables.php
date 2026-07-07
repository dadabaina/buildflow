<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modèles de dépense (sous-détails de prix) — indépendants des Modèles de Dosage.
        Schema::create('expense_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('output_unit', 30);      // unité produite : m³, m², ml, forfait…
            $table->decimal('output_quantity', 10, 3)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
            $table->index(['company_id', 'is_active']);
        });

        // Lignes d'un modèle de dépense (matériaux, main-d'œuvre, matériel, sous-traitance)
        Schema::create('expense_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_type_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('item_type', ['material', 'labor', 'equipment', 'subcontract'])->default('material');
            $table->string('description', 150);
            $table->string('unit', 30);
            $table->decimal('quantity_per_unit', 12, 4);
            $table->decimal('waste_rate', 5, 2)->default(0);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['expense_template_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_template_items');
        Schema::dropIfExists('expense_templates');
    }
};
