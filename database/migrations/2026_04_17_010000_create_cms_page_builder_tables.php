<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masjid_id')->nullable()->constrained('masjid')->nullOnDelete();
            $table->string('slug', 120);
            $table->string('title', 200);
            $table->longText('content_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['masjid_id', 'slug']);
            $table->index(['slug', 'is_active']);
        });

        Schema::create('cms_components', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('type', 80);
            $table->json('schema_json')->nullable();
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_components');
        Schema::dropIfExists('cms_pages');
    }
};
