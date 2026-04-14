<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akaun', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_masjid')->constrained('masjid')->cascadeOnDelete();
            $table->string('nama_akaun', 150);
            $table->enum('jenis', ['tunai', 'bank', 'lain'])->default('tunai');
            $table->string('no_akaun', 100)->nullable();
            $table->string('nama_bank', 150)->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->index(['id_masjid', 'jenis']);
            $table->index(['id_masjid', 'status_aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akaun');
    }
};
