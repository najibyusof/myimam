<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktiviti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_masjid')->nullable()->constrained('masjid')->nullOnDelete();
            $table->foreignId('id_user')->nullable()->constrained('users')->nullOnDelete();
            $table->string('jenis', 50);
            $table->string('modul', 100)->nullable();
            $table->string('aksi', 50)->nullable();
            $table->unsignedBigInteger('rujukan_id')->nullable();
            $table->text('butiran')->nullable();
            $table->longText('data_lama')->nullable();
            $table->longText('data_baru')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['id_masjid', 'created_at']);
            $table->index(['id_user', 'created_at']);
            $table->index(['jenis', 'created_at']);
            $table->index(['modul', 'aksi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktiviti');
    }
};
