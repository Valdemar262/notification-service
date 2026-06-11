<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('notification_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
            $table->string('channel');
            $table->string('priority');
            $table->string('status')->default('queued');
            $table->string('recipient_address');
            $table->string('provider')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->string('business_idempotency_key')->unique();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('dropped_at')->nullable();
            $table->timestamps();

            $table->unique(['notification_batch_id', 'subscriber_id', 'channel'], 'notifications_batch_subscriber_channel_unique');
            $table->index(['subscriber_id', 'created_at']);
            $table->index(['subscriber_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index('provider_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
