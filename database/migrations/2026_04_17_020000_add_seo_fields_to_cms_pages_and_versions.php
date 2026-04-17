<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->string('seo_title', 200)->nullable()->after('title');
            $table->string('seo_meta_description', 320)->nullable()->after('seo_title');
        });

        Schema::table('cms_page_versions', function (Blueprint $table) {
            $table->string('seo_title', 200)->nullable()->after('title');
            $table->string('seo_meta_description', 320)->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('cms_page_versions', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_meta_description']);
        });

        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_meta_description']);
        });
    }
};
