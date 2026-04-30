<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('belanja', function (Blueprint $table) {
            $table->unsignedTinyInteger('approval_step')->default(0)->after('tarikh_lulus');

            $table->unsignedBigInteger('bendahari_lulus_oleh')->nullable()->after('approval_step');
            $table->timestamp('bendahari_lulus_pada')->nullable()->after('bendahari_lulus_oleh');
            $table->string('bendahari_signature', 64)->nullable()->after('bendahari_lulus_pada');

            $table->unsignedBigInteger('pengerusi_lulus_oleh')->nullable()->after('bendahari_signature');
            $table->timestamp('pengerusi_lulus_pada')->nullable()->after('pengerusi_lulus_oleh');
            $table->string('pengerusi_signature', 64)->nullable()->after('pengerusi_lulus_pada');

            $table->boolean('is_baucar_locked')->default(false)->after('pengerusi_signature');
            $table->timestamp('locked_at')->nullable()->after('is_baucar_locked');
            $table->unsignedBigInteger('locked_by')->nullable()->after('locked_at');

            $table->foreign('bendahari_lulus_oleh')->references('id')->on('users')->nullOnDelete();
            $table->foreign('pengerusi_lulus_oleh')->references('id')->on('users')->nullOnDelete();
            $table->foreign('locked_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['id_masjid', 'approval_step', 'is_deleted']);
            $table->index(['id_masjid', 'is_baucar_locked', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('belanja', function (Blueprint $table) {
            $table->dropIndex(['id_masjid', 'approval_step', 'is_deleted']);
            $table->dropIndex(['id_masjid', 'is_baucar_locked', 'status']);

            $table->dropForeign(['bendahari_lulus_oleh']);
            $table->dropForeign(['pengerusi_lulus_oleh']);
            $table->dropForeign(['locked_by']);

            $table->dropColumn([
                'approval_step',
                'bendahari_lulus_oleh',
                'bendahari_lulus_pada',
                'bendahari_signature',
                'pengerusi_lulus_oleh',
                'pengerusi_lulus_pada',
                'pengerusi_signature',
                'is_baucar_locked',
                'locked_at',
                'locked_by',
            ]);
        });
    }
};
