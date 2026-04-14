<?php

namespace Tests\Feature;

use App\Models\Masjid;
use App\Models\SumberHasil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SumberHasilModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_sumber_hasil_module_scoped_crud_and_toggle_flow(): void
    {
        $permissions = collect([
            'sumber_hasil.view',
            'sumber_hasil.create',
            'sumber_hasil.update',
            'sumber_hasil.delete',
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

        $ownActive = SumberHasil::query()->create([
            'id_masjid' => $masjidA->id,
            'kod' => 'DJM',
            'nama_sumber' => 'Source Active A',
            'jenis' => 'Derma Jumaat',
            'aktif' => true,
        ]);

        $ownInactive = SumberHasil::query()->create([
            'id_masjid' => $masjidA->id,
            'kod' => 'SON',
            'nama_sumber' => 'Source Inactive A',
            'jenis' => 'Sumbangan Online',
            'aktif' => false,
        ]);

        SumberHasil::query()->create([
            'id_masjid' => $masjidB->id,
            'kod' => 'SEW',
            'nama_sumber' => 'Source Hidden B',
            'jenis' => 'Sewaan',
            'aktif' => true,
        ]);

        $this->actingAs($bendahari)
            ->get(route('admin.sumber-hasil.index'))
            ->assertOk()
            ->assertSee('Source Active A')
            ->assertSee('Source Inactive A')
            ->assertDontSee('Source Hidden B');

        $this->actingAs($bendahari)
            ->get(route('admin.sumber-hasil.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('Source Active A')
            ->assertDontSee('Source Inactive A');

        $this->actingAs($bendahari)
            ->post(route('admin.sumber-hasil.store'), [
                'id_masjid' => $masjidB->id,
                'kod' => 'SEW2',
                'nama_sumber' => 'Sewaan Dewan',
                'jenis' => 'Sewaan',
                'aktif' => true,
            ])
            ->assertRedirect();

        $created = SumberHasil::query()->where('kod', 'SEW2')->first();
        $this->assertNotNull($created);
        $this->assertSame($masjidA->id, $created->id_masjid);

        $this->actingAs($bendahari)
            ->patch(route('admin.sumber-hasil.status', $ownActive))
            ->assertRedirect();

        $this->assertFalse($ownActive->fresh()->aktif);

        $this->actingAs($bendahari)
            ->put(route('admin.sumber-hasil.update', $ownInactive), [
                'id_masjid' => $masjidA->id,
                'kod' => 'SON',
                'nama_sumber' => 'Sumbangan Online Updated',
                'jenis' => 'Sumbangan Online',
                'aktif' => true,
            ])
            ->assertRedirect();

        $this->assertSame('Sumbangan Online Updated', $ownInactive->fresh()->nama_sumber);

        $this->actingAs($bendahari)
            ->delete(route('admin.sumber-hasil.destroy', $ownInactive))
            ->assertRedirect();

        $this->assertNull($ownInactive->fresh());
    }
}
