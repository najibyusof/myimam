<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cms_page_id')->nullable()->constrained('cms_pages')->nullOnDelete();
            $table->foreignId('masjid_id')->nullable()->constrained('masjid')->nullOnDelete();
            $table->string('slug', 120);
            $table->unsignedInteger('version_no');
            $table->string('title', 200);
            $table->longText('content_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('action', 40)->default('save');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['masjid_id', 'slug', 'version_no'], 'cms_page_versions_scope_unique');
            $table->index(['masjid_id', 'slug', 'created_at'], 'cms_page_versions_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_page_versions');
    }
};
