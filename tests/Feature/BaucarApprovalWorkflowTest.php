<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BaucarApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_bendahari_can_approve_first_step(): void
    {
        [$masjid, $akaun, $kategori] = $this->seedMasjidCoreData();

        $bendahari = $this->makeUserWithRoleAndPermissions(
            'bendahari.workflow@example.test',
            $masjid->id,
            'Bendahari',
            ['belanja.view'],
            true
        );

        $belanja = $this->makeDraftBelanja($masjid->id, $akaun->id, $kategori->id, $bendahari->id);

        $this->actingAs($bendahari)
            ->post(route('baucar.approve', ['belanja_id' => $belanja->id]))
            ->assertRedirect(route('baucar.show', ['belanja_id' => $belanja->id]));

        $fresh = $belanja->fresh();

        $this->assertSame('DRAF', $fresh->status);
        $this->assertSame(1, (int) $fresh->approval_step);
        $this->assertSame($bendahari->id, $fresh->bendahari_lulus_oleh);
        $this->assertNotNull($fresh->bendahari_lulus_pada);
        $this->assertNotNull($fresh->bendahari_signature);
        $this->assertFalse((bool) $fresh->is_baucar_locked);
    }

    public function test_pengerusi_can_only_finalize_after_bendahari_step(): void
    {
        [$masjid, $akaun, $kategori] = $this->seedMasjidCoreData();

        $bendahari = $this->makeUserWithRoleAndPermissions(
            'bendahari.step@example.test',
            $masjid->id,
            'Bendahari',
            ['belanja.view'],
            true
        );

        $pengerusi = $this->makeUserWithRoleAndPermissions(
            'pengerusi.step@example.test',
            $masjid->id,
            'Pengerusi',
            ['belanja.view'],
            true
        );

        $belanja = $this->makeDraftBelanja($masjid->id, $akaun->id, $kategori->id, $bendahari->id);

        $this->actingAs($pengerusi)
            ->post(route('baucar.approve', ['belanja_id' => $belanja->id]))
            ->assertForbidden();

        $this->actingAs($bendahari)
            ->post(route('baucar.approve', ['belanja_id' => $belanja->id]))
            ->assertRedirect();

        $this->actingAs($pengerusi)
            ->post(route('baucar.approve', ['belanja_id' => $belanja->id]))
            ->assertRedirect(route('baucar.show', ['belanja_id' => $belanja->id]));

        $fresh = $belanja->fresh();

        $this->assertSame('LULUS', $fresh->status);
        $this->assertSame(2, (int) $fresh->approval_step);
        $this->assertSame($pengerusi->id, $fresh->pengerusi_lulus_oleh);
        $this->assertNotNull($fresh->pengerusi_lulus_pada);
        $this->assertNotNull($fresh->pengerusi_signature);
        $this->assertTrue((bool) $fresh->is_baucar_locked);
        $this->assertSame($pengerusi->id, $fresh->locked_by);
        $this->assertNotNull($fresh->locked_at);
    }

    public function test_non_authorized_role_cannot_approve(): void
    {
        [$masjid, $akaun, $kategori] = $this->seedMasjidCoreData();

        $ajk = $this->makeUserWithRoleAndPermissions(
            'ajk.workflow@example.test',
            $masjid->id,
            'AJK',
            ['belanja.view'],
            true
        );

        $belanja = $this->makeDraftBelanja($masjid->id, $akaun->id, $kategori->id, $ajk->id);

        $this->actingAs($ajk)
            ->post(route('baucar.approve', ['belanja_id' => $belanja->id]))
            ->assertForbidden();
    }

    public function test_approval_requires_profile_signature_image(): void
    {
        [$masjid, $akaun, $kategori] = $this->seedMasjidCoreData();

        $bendahari = $this->makeUserWithRoleAndPermissions(
            'bendahari.nosign@example.test',
            $masjid->id,
            'Bendahari',
            ['belanja.view'],
            false
        );

        $belanja = $this->makeDraftBelanja($masjid->id, $akaun->id, $kategori->id, $bendahari->id);

        $this->actingAs($bendahari)
            ->post(route('baucar.approve', ['belanja_id' => $belanja->id]))
            ->assertRedirect(route('baucar.show', ['belanja_id' => $belanja->id]));

        $this->assertSame(0, (int) $belanja->fresh()->approval_step);
        $this->assertNull($belanja->fresh()->bendahari_signature);
    }

    public function test_locked_baucar_cannot_be_updated_or_rejected(): void
    {
        [$masjid, $akaun, $kategori] = $this->seedMasjidCoreData();

        $bendahari = $this->makeUserWithRoleAndPermissions(
            'bendahari.lock@example.test',
            $masjid->id,
            'Bendahari',
            ['belanja.view', 'belanja.update'],
            true
        );

        $pengerusi = $this->makeUserWithRoleAndPermissions(
            'pengerusi.lock@example.test',
            $masjid->id,
            'Pengerusi',
            ['belanja.view'],
            true
        );

        $belanja = $this->makeDraftBelanja($masjid->id, $akaun->id, $kategori->id, $bendahari->id);

        $this->actingAs($bendahari)
            ->post(route('baucar.approve', ['belanja_id' => $belanja->id]))
            ->assertRedirect();

        $this->actingAs($pengerusi)
            ->post(route('baucar.approve', ['belanja_id' => $belanja->id]))
            ->assertRedirect();

        $this->assertTrue((bool) $belanja->fresh()->is_baucar_locked);

        $this->actingAs($bendahari)
            ->put(route('admin.belanja.update', $belanja), [
                'id_masjid' => $masjid->id,
                'tarikh' => '2026-04-30',
                'amaun' => 123.45,
                'id_akaun' => $akaun->id,
                'id_kategori_belanja' => $kategori->id,
                'submit_action' => 'draft',
                'penerima' => 'Updated Recipient',
                'catatan' => 'Should be blocked because locked',
            ])
            ->assertForbidden();

        $this->actingAs($bendahari)
            ->post(route('baucar.reject', ['belanja_id' => $belanja->id]), [
                'catatan_tolak' => 'Should not be accepted because locked',
            ])
            ->assertForbidden();
    }

    private function seedMasjidCoreData(): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Workflow',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addDays(30),
        ]);

        $akaun = Akaun::query()->create([
            'id_masjid' => $masjid->id,
            'nama_akaun' => 'Bank Workflow',
            'jenis' => 'bank',
            'status_aktif' => true,
        ]);

        $kategori = KategoriBelanja::query()->create([
            'id_masjid' => $masjid->id,
            'kod' => 'WF',
            'nama_kategori' => 'Workflow',
            'aktif' => true,
        ]);

        return [$masjid, $akaun, $kategori];
    }

    private function makeUserWithRoleAndPermissions(
        string $email,
        int $masjidId,
        string $roleName,
        array $permissionNames,
        bool $withSignature = false
    ): User {
        $permissions = collect($permissionNames)->map(function (string $name) {
            return Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        });

        $role = Role::query()->firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($permissions);

        $user = User::query()->create([
            'name' => $roleName . ' User',
            'email' => $email,
            'password' => 'password',
            'id_masjid' => $masjidId,
            'aktif' => true,
            'signature_path' => $withSignature ? 'signature-images/dummy-signature.png' : null,
        ]);

        $user->assignRole($role);

        return $user;
    }

    private function makeDraftBelanja(int $masjidId, int $akaunId, int $kategoriId, int $createdBy): Belanja
    {
        return Belanja::query()->create([
            'id_masjid' => $masjidId,
            'tarikh' => '2026-04-30',
            'id_akaun' => $akaunId,
            'id_kategori_belanja' => $kategoriId,
            'amaun' => 100,
            'created_by' => $createdBy,
            'status' => 'DRAF',
            'is_deleted' => false,
        ]);
    }
}
