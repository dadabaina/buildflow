<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('reference')->unique(); // AVN-YYYY-NNN
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['brouillon', 'envoye', 'accepte', 'refuse', 'annule'])->default('brouillon');
            $table->decimal('subtotal_ht', 15, 2)->default(0);
            $table->decimal('tva_rate', 5, 2)->default(20);
            $table->decimal('tva_amount', 15, 2)->default(0);
            $table->decimal('total_ttc', 15, 2)->default(0);
            $table->string('client_token', 64)->nullable()->unique();
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'project_id']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('amendment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amendment_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->string('unit', 30)->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_ht', 15, 2)->storedAs('quantity * unit_price');
            $table->boolean('is_deduction')->default(false); // negative line
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amendment_items');
        Schema::dropIfExists('amendments');
    }
};
