<?php

namespace Tests\Feature;

use App\Models\Masjid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SidebarMenuVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_response_renders_sidebar_with_only_authorized_links_for_tenant_user(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->ensurePermissions([
            'dashboard.view',
            'akaun.view',
            'users.view',
            'reports.view',
        ]);

        $role = Role::query()->firstOrCreate([
            'name' => 'AJK',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions(['dashboard.view', 'akaun.view']);

        $masjid = $this->createActiveMasjid('Masjid Sidebar Integration A');

        $user = User::factory()->create([
            'id_masjid' => $masjid->id,
            'peranan' => 'staff',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Papan Pemuka');
        $response->assertSee('Akaun');
        $response->assertDontSee('Pengurusan Pengguna');
        $response->assertDontSee('Laporan');
    }

    public function test_dashboard_response_renders_all_existing_sidebar_links_for_superadmin(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdminRole = Role::query()->firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ]);

        $superAdmin = User::factory()->create([
            'id_masjid' => null,
            'peranan' => 'superadmin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole($superAdminRole);

        $response = $this->actingAs($superAdmin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Papan Pemuka');
        $response->assertSee('Akaun');
        $response->assertSee('Hasil');
        $response->assertSee('Belanja');
        $response->assertSee('Import Bank PDF');
        $response->assertSee('Laporan');
        $response->assertSee('Pengurusan Pengguna');
    }

    public function test_sidebar_hides_unauthorized_links_for_regular_user(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->ensurePermissions([
            'dashboard.view',
            'akaun.view',
            'users.view',
            'reports.view',
        ]);

        $role = Role::query()->firstOrCreate([
            'name' => 'AJK',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions(['dashboard.view', 'akaun.view']);

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Sidebar A',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addMonth(),
        ]);

        $user = User::factory()->create([
            'id_masjid' => $masjid->id,
            'peranan' => 'staff',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $user->assignRole($role);

        $this->actingAs($user);

        $html = (string) view('components.sidebar')->render();

        $this->assertStringContainsString('Papan Pemuka', $html);
        $this->assertStringContainsString('Akaun', $html);
        $this->assertStringNotContainsString('Pengurusan Pengguna', $html);
        $this->assertStringNotContainsString('Laporan', $html);
    }

    public function test_superadmin_sees_all_existing_sidebar_links(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdminRole = Role::query()->firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ]);

        $superAdmin = User::factory()->create([
            'id_masjid' => null,
            'peranan' => 'superadmin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole($superAdminRole);

        $this->actingAs($superAdmin);

        $html = (string) view('components.sidebar')->render();

        $this->assertStringContainsString('Papan Pemuka', $html);
        $this->assertStringContainsString('Akaun', $html);
        $this->assertStringContainsString('Hasil', $html);
        $this->assertStringContainsString('Belanja', $html);
        $this->assertStringContainsString('Import Bank PDF', $html);
        $this->assertStringContainsString('Laporan', $html);
        $this->assertStringContainsString('Pengurusan Pengguna', $html);
    }

    public function test_non_superadmin_without_masjid_only_sees_non_tenant_items(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->ensurePermissions([
            'dashboard.view',
            'akaun.view',
        ]);

        $role = Role::query()->firstOrCreate([
            'name' => 'User',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions(['dashboard.view', 'akaun.view']);

        $user = User::factory()->create([
            'id_masjid' => null,
            'peranan' => 'staff',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $user->assignRole($role);

        $this->actingAs($user);

        $html = (string) view('components.sidebar')->render();

        $this->assertStringContainsString('Papan Pemuka', $html);
        $this->assertStringNotContainsString('Akaun', $html);
    }

    public function test_tenant_admin_sees_subscription_link_in_sidebar(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->ensurePermissions([
            'dashboard.view',
        ]);

        $role = Role::query()->firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions(['dashboard.view']);

        $masjid = $this->createActiveMasjid('Masjid Subscription Sidebar');

        $user = User::factory()->create([
            'id_masjid' => $masjid->id,
            'peranan' => 'admin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $user->assignRole($role);

        $this->actingAs($user);

        $html = (string) view('components.sidebar')->render();

        $this->assertStringContainsString('Langganan', $html);
    }

    private function ensurePermissions(array $names): void
    {
        foreach ($names as $name) {
            Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }
    }

    private function createActiveMasjid(string $name): Masjid
    {
        return Masjid::query()->create([
            'nama' => $name,
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addMonth(),
        ]);
    }
}
