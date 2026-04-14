<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pindahan_akaun', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_masjid')->constrained('masjid')->cascadeOnDelete();
            $table->date('tarikh');
            $table->foreignId('dari_akaun_id')->constrained('akaun')->restrictOnDelete();
            $table->foreignId('ke_akaun_id')->constrained('akaun')->restrictOnDelete();
            $table->decimal('amaun', 14, 2);
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['id_masjid', 'tarikh']);
            $table->index(['id_masjid', 'dari_akaun_id', 'tarikh']);
            $table->index(['id_masjid', 'ke_akaun_id', 'tarikh']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pindahan_akaun');
    }
};
