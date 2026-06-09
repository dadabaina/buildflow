<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // En MySQL, pour modifier un ENUM, on utilise souvent une commande native car change() peut être capricieux sur les ENUMs selon la version de DBAL
        DB::statement("ALTER TABLE employees MODIFY COLUMN contract_type ENUM('cdi', 'cdd', 'interim', 'sous_traitant', 'journalier', 'mensuel') NOT NULL DEFAULT 'cdd'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE employees MODIFY COLUMN contract_type ENUM('cdi', 'cdd', 'interim', 'sous_traitant', 'journalier') NOT NULL DEFAULT 'cdd'");
    }
};
