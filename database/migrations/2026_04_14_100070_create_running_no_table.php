<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_no', function (Blueprint $table) {
            $table->foreignId('id_masjid')->constrained('masjid')->cascadeOnDelete();
            $table->string('prefix', 20);
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('bulan');
            $table->unsignedInteger('last_no')->default(0);
            $table->timestamps();

            $table->primary(['id_masjid', 'prefix', 'tahun', 'bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_no');
    }
};
