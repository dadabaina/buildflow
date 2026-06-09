<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->boolean('is_internal')->default(true)->after('acquisition_cost');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null')->after('is_internal');
        });
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['is_internal', 'supplier_id']);
        });
    }
};
