<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionModuleSmokeTest extends TestCase
{
    public function test_role_creation_and_route_restriction_smoke(): void
    {
        $rolesAssignPermission = Permission::query()->firstOrCreate([
            'name' => 'roles.assign',
            'guard_name' => 'web',
        ]);

        $dashboardPermission = Permission::query()->firstOrCreate([
            'name' => 'dashboard.view',
            'guard_name' => 'web',
        ]);

        $adminRole = Role::query()->firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);
        $adminRole->syncPermissions([$rolesAssignPermission, $dashboardPermission]);

        $userRole = Role::query()->firstOrCreate([
            'name' => 'User',
            'guard_name' => 'web',
        ]);
        $userRole->syncPermissions([$dashboardPermission]);

        $admin = User::query()->whereHas('roles', function ($query) {
            $query->where('name', 'Admin');
        })->first();

        if (!$admin) {
            $admin = User::factory()->create();
            $admin->assignRole($adminRole);
        }

        $nonPrivileged = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'User');
            })
            ->first();

        if (!$nonPrivileged) {
            $nonPrivileged = User::factory()->create();
            $nonPrivileged->assignRole($userRole);
        }

        $this->assertNotNull($admin, 'Admin user is required for smoke test.');
        $this->assertNotNull($nonPrivileged, 'A user with role "User" is required for non-privileged smoke check.');
        $this->assertFalse($nonPrivileged->can('roles.assign'), 'Selected non-privileged user unexpectedly has roles.assign access.');

        $permissionNames = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->limit(3)
            ->pluck('name')
            ->all();

        $this->assertNotEmpty($permissionNames, 'At least one web permission is required.');

        $roleName = 'SmokeRoleRegression';

        // Keep this smoke idempotent across repeated local/CI runs.
        Role::query()
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->delete();

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => $roleName,
                'permissions' => $permissionNames,
            ])
            ->assertRedirect();

        $createdRole = Role::query()
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->first();

        $this->assertNotNull($createdRole, 'Role should be created through HTTP endpoint.');
        $this->assertSame(
            count($permissionNames),
            $createdRole->permissions()->count(),
            'Created role should have selected permissions assigned.'
        );

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertOk();

        $this->actingAs($nonPrivileged)
            ->get(route('admin.roles.index'))
            ->assertForbidden();

        $createdRole->delete();
    }
}
