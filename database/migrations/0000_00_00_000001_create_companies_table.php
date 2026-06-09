<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->default('Madagascar');
            $table->string('logo_path')->nullable();
            $table->string('currency', 10)->default('MGA');
            $table->decimal('tva_rate', 5, 2)->default(20.00);
            $table->decimal('rg_rate', 5, 2)->default(5.00);
            $table->decimal('fg_rate', 5, 2)->default(15.00);
            $table->decimal('marge_rate', 5, 2)->default(10.00);
            $table->decimal('aleas_rate', 5, 2)->default(5.00);
            $table->string('quote_prefix', 10)->default('DEV');
            $table->string('invoice_prefix', 10)->default('FAC');
            $table->string('credit_note_prefix', 10)->default('AVO');
            $table->string('purchase_order_prefix', 10)->default('BC');
            $table->string('project_prefix', 10)->default('BF');
            $table->string('plan', 20)->default('free');
            $table->timestamp('plan_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
