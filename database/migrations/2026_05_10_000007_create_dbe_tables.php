<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure all foreign-key dependencies from earlier migrations exist.
        // If any are missing the database is in an inconsistent state; the
        // developer should run:  php artisan migrate:fresh
        $required = ['companies', 'material_categories', 'regions', 'job_types'];
        foreach ($required as $table) {
            if (!Schema::hasTable($table)) {
                throw new \RuntimeException(
                    "Migration [2026_05_10_000007_create_dbe_tables] requires the \"{$table}\" table " .
                    "which does not exist. Your database is in an inconsistent state. " .
                    "Fix it by running: php artisan migrate:fresh"
                );
            }
        }

        // Catalogue de matériaux de l'entreprise
        if (!Schema::hasTable('materials')) {
            Schema::create('materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('material_category_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->string('unit', 30);         // kg, m³, sac, ml, m², etc.
                $table->string('reference', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'name']);
                $table->index(['company_id', 'is_active']);
            });
        }

        // Historique des prix matériaux (par région, par date)
        if (!Schema::hasTable('material_prices')) {
            Schema::create('material_prices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('material_id')->constrained()->cascadeOnDelete();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('unit_price', 12, 2);
                $table->date('effective_date');
                $table->string('supplier_name', 150)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['material_id', 'region_id', 'effective_date']);
            });
        }

        // Grille salariale (métier × région × date)
        if (!Schema::hasTable('salary_rates')) {
            Schema::create('salary_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('job_type_id')->constrained()->cascadeOnDelete();
                $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('daily_rate', 10, 2)->default(0);
                $table->decimal('hourly_rate', 10, 2)->default(0);
                $table->date('effective_date');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'job_type_id', 'region_id', 'effective_date'], 'salary_rates_lookup_idx');
            });
        }

        // Modèles de dosage (recettes techniques)
        if (!Schema::hasTable('dosage_models')) {
            Schema::create('dosage_models', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->string('output_unit', 30);      // unité produite : m³, m², ml…
                $table->decimal('output_quantity', 10, 3)->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'name']);
                $table->index(['company_id', 'is_active']);
            });
        }

        // Lignes d'un modèle de dosage
        if (!Schema::hasTable('dosage_items')) {
            Schema::create('dosage_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dosage_model_id')->constrained()->cascadeOnDelete();
                $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
                $table->enum('item_type', ['material', 'labor', 'equipment', 'subcontract'])->default('material');
                $table->string('description', 150);
                $table->string('unit', 30);
                $table->decimal('quantity_per_unit', 12, 4);
                $table->decimal('waste_rate', 5, 2)->default(0);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['dosage_model_id', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dosage_items');
        Schema::dropIfExists('dosage_models');
        Schema::dropIfExists('salary_rates');
        Schema::dropIfExists('material_prices');
        Schema::dropIfExists('materials');
    }
};
