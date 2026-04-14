<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\Masjid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AkaunModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_akaun_module_scoped_filters_and_crud_flow(): void
    {
        $permissions = collect([
            'akaun.view',
            'akaun.create',
            'akaun.update',
            'akaun.delete',
        ])->map(function (string $name) {
            return Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        });

        $role = Role::query()->firstOrCreate([
            'name' => 'Bendahari',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($permissions);

        $masjidA = Masjid::query()->create(['nama' => 'Masjid A']);
        $masjidB = Masjid::query()->create(['nama' => 'Masjid B']);

        $bendahari = User::factory()->create([
            'id_masjid' => $masjidA->id,
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $bendahari->assignRole($role);

        $tunaiA = Akaun::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_akaun' => 'Tabung Tunai A',
            'jenis' => 'tunai',
            'status_aktif' => true,
        ]);

        $bankAInactive = Akaun::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_akaun' => 'Bank A Inactive',
            'jenis' => 'bank',
            'no_akaun' => '111222333',
            'nama_bank' => 'Bank Tempatan',
            'status_aktif' => false,
        ]);

        Akaun::query()->create([
            'id_masjid' => $masjidB->id,
            'nama_akaun' => 'Bank B Hidden',
            'jenis' => 'bank',
            'no_akaun' => '999888777',
            'nama_bank' => 'Bank Luar',
            'status_aktif' => true,
        ]);

        $this->actingAs($bendahari)
            ->get(route('admin.akaun.index'))
            ->assertOk()
            ->assertSee('Tabung Tunai A')
            ->assertSee('Bank A Inactive')
            ->assertDontSee('Bank B Hidden');

        $this->actingAs($bendahari)
            ->get(route('admin.akaun.index', ['jenis' => 'bank']))
            ->assertOk()
            ->assertSee('Bank A Inactive')
            ->assertDontSee('Tabung Tunai A');

        $this->actingAs($bendahari)
            ->get(route('admin.akaun.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('Tabung Tunai A')
            ->assertDontSee('Bank A Inactive');

        $this->actingAs($bendahari)
            ->post(route('admin.akaun.store'), [
                'id_masjid' => $masjidB->id,
                'nama_akaun' => 'Forced Scope Account',
                'jenis' => 'bank',
                'no_akaun' => '123123123',
                'nama_bank' => 'Scope Bank',
                'status_aktif' => true,
            ])
            ->assertRedirect();

        $created = Akaun::query()->where('nama_akaun', 'Forced Scope Account')->first();
        $this->assertNotNull($created);
        $this->assertSame($masjidA->id, $created->id_masjid);

        $this->actingAs($bendahari)
            ->put(route('admin.akaun.update', $tunaiA), [
                'id_masjid' => $masjidA->id,
                'nama_akaun' => 'Tunai A Updated',
                'jenis' => 'tunai',
                'status_aktif' => true,
            ])
            ->assertRedirect();

        $this->assertSame('Tunai A Updated', $tunaiA->fresh()->nama_akaun);

        $this->actingAs($bendahari)
            ->delete(route('admin.akaun.destroy', $bankAInactive))
            ->assertRedirect();

        $this->assertNull($bankAInactive->fresh());
    }
}
