<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masjid_id')->nullable()->constrained('masjid')->nullOnDelete();
            $table->string('page_name', 120);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['masjid_id', 'page_name']);
            $table->index('page_name');
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('section', 120);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['page_id', 'section']);
            $table->index(['section', 'is_active']);
        });

        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('content_key', 120);
            $table->longText('content_text')->nullable();
            $table->json('content_json')->nullable();
            $table->string('image_path', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['section_id', 'content_key']);
            $table->index('content_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('pages');
    }
};
