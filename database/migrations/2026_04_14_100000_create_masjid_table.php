<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->text('alamat')->nullable();
            $table->string('daerah', 100)->nullable();
            $table->string('negeri', 100)->nullable();
            $table->string('no_pendaftaran', 100)->nullable();
            $table->date('tarikh_daftar')->nullable();
            $table->timestamps();

            $table->index(['negeri', 'daerah']);
            $table->index('nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid');
    }
};
