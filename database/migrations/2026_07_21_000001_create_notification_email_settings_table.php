<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_email_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('notification_type');
            $table->json('emails')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'notification_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_email_settings');
    }
};
