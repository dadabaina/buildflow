<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_digest_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('notification_type');
            $table->date('digest_date');
            $table->unsignedInteger('items_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Garde-fou : jamais plus d'un digest envoyé pour un même type/jour/société,
            // même si la commande planifiée est relancée deux fois le même jour.
            // Nom raccourci explicite : le nom auto-généré dépasse la limite MySQL de 64 caractères.
            $table->unique(['company_id', 'notification_type', 'digest_date'], 'digest_logs_company_type_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_digest_logs');
    }
};
