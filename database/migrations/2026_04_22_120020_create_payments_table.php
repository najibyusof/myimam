<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('masjid')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('gateway', 40);
            $table->string('reference_id', 150)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['gateway', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
