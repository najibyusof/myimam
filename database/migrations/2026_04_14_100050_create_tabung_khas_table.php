<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabung_khas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_masjid')->constrained('masjid')->cascadeOnDelete();
            $table->string('nama_tabung', 150);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['id_masjid', 'nama_tabung']);
            $table->index(['id_masjid', 'aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabung_khas');
    }
};
