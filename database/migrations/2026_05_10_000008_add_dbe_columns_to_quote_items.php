<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            // Lien vers le modèle de dosage utilisé pour calculer cette ligne
            $table->foreignId('dosage_model_id')
                  ->nullable()
                  ->after('quote_section_id')
                  ->constrained('dosage_models')
                  ->nullOnDelete();

            // Déboursé Estimatif (DBE) — coût réel chantier, ventilé par nature
            $table->decimal('dbe_materials', 15, 2)->default(0)->after('total_ht');
            $table->decimal('dbe_labor',     15, 2)->default(0)->after('dbe_materials');
            $table->decimal('dbe_equipment', 15, 2)->default(0)->after('dbe_labor');
            $table->decimal('dbe_subcontract',15, 2)->default(0)->after('dbe_equipment');
            $table->decimal('dbe_total',      15, 2)->default(0)->after('dbe_subcontract');

            // Coefficients de marge appliqués au DBE
            $table->decimal('fg_rate',     5, 2)->default(0)->after('dbe_total'); // Frais généraux %
            $table->decimal('margin_rate', 5, 2)->default(0)->after('fg_rate');   // Marge %
            $table->decimal('alea_rate',   5, 2)->default(0)->after('margin_rate'); // Aléas %

            // Prix saisi manuellement (ignore le calcul DBE auto)
            $table->boolean('price_override')->default(false)->after('alea_rate');
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropForeign(['dosage_model_id']);
            $table->dropColumn([
                'dosage_model_id',
                'dbe_materials', 'dbe_labor', 'dbe_equipment', 'dbe_subcontract', 'dbe_total',
                'fg_rate', 'margin_rate', 'alea_rate', 'price_override',
            ]);
        });
    }
};
