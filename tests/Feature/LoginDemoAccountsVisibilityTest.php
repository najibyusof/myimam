<?php

namespace Tests\Feature;

use App\Models\CmsBuilderPage;
use App\Services\LoginDemoAccountService;
use Database\Seeders\CmsSeeder;
use Database\Seeders\MasjidSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginDemoAccountsVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_dynamic_demo_accounts_when_enabled(): void
    {
        $this->seed(MasjidSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(CmsSeeder::class);

        /** @var LoginDemoAccountService $service */
        $service = app(LoginDemoAccountService::class);
        $demoData = $service->forLoginPage(null);

        $this->assertCount(5, $demoData['accounts']);

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee(__('auth.demo_accounts'));

        foreach ($demoData['accounts'] as $account) {
            $response->assertSee($account['label']);
            $response->assertSee($account['email']);
        }
    }

    public function test_login_page_hides_demo_accounts_when_disabled_in_cms_component(): void
    {
        $this->seed(MasjidSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(CmsSeeder::class);

        $page = CmsBuilderPage::query()
            ->where('slug', 'login')
            ->whereNull('masjid_id')
            ->firstOrFail();

        $layout = $page->content_json ?? [];
        $components = is_array($layout['components'] ?? null) ? $layout['components'] : [];

        foreach ($components as $index => $component) {
            if (($component['type'] ?? null) !== 'login_form') {
                continue;
            }

            $props = is_array($component['props'] ?? null) ? $component['props'] : [];
            $props['show_demo_accounts'] = '0';
            $components[$index]['props'] = $props;
        }

        $layout['components'] = $components;
        $page->update(['content_json' => $layout]);

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertDontSee(__('auth.demo_accounts'));
        $response->assertDontSee('superadmin@imam.com');
    }
}
