<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\CheckTenantActive;
use App\Models\Masjid;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AiPageGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([CheckTenantActive::class, CheckSubscription::class]);
    }

    public function test_admin_can_access_ai_presets(): void
    {
        $permission = Permission::findOrCreate('cms.manage', 'web');
        $role = Role::query()->firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['level' => 2, 'masjid_id' => null]
        );
        $role->givePermissionTo($permission);

        $admin = User::query()->create([
            'name' => 'Admin AI',
            'email' => 'admin.ai@test.com',
            'password' => 'password',
            'peranan' => 'admin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($role);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.cms.builder.presets'));

        $response->assertOk();
        $response->assertJsonStructure(['presets']);
        $response->assertJsonCount(4, 'presets');
        $this->assertTrue(is_array($response->json('presets')));
    }

    public function test_admin_cannot_access_ai_without_permission(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => 'password',
            'peranan' => 'staff',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('admin.cms.builder.generate'), [
                'type' => 'landing',
                'description' => 'A modern mosque website with donation and programs',
            ]);

        $response->assertForbidden();
    }

    public function test_generate_rejects_invalid_page_type(): void
    {
        $permission = Permission::findOrCreate('cms.manage', 'web');
        $role = Role::query()->firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['level' => 2, 'masjid_id' => null]
        );
        $role->givePermissionTo($permission);

        $admin = User::query()->create([
            'name' => 'Admin AI',
            'email' => 'admin.ai@test.com',
            'password' => 'password',
            'peranan' => 'admin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($role);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.cms.builder.generate'), [
                'type' => 'invalid_type',
                'description' => 'A modern mosque website',
            ]);

        $response->assertUnprocessable();
    }

    public function test_generate_rejects_short_description(): void
    {
        $permission = Permission::findOrCreate('cms.manage', 'web');
        $role = Role::query()->firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['level' => 2, 'masjid_id' => null]
        );
        $role->givePermissionTo($permission);

        $admin = User::query()->create([
            'name' => 'Admin AI',
            'email' => 'admin.ai@test.com',
            'password' => 'password',
            'peranan' => 'admin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($role);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.cms.builder.generate'), [
                'type' => 'landing',
                'description' => 'Short',
            ]);

        $response->assertUnprocessable();
    }

    public function test_generate_validates_component_structure(): void
    {
        $this->markTestSkipped('Requires mock AI service for consistent testing');
    }

    public function test_ai_page_generator_service_validates_components(): void
    {
        $service = app(\App\Services\AiPageGeneratorService::class);

        // Test valid component structure
        $validLayout = [
            'type' => 'hero',
            'props' => [
                'title' => 'Test Hero',
                'subtitle' => 'A test',
                'button_text' => 'Click',
                'button_link' => '/',
            ],
        ];

        $validated = $this->invokeMethod($service, 'validateComponent', [$validLayout]);

        $this->assertEquals('hero', $validated['type']);
        $this->assertIsArray($validated['props']);
        $this->assertIsArray($validated['children']);
    }

    public function test_ai_page_generator_rejects_invalid_component_type(): void
    {
        $service = app(\App\Services\AiPageGeneratorService::class);

        $invalidComponent = [
            'type' => 'invalid_component',
            'props' => ['title' => 'Test'],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->invokeMethod($service, 'validateComponent', [$invalidComponent]);
    }

    public function test_ai_page_generator_rejects_missing_type(): void
    {
        $service = app(\App\Services\AiPageGeneratorService::class);

        $invalidComponent = [
            'props' => ['title' => 'Test'],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->invokeMethod($service, 'validateComponent', [$invalidComponent]);
    }

    public function test_preset_prompts_contain_expected_fields(): void
    {
        $service = app(\App\Services\AiPageGeneratorService::class);
        $presets = $service->getPresetPrompts();

        $this->assertCount(4, $presets);

        foreach ($presets as $preset) {
            $this->assertArrayHasKey('label', $preset);
            $this->assertArrayHasKey('type', $preset);
            $this->assertArrayHasKey('description', $preset);
        }
    }

    protected function invokeMethod($object, $methodName, $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
