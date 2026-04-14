<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsContent;
use App\Models\Masjid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_landing_page_uses_global_cms_content(): void
    {
        $this->seedLandingPage(null, 'Global Title', ['Global Feature One', 'Global Feature Two']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Global Title');
        $response->assertSee('Global Feature One');
    }

    public function test_query_parameter_can_resolve_tenant_specific_landing_content(): void
    {
        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Al-Hidayah',
            'code' => 'al-hidayah',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->seedLandingPage(null, 'Global Title', ['Global Feature']);
        $this->seedLandingPage($masjid->id, 'Tenant Title', ['Tenant Feature']);

        $response = $this->get('/?masjid=al-hidayah');

        $response->assertOk();
        $response->assertSee('Tenant Title');
        $response->assertSee('Tenant Feature');
        $response->assertSee('Masjid Al-Hidayah');
    }

    public function test_subdomain_can_resolve_tenant_specific_landing_content(): void
    {
        config()->set('app.url', 'https://app.test');

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Ar-Rahmah',
            'code' => 'ar-rahmah',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->seedLandingPage($masjid->id, 'Subdomain Tenant Title', ['Subdomain Feature']);

        $response = $this->get('http://ar-rahmah.app.test/');

        $response->assertOk();
        $response->assertSee('Subdomain Tenant Title');
        $response->assertSee('Subdomain Feature');
    }

    public function test_inactive_tenant_page_falls_back_to_global_content(): void
    {
        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Fallback',
            'code' => 'fallback',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->seedLandingPage(null, 'Global Title', ['Global Feature']);
        $this->seedLandingPage($masjid->id, 'Inactive Tenant Title', ['Inactive Feature'], false);

        $response = $this->get('/?masjid=fallback');

        $response->assertOk();
        $response->assertSee('Global Title');
        $response->assertSee('Global Feature');
        $response->assertDontSee('Inactive Tenant Title');
    }

    public function test_query_selected_tenant_is_persisted_in_session_for_follow_up_requests(): void
    {
        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Session',
            'code' => 'session-masjid',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->seedLandingPage($masjid->id, 'Session Tenant Title', ['Session Feature']);

        $firstResponse = $this->get('/?masjid=session-masjid');

        $firstResponse->assertOk();
        $firstResponse->assertSessionHas('tenant.masjid_id', $masjid->id);
        $firstResponse->assertSee('Session Tenant Title');

        $secondResponse = $this->get('/');

        $secondResponse->assertOk();
        $secondResponse->assertSee('Session Tenant Title');
        $secondResponse->assertSee('session');
    }

    public function test_login_screen_can_reuse_session_resolved_tenant_context(): void
    {
        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Login Session',
            'code' => 'login-session',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->withSession(['tenant.masjid_id' => $masjid->id]);

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSessionHas('tenant.masjid_id', $masjid->id);
    }

    private function seedLandingPage(?int $masjidId, string $title, array $features, bool $isActive = true): void
    {
        $page = CmsPage::query()->create([
            'masjid_id' => $masjidId,
            'page_name' => 'landing',
            'is_active' => $isActive,
        ]);

        $hero = CmsSection::query()->create([
            'page_id' => $page->id,
            'section' => 'hero',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $featureSection = CmsSection::query()->create([
            'page_id' => $page->id,
            'section' => 'features',
            'sort_order' => 20,
            'is_active' => true,
        ]);

        $footer = CmsSection::query()->create([
            'page_id' => $page->id,
            'section' => 'footer',
            'sort_order' => 30,
            'is_active' => true,
        ]);

        CmsContent::query()->create([
            'section_id' => $hero->id,
            'content_key' => 'title',
            'content_text' => $title,
            'sort_order' => 10,
        ]);

        CmsContent::query()->create([
            'section_id' => $featureSection->id,
            'content_key' => 'items',
            'content_json' => $features,
            'sort_order' => 10,
        ]);

        CmsContent::query()->create([
            'section_id' => $footer->id,
            'content_key' => 'text',
            'content_text' => 'Footer Text',
            'sort_order' => 10,
        ]);
    }
}
