<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_belanja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_masjid')->constrained('masjid')->cascadeOnDelete();
            $table->string('kod', 20);
            $table->string('nama_kategori', 150);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['id_masjid', 'kod']);
            $table->index(['id_masjid', 'aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_belanja');
    }
};
