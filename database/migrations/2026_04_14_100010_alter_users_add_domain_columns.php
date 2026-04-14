<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('id_masjid')
                ->nullable()
                ->after('id')
                ->constrained('masjid')
                ->nullOnDelete();

            $table->enum('peranan', ['superadmin', 'admin', 'staff'])
                ->default('staff')
                ->after('password');

            $table->boolean('aktif')
                ->default(true)
                ->after('peranan');

            $table->index(['id_masjid', 'peranan']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_id_masjid_peranan_index');
            $table->dropConstrainedForeignId('id_masjid');
            $table->dropColumn(['peranan', 'aktif']);
        });
    }
};
