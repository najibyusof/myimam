<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_masjid')->constrained('masjid')->cascadeOnDelete();
            $table->date('tarikh');
            $table->string('no_resit', 50)->nullable();

            $table->foreignId('id_akaun')->constrained('akaun')->restrictOnDelete();
            $table->foreignId('id_sumber_hasil')->constrained('sumber_hasil')->restrictOnDelete();

            $table->decimal('amaun_tunai', 14, 2)->default(0);
            $table->decimal('amaun_online', 14, 2)->default(0);
            $table->decimal('jumlah', 14, 2);

            $table->foreignId('id_tabung_khas')->nullable()->constrained('tabung_khas')->nullOnDelete();
            $table->foreignId('id_program')->nullable()->constrained('program_masjid')->nullOnDelete();

            $table->enum('jenis_jumaat', ['biasa', 'ramadan', 'hariraya'])->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['id_masjid', 'tarikh']);
            $table->index(['id_masjid', 'id_sumber_hasil', 'tarikh']);
            $table->index(['id_masjid', 'id_akaun', 'tarikh']);
            $table->index(['id_masjid', 'jenis_jumaat', 'tarikh']);
            $table->unique(['id_masjid', 'no_resit']);
            $table->unique(['id_masjid', 'tarikh', 'id_sumber_hasil', 'jenis_jumaat', 'id_akaun'], 'uq_kutipan_jumaat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil');
    }
};
