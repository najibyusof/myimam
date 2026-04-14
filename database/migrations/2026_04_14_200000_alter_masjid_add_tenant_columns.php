<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('masjid', function (Blueprint $table) {
            // Unique short code for subdomain/slug identification e.g. "masjid-al-hidayah"
            $table->string('code', 100)->unique()->nullable()->after('nama');

            // Tenant lifecycle status
            $table->enum('status', ['active', 'suspended', 'pending'])
                ->default('pending')
                ->after('no_pendaftaran');

            // Subscription mirror (denormalized for quick middleware checks)
            $table->enum('subscription_status', ['active', 'expired', 'trial', 'none'])
                ->default('none')
                ->after('status');

            $table->timestamp('subscription_expiry')->nullable()->after('subscription_status');

            // SuperAdmin who created this tenant record
            $table->foreignId('created_by')
                ->nullable()
                ->after('subscription_expiry')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('code');
            $table->index(['status', 'subscription_status']);
        });
    }

    public function down(): void
    {
        Schema::table('masjid', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropIndex('masjid_code_unique');
            $table->dropIndex('masjid_status_subscription_status_index');
            $table->dropColumn(['code', 'status', 'subscription_status', 'subscription_expiry', 'created_by']);
        });
    }
};
