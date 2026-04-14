<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\Masjid;
use App\Models\PindahanAkaun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PindahanAkaunModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pindahan_akaun_module_transfer_validation_scoping_and_crud_flow(): void
    {
        // --- permissions & role ---
        $permissions = collect([
            'pindahan_akaun.view',
            'pindahan_akaun.create',
            'pindahan_akaun.update',
            'pindahan_akaun.delete',
        ])->map(fn (string $name) => Permission::query()->firstOrCreate([
            'name'       => $name,
            'guard_name' => 'web',
        ]));

        $role = Role::query()->firstOrCreate(['name' => 'Bendahari', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        // --- fixtures ---
        $masjidA = Masjid::query()->create([
            'nama' => 'Masjid Pindahan A',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addMonth(),
        ]);
        $masjidB = Masjid::query()->create([
            'nama' => 'Masjid Pindahan B',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addMonth(),
        ]);

        $bendahari = User::query()->create([
            'name'      => 'Bendahari Pindahan',
            'email'     => 'bendahari.pindahan@example.test',
            'password'  => 'password',
            'id_masjid' => $masjidA->id,
            'aktif'     => true,
        ]);
        $bendahari->assignRole($role);

        $akaunA1 = Akaun::query()->create([
            'id_masjid'   => $masjidA->id,
            'nama_akaun'  => 'Bank A1',
            'jenis'       => 'bank',
            'status_aktif' => true,
        ]);
        $akaunA2 = Akaun::query()->create([
            'id_masjid'   => $masjidA->id,
            'nama_akaun'  => 'Tunai A2',
            'jenis'       => 'tunai',
            'status_aktif' => true,
        ]);
        $akaunInactive = Akaun::query()->create([
            'id_masjid'   => $masjidA->id,
            'nama_akaun'  => 'Akaun Tidak Aktif',
            'jenis'       => 'bank',
            'status_aktif' => false,
        ]);
        $akaunB = Akaun::query()->create([
            'id_masjid'   => $masjidB->id,
            'nama_akaun'  => 'Bank B',
            'jenis'       => 'bank',
            'status_aktif' => true,
        ]);

        // Pre-existing records
        $recordA = PindahanAkaun::query()->create([
            'id_masjid'     => $masjidA->id,
            'tarikh'        => '2026-04-10',
            'dari_akaun_id' => $akaunA1->id,
            'ke_akaun_id'   => $akaunA2->id,
            'amaun'         => 500.00,
            'catatan'       => 'Pindahan test A',
            'created_by'    => $bendahari->id,
        ]);
        $recordB = PindahanAkaun::query()->create([
            'id_masjid'     => $masjidB->id,
            'tarikh'        => '2026-04-11',
            'dari_akaun_id' => $akaunB->id,
            'ke_akaun_id'   => $akaunB->id, // same used for isolation only; normally invalid
            'amaun'         => 999.00,
            'catatan'       => 'Rekod tersembunyi B',
            'created_by'    => $bendahari->id,
        ]);

        // --- index: masjid scoping ---
        $this->actingAs($bendahari)
            ->get(route('admin.pindahan-akaun.index'))
            ->assertOk()
            ->assertSee('RM 500.00')
            ->assertDontSee('RM 999.00');

        // --- index: akaun filter ---
        $this->actingAs($bendahari)
            ->get(route('admin.pindahan-akaun.index', ['akaun_id' => $akaunA1->id]))
            ->assertOk()
            ->assertSee('RM 500.00');

        // --- index: date range filter ---
        $this->actingAs($bendahari)
            ->get(route('admin.pindahan-akaun.index', ['date_from' => '2026-04-10', 'date_to' => '2026-04-10']))
            ->assertOk()
            ->assertSee('RM 500.00');

        // --- create page loads ---
        $this->actingAs($bendahari)
            ->get(route('admin.pindahan-akaun.create'))
            ->assertOk()
            ->assertSee('Dari Akaun')
            ->assertSee('Ke Akaun');

        // --- store: valid transfer ---
        $this->actingAs($bendahari)
            ->post(route('admin.pindahan-akaun.store'), [
                'id_masjid'     => $masjidB->id, // should be overridden to masjidA
                'tarikh'        => '2026-04-14',
                'dari_akaun_id' => $akaunA1->id,
                'ke_akaun_id'   => $akaunA2->id,
                'amaun'         => 250.75,
                'catatan'       => 'Pindahan baharu ujian',
            ])
            ->assertRedirect();

        $created = PindahanAkaun::query()->where('catatan', 'Pindahan baharu ujian')->first();
        $this->assertNotNull($created);
        $this->assertSame($masjidA->id, $created->id_masjid); // id_masjid forced from actor
        $this->assertEquals(250.75, (float) $created->amaun);

        // --- store: same account validation ---
        $this->actingAs($bendahari)
            ->post(route('admin.pindahan-akaun.store'), [
                'tarikh'        => '2026-04-14',
                'dari_akaun_id' => $akaunA1->id,
                'ke_akaun_id'   => $akaunA1->id, // same!
                'amaun'         => 100,
            ])
            ->assertSessionHasErrors(['ke_akaun_id']);

        // --- store: cross-masjid account rejected by service ---
        $this->actingAs($bendahari)
            ->post(route('admin.pindahan-akaun.store'), [
                'tarikh'        => '2026-04-14',
                'dari_akaun_id' => $akaunA1->id,
                'ke_akaun_id'   => $akaunB->id, // different masjid
                'amaun'         => 100,
            ])
            ->assertSessionHasErrors(['ke_akaun_id']);

        // --- store: inactive account rejected by service ---
        $this->actingAs($bendahari)
            ->post(route('admin.pindahan-akaun.store'), [
                'tarikh'        => '2026-04-14',
                'dari_akaun_id' => $akaunInactive->id,
                'ke_akaun_id'   => $akaunA2->id,
                'amaun'         => 100,
            ])
            ->assertSessionHasErrors(['dari_akaun_id']);

        // --- edit loads ---
        $this->actingAs($bendahari)
            ->get(route('admin.pindahan-akaun.edit', $recordA))
            ->assertOk()
            ->assertSee('500');

        // --- update: valid ---
        $this->actingAs($bendahari)
            ->put(route('admin.pindahan-akaun.update', $recordA), [
                'tarikh'        => '2026-04-10',
                'dari_akaun_id' => $akaunA2->id,
                'ke_akaun_id'   => $akaunA1->id,
                'amaun'         => 600,
                'catatan'       => 'Rekod dikemaskini',
            ])
            ->assertRedirect();

        $this->assertSame(600.0, (float) $recordA->refresh()->amaun);
        $this->assertSame('Rekod dikemaskini', $recordA->catatan);

        // --- cross-masjid scoping: masjid B record not editable ---
        $this->actingAs($bendahari)
            ->get(route('admin.pindahan-akaun.edit', $recordB))
            ->assertNotFound();

        // --- destroy ---
        $toDelete = PindahanAkaun::query()->create([
            'id_masjid'     => $masjidA->id,
            'tarikh'        => '2026-04-13',
            'dari_akaun_id' => $akaunA1->id,
            'ke_akaun_id'   => $akaunA2->id,
            'amaun'         => 50,
            'created_by'    => $bendahari->id,
        ]);

        $this->actingAs($bendahari)
            ->delete(route('admin.pindahan-akaun.destroy', $toDelete))
            ->assertRedirect(route('admin.pindahan-akaun.index'));

        $this->assertNull(PindahanAkaun::query()->find($toDelete->id));
    }
}
