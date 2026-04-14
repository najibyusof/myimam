<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_masjid', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_masjid')->constrained('masjid')->cascadeOnDelete();
            $table->string('nama_program', 150);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['id_masjid', 'nama_program']);
            $table->index(['id_masjid', 'aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_masjid');
    }
};
