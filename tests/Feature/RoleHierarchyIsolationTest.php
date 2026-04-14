<?php

namespace Tests\Feature;

use App\Models\Masjid;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RoleHierarchyIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_masjid_admin_cannot_edit_global_or_admin_level_roles(): void
    {
        $this->bootstrapRoleAssignPermission();

        [$masjidA] = $this->createMasjids();

        $adminRole = Role::query()->firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ], [
            'level' => 2,
            'masjid_id' => null,
        ]);
        $adminRole->syncPermissions(['roles.assign']);

        $globalAuditor = Role::query()->create([
            'name' => 'GlobalAuditor',
            'guard_name' => 'web',
            'level' => 3,
            'masjid_id' => null,
        ]);

        $tenantAdmin = User::factory()->create([
            'id_masjid' => $masjidA->id,
            'peranan' => 'admin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $tenantAdmin->assignRole($adminRole);

        $this->actingAs($tenantAdmin)
            ->get(route('admin.roles.edit', $adminRole))
            ->assertForbidden();

        $this->actingAs($tenantAdmin)
            ->get(route('admin.roles.edit', $globalAuditor))
            ->assertForbidden();
    }

    public function test_cross_tenant_role_assignment_is_denied_for_masjid_admin(): void
    {
        $this->bootstrapRoleAssignPermission();

        [$masjidA, $masjidB] = $this->createMasjids();

        $adminRole = Role::query()->firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ], [
            'level' => 2,
            'masjid_id' => null,
        ]);
        $adminRole->syncPermissions(['roles.assign']);

        $tenantRoleB = Role::query()->create([
            'name' => 'TenantRoleB',
            'guard_name' => 'web',
            'level' => 3,
            'masjid_id' => $masjidB->id,
        ]);

        $tenantAdminA = User::factory()->create([
            'id_masjid' => $masjidA->id,
            'peranan' => 'admin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $tenantAdminA->assignRole($adminRole);

        $targetUserA = User::factory()->create([
            'id_masjid' => $masjidA->id,
            'peranan' => 'staff',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);

        $canAssign = Gate::forUser($tenantAdminA)->allows('assign-role', [$tenantRoleB, $targetUserA]);

        $this->assertFalse($canAssign, 'Masjid Admin must not assign roles from another masjid.');
    }

    public function test_superadmin_can_manage_role_across_tenants(): void
    {
        $this->bootstrapRoleAssignPermission();

        [, $masjidB] = $this->createMasjids();

        $superAdminRole = Role::query()->firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ], [
            'level' => 1,
            'masjid_id' => null,
        ]);
        $superAdminRole->syncPermissions(['roles.assign']);

        $tenantRole = Role::query()->create([
            'name' => 'TenantTreasurer',
            'guard_name' => 'web',
            'level' => 3,
            'masjid_id' => $masjidB->id,
        ]);

        $superAdmin = User::factory()->create([
            'id_masjid' => null,
            'peranan' => 'superadmin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole($superAdminRole);

        $this->actingAs($superAdmin)
            ->get(route('admin.roles.edit', $tenantRole))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->put(route('admin.roles.update', $tenantRole), [
                'name' => 'TenantTreasurerUpdated',
                'permissions' => ['roles.assign'],
            ])
            ->assertRedirect();

        $tenantRole->refresh();
        $this->assertSame('TenantTreasurerUpdated', $tenantRole->name);
    }

    private function bootstrapRoleAssignPermission(): void
    {
        Permission::query()->firstOrCreate([
            'name' => 'roles.assign',
            'guard_name' => 'web',
        ]);
    }

    /**
     * @return array{0: Masjid, 1: Masjid}
     */
    private function createMasjids(): array
    {
        $masjidA = Masjid::query()->create([
            'nama' => 'Masjid A',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addMonth(),
        ]);

        $masjidB = Masjid::query()->create([
            'nama' => 'Masjid B',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addMonth(),
        ]);

        return [$masjidA, $masjidB];
    }
}
