<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('belanja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_masjid')->constrained('masjid')->cascadeOnDelete();
            $table->date('tarikh');
            $table->foreignId('id_akaun')->constrained('akaun')->restrictOnDelete();
            $table->foreignId('id_kategori_belanja')->constrained('kategori_belanja')->restrictOnDelete();
            $table->decimal('amaun', 14, 2);

            $table->foreignId('id_tabung_khas')->nullable()->constrained('tabung_khas')->nullOnDelete();
            $table->foreignId('id_program')->nullable()->constrained('program_masjid')->nullOnDelete();

            $table->string('penerima', 190)->nullable();
            $table->text('catatan')->nullable();
            $table->string('bukti_fail', 255)->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['DRAF', 'LULUS'])->default('DRAF');

            $table->foreignId('id_baucar')->nullable()->constrained('baucar_bayaran')->nullOnDelete();

            $table->boolean('is_deleted')->default(false);
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('deleted_at')->nullable();

            $table->foreignId('dilulus_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('tarikh_lulus')->nullable();

            $table->timestamps();

            $table->index(['id_masjid', 'status', 'is_deleted', 'tarikh']);
            $table->index(['id_masjid', 'id_baucar', 'status', 'is_deleted']);
            $table->index(['id_masjid', 'id_akaun', 'tarikh']);
            $table->index(['id_masjid', 'id_kategori_belanja', 'tarikh']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('belanja');
    }
};
