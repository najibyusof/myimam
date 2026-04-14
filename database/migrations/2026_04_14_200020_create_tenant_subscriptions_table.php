<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masjid_id')
                ->constrained('masjid')
                ->cascadeOnDelete();
            $table->foreignId('plan_id')
                ->constrained('subscription_plans')
                ->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expired', 'cancelled', 'grace'])
                ->default('active');
            $table->unsignedSmallInteger('grace_days')->default(7); // grace period after expiry
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_reference', 150)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['masjid_id', 'status']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_subscriptions');
    }
};
