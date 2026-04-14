<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('baucar_bayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_masjid')->constrained('masjid')->cascadeOnDelete();
            $table->date('tarikh');
            $table->string('no_baucar', 50);
            $table->foreignId('id_akaun')->constrained('akaun')->restrictOnDelete();
            $table->enum('kaedah', ['tunai', 'bank'])->default('tunai');
            $table->string('no_rujukan', 100)->nullable();
            $table->decimal('jumlah', 14, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->enum('status', ['DRAF', 'LULUS'])->default('DRAF');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('dilulus_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('tarikh_lulus')->nullable();
            $table->timestamps();

            $table->unique(['id_masjid', 'no_baucar']);
            $table->index(['id_masjid', 'status', 'tarikh']);
            $table->index(['id_masjid', 'id_akaun', 'tarikh']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('baucar_bayaran');
    }
};
