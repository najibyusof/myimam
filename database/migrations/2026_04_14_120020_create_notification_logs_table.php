<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('notification_id')->nullable();
            $table->string('channel'); // email, telegram, fcm, database
            $table->morphs('notifiable');
            $table->string('subject')->nullable();
            $table->longText('message');
            $table->string('status'); // pending, sent, failed
            $table->string('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // morphs('notifiable') already creates an index on
            // ['notifiable_type', 'notifiable_id'].
            // Do not add that composite index again.
            $table->index(['channel', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
