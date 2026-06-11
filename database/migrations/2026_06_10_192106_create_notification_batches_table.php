<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('idempotency_key')->unique();
            $table->string('initiator')->nullable();
            $table->string('channel');
            $table->string('priority');
            $table->text('message');
            $table->unsignedInteger('requested_recipients_count');
            $table->unsignedInteger('accepted_notifications_count')->default(0);
            $table->unsignedInteger('queued_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('dropped_count')->default(0);
            $table->timestamps();

            $table->index(['channel', 'priority']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_batches');
    }
};
