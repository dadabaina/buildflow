<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajoute le type "other" (Divers) pour les lignes qui ne sont ni matériau,
        // ni main-d'œuvre, ni matériel, ni sous-traitance (ex: implantation, EPI,
        // frais généraux, nettoyage...).
        DB::statement("ALTER TABLE expense_template_items MODIFY item_type ENUM('material','labor','equipment','subcontract','other') NOT NULL DEFAULT 'material'");
    }

    public function down(): void
    {
        DB::statement("UPDATE expense_template_items SET item_type = 'subcontract' WHERE item_type = 'other'");
        DB::statement("ALTER TABLE expense_template_items MODIFY item_type ENUM('material','labor','equipment','subcontract') NOT NULL DEFAULT 'material'");
    }
};
