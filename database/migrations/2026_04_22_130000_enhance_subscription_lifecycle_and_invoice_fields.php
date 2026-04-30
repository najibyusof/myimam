<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('is_trial')->default(false)->after('status');
            $table->dateTime('trial_ends_at')->nullable()->after('end_date');
            $table->boolean('auto_renew')->default(true)->after('trial_ends_at');
            $table->dateTime('reminder_sent_at')->nullable()->after('auto_renew');
            $table->unsignedBigInteger('renewal_of_id')->nullable()->after('reminder_sent_at');

            $table->index(['is_trial', 'status']);
            $table->index(['auto_renew', 'status', 'end_date']);
            $table->foreign('renewal_of_id')->references('id')->on('subscriptions')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('invoice_no', 60)->nullable()->after('reference_id');
            $table->string('invoice_path')->nullable()->after('invoice_no');

            $table->index('invoice_no');
        });

        Schema::table('masjid', function (Blueprint $table) {
            $table->string('whatsapp_no', 30)->nullable()->after('no_pendaftaran');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['renewal_of_id']);
            $table->dropIndex(['is_trial', 'status']);
            $table->dropIndex(['auto_renew', 'status', 'end_date']);
            $table->dropColumn([
                'is_trial',
                'trial_ends_at',
                'auto_renew',
                'reminder_sent_at',
                'renewal_of_id',
            ]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['invoice_no']);
            $table->dropColumn(['invoice_no', 'invoice_path']);
        });

        Schema::table('masjid', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_no']);
        });
    }
};
