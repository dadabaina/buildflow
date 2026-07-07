<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Lien fort vers la ligne de devis d'origine : permet de comparer
            // le prévu (déboursé sec de la ligne) au réel (dépenses de la tâche)
            // et de dédupliquer la génération des tâches autrement que par titre.
            $table->foreignId('quote_item_id')->nullable()->after('project_id')
                  ->constrained('quote_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['quote_item_id']);
            $table->dropColumn('quote_item_id');
        });
    }
};
