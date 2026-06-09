<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference', 50);
            $table->string('title', 150);
            $table->date('report_date');
            $table->string('location', 200)->nullable();
            $table->json('participants')->nullable();
            $table->string('weather', 50)->nullable();
            $table->text('content')->nullable();
            $table->date('next_meeting_date')->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'project_id']);
        });

        Schema::create('site_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_report_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->string('responsible', 100)->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_report_items');
        Schema::dropIfExists('site_reports');
    }
};
