<?php

namespace Tests\Feature;

use App\Models\Masjid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_management_stays_within_their_tenant(): void
    {
        $adminRole = Role::query()->firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

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

        $adminA = User::factory()->create([
            'id_masjid' => $masjidA->id,
            'peranan' => 'admin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $adminA->assignRole($adminRole);

        $sameTenantUser = User::factory()->create([
            'id_masjid' => $masjidA->id,
            'name' => 'Visible User',
            'email' => 'visible.user@gmail.com',
            'aktif' => true,
        ]);

        $otherTenantUser = User::factory()->create([
            'id_masjid' => $masjidB->id,
            'name' => 'Hidden User',
            'email' => 'hidden@example.com',
            'aktif' => true,
        ]);

        $this->actingAs($adminA)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Visible User')
            ->assertDontSee('Hidden User');

        $this->actingAs($adminA)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Masjid A')
            ->assertDontSee('Masjid B');

        $this->actingAs($adminA)
            ->post(route('admin.users.store'), [
                'id_masjid' => $masjidB->id,
                'name' => 'Forced Tenant User',
                'email' => 'forced.tenant@gmail.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'Admin',
                'aktif' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $createdUser = User::query()->where('email', 'forced.tenant@gmail.com')->first();

        $this->assertNotNull($createdUser);
        $this->assertSame($masjidA->id, $createdUser->id_masjid);

        $this->actingAs($adminA)
            ->get(route('admin.users.edit', $otherTenantUser))
            ->assertForbidden();

        $this->actingAs($adminA)
            ->put(route('admin.users.update', $sameTenantUser), [
                'id_masjid' => $masjidB->id,
                'name' => 'Visible User Updated',
                'email' => 'visible.user@gmail.com',
                'role' => 'Admin',
                'aktif' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $sameTenantUser->refresh();

        $this->assertSame('Visible User Updated', $sameTenantUser->name);
        $this->assertSame($masjidA->id, $sameTenantUser->id_masjid);
    }
}