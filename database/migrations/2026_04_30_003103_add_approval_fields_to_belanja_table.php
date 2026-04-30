<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('belanja', function (Blueprint $table) {
            $table->unsignedBigInteger('ditolak_oleh')->nullable()->after('dilulus_oleh');
            $table->timestamp('tarikh_tolak')->nullable()->after('tarikh_lulus');
            $table->text('catatan_tolak')->nullable()->after('tarikh_tolak');
        });
    }

    public function down(): void
    {
        Schema::table('belanja', function (Blueprint $table) {
            $table->dropColumn(['ditolak_oleh', 'tarikh_tolak', 'catatan_tolak']);
        });
    }
};
