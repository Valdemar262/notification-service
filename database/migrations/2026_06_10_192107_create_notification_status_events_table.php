<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_status_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('reason')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['notification_id', 'created_at']);
            $table->index(['to_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_status_events');
    }
};
