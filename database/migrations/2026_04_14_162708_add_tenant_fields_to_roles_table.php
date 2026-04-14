<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'masjid_id')) {
                $table->unsignedBigInteger('masjid_id')->nullable()->after('guard_name');
            }

            if (!Schema::hasColumn('roles', 'level')) {
                $table->tinyInteger('level')->unsigned()->default(3)->after('masjid_id');
            }

            // Add FK only if it hasn't been added yet
            $fks = collect(Schema::getForeignKeys('roles'))->pluck('foreign_key')->flatten();
            if (!$fks->contains('masjid_id')) {
                $table->foreign('masjid_id')
                    ->references('id')
                    ->on('masjid')
                    ->nullOnDelete();
            }

            if (!Schema::hasIndex('roles', 'roles_masjid_id_index')) {
                $table->index('masjid_id', 'roles_masjid_id_index');
            }
        });

        // Back-fill level for existing global roles
        DB::table('roles')->where('name', 'Admin')->whereNull('masjid_id')->update(['level' => 2]);
        DB::table('roles')
            ->whereIn('name', ['Manager', 'FinanceOfficer', 'Bendahari', 'AJK', 'Auditor', 'MasjidOfficer', 'User'])
            ->whereNull('masjid_id')
            ->update(['level' => 3]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['masjid_id']);
            $table->dropIndex('roles_masjid_id_index');
            $table->dropColumn(['masjid_id', 'level']);
        });
    }
};
