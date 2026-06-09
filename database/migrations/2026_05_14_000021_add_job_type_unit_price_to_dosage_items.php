<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dosage_items', function (Blueprint $table) {
            // Pour les lignes de type "labor" → pointe vers la grille salariale
            $table->foreignId('job_type_id')->nullable()->after('material_id')
                  ->constrained('job_types')->nullOnDelete();

            // Pour les types "equipment" et "subcontract" → prix unitaire direct
            $table->decimal('unit_price', 15, 2)->nullable()->after('waste_rate');
        });
    }

    public function down(): void
    {
        Schema::table('dosage_items', function (Blueprint $table) {
            $table->dropForeign(['job_type_id']);
            $table->dropColumn(['job_type_id', 'unit_price']);
        });
    }
};
