<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        // Insert default categories for existing companies
        $companies = DB::table('companies')->pluck('id');
        foreach ($companies as $companyId) {
            DB::table('job_categories')->insert([
                ['company_id' => $companyId, 'name' => 'Études et conception', 'created_at' => now(), 'updated_at' => now()],
                ['company_id' => $companyId, 'name' => 'Encadrement et gestion de chantier', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_categories');
    }
};
