<?php

namespace Tests\Feature;

use App\Models\Masjid;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleAssignmentTamperingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_admin_cannot_create_user_with_global_admin_role(): void
    {
        [$adminA, $masjidA] = $this->makeTenantAdmin();

        $this->actingAs($adminA)
            ->post(route('admin.users.store'), [
                'id_masjid' => $masjidA->id,
                'name' => 'Escalation Attempt',
                'email' => 'escalation.admin.role@gmail.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'Admin',
                'aktif' => '1',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('users', [
            'email' => 'escalation.admin.role@gmail.com',
        ]);
    }

    public function test_tenant_admin_cannot_create_user_with_other_tenant_role(): void
    {
        [$adminA, $masjidA, $masjidB] = $this->makeTenantAdmin(withSecondMasjid: true);

        Role::query()->create([
            'name' => 'TenantRoleB_CreateDenied',
            'guard_name' => 'web',
            'level' => 3,
            'masjid_id' => $masjidB->id,
        ]);

        $this->actingAs($adminA)
            ->post(route('admin.users.store'), [
                'id_masjid' => $masjidA->id,
                'name' => 'Cross Tenant Create',
                'email' => 'cross.tenant.create@gmail.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'TenantRoleB_CreateDenied',
                'aktif' => '1',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('users', [
            'email' => 'cross.tenant.create@gmail.com',
        ]);
    }

    public function test_tenant_admin_cannot_update_user_with_other_tenant_role_or_force_tenant_change(): void
    {
        [$adminA, $masjidA, $masjidB] = $this->makeTenantAdmin(withSecondMasjid: true);

        $roleA = Role::query()->create([
            'name' => 'TenantRoleA_Updatable',
            'guard_name' => 'web',
            'level' => 3,
            'masjid_id' => $masjidA->id,
        ]);

        Role::query()->create([
            'name' => 'TenantRoleB_UpdateDenied',
            'guard_name' => 'web',
            'level' => 3,
            'masjid_id' => $masjidB->id,
        ]);

        $target = User::factory()->create([
            'id_masjid' => $masjidA->id,
            'name' => 'Safe Target',
            'email' => 'safe.target@gmail.com',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $target->assignRole($roleA);

        $this->actingAs($adminA)
            ->put(route('admin.users.update', $target), [
                'id_masjid' => $masjidB->id,
                'name' => 'Compromised Target',
                'email' => 'safe.target@gmail.com',
                'role' => 'TenantRoleB_UpdateDenied',
                'aktif' => '1',
            ])
            ->assertNotFound();

        $target->refresh();

        $this->assertSame('Safe Target', $target->name);
        $this->assertSame((int) $masjidA->id, (int) $target->id_masjid);
        $this->assertTrue($target->hasRole('TenantRoleA_Updatable'));
        $this->assertFalse($target->hasRole('TenantRoleB_UpdateDenied'));
    }

    public function test_tenant_admin_can_create_user_with_own_tenant_level3_role(): void
    {
        [$adminA, $masjidA] = $this->makeTenantAdmin();

        Role::query()->create([
            'name' => 'TenantRoleA_CreateAllowed',
            'guard_name' => 'web',
            'level' => 3,
            'masjid_id' => $masjidA->id,
        ]);

        $this->actingAs($adminA)
            ->post(route('admin.users.store'), [
                'id_masjid' => $masjidA->id,
                'name' => 'Legit Tenant User',
                'email' => 'legit.tenant.user@gmail.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'TenantRoleA_CreateAllowed',
                'aktif' => '1',
            ])
            ->assertRedirect();

        $user = User::query()->where('email', 'legit.tenant.user@gmail.com')->first();

        $this->assertNotNull($user);
        $this->assertSame((int) $masjidA->id, (int) $user->id_masjid);
        $this->assertTrue($user->hasRole('TenantRoleA_CreateAllowed'));
    }

    /**
     * @return array{0: User, 1: Masjid, 2?: Masjid}
     */
    private function makeTenantAdmin(bool $withSecondMasjid = false): array
    {
        $adminRole = Role::query()->firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ], [
            'level' => 2,
            'masjid_id' => null,
        ]);

        $masjidA = Masjid::query()->create([
            'nama' => 'Masjid A',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addMonth(),
        ]);

        $masjidB = null;
        if ($withSecondMasjid) {
            $masjidB = Masjid::query()->create([
                'nama' => 'Masjid B',
                'status' => 'active',
                'subscription_status' => 'active',
                'subscription_expiry' => now()->addMonth(),
            ]);
        }

        $adminA = User::factory()->create([
            'id_masjid' => $masjidA->id,
            'peranan' => 'admin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $adminA->assignRole($adminRole);

        return $withSecondMasjid
            ? [$adminA, $masjidA, $masjidB]
            : [$adminA, $masjidA];
    }
}
