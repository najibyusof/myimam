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
            $table->string('no_baucar', 30)->nullable()->unique()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('belanja', function (Blueprint $table) {
            $table->dropUnique(['no_baucar']);
            $table->dropColumn('no_baucar');
        });
    }
};
