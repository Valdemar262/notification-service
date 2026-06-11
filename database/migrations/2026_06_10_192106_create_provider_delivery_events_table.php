<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_delivery_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('provider_message_id');
            $table->string('event_type');
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_message_id', 'event_type'], 'provider_delivery_events_unique');
            $table->index('processed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_delivery_events');
    }
};
