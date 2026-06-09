<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->unsignedSmallInteger('version')->default(1)->after('status');
            $table->foreignId('parent_quote_id')
                ->nullable()
                ->after('version')
                ->constrained('quotes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeign(['parent_quote_id']);
            $table->dropColumn(['version', 'parent_quote_id']);
        });
    }
};
