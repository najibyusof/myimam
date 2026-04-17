<?php

namespace Tests\Feature;

use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsBuilderPublicRenderSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_home_page_renders_premium_builder_content(): void
    {
        $this->seed(CmsSeeder::class);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Platform Operasi Kewangan Masjid Dengan Penyampaian Yang Lebih Profesional');
        $response->assertSee('/cms/defaults/landing-premium.svg');
        $response->assertSee('MyImam CMS Experience');
        $response->assertSee('Mula Dengan MyImam');
    }

    public function test_seeded_login_page_renders_premium_builder_content(): void
    {
        $this->seed(CmsSeeder::class);

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('AKSES DEMO');
        $response->assertSee('Sistem Pengurusan Kewangan Masjid');
        $response->assertSee('Log Masuk');
    }
}
