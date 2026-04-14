<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\Hasil;
use App\Models\Masjid;
use App\Models\SumberHasil;
use App\Models\TabungKhas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HasilModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_hasil_module_scoped_crud_and_filter_flow(): void
    {
        $permissions = collect([
            'hasil.view',
            'hasil.create',
            'hasil.update',
            'hasil.delete',
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

        $masjidA = Masjid::query()->create(['nama' => 'Masjid Hasil A']);
        $masjidB = Masjid::query()->create(['nama' => 'Masjid Hasil B']);

        $bendahari = User::query()->create([
            'name' => 'Bendahari Hasil',
            'email' => 'bendahari.hasil@example.test',
            'password' => 'password',
            'id_masjid' => $masjidA->id,
            'aktif' => true,
        ]);
        $bendahari->assignRole($role);

        $akaunA = Akaun::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_akaun' => 'Tunai Utama A',
            'jenis' => 'tunai',
            'status_aktif' => true,
        ]);
        $akaunB = Akaun::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_akaun' => 'Bank Operasi A',
            'jenis' => 'bank',
            'status_aktif' => true,
        ]);
        $akaunHidden = Akaun::query()->create([
            'id_masjid' => $masjidB->id,
            'nama_akaun' => 'Tunai Hidden B',
            'jenis' => 'tunai',
            'status_aktif' => true,
        ]);

        $sumberA = SumberHasil::query()->create([
            'id_masjid' => $masjidA->id,
            'kod' => 'JMT',
            'nama_sumber' => 'Jumaat A',
            'jenis' => 'Derma',
            'aktif' => true,
        ]);
        $sumberB = SumberHasil::query()->create([
            'id_masjid' => $masjidA->id,
            'kod' => 'SUMB',
            'nama_sumber' => 'Sumbangan A',
            'jenis' => 'Sumbangan',
            'aktif' => true,
        ]);
        $sumberHidden = SumberHasil::query()->create([
            'id_masjid' => $masjidB->id,
            'kod' => 'HID',
            'nama_sumber' => 'Sumber Hidden B',
            'jenis' => 'Derma',
            'aktif' => true,
        ]);

        $tabungA = TabungKhas::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_tabung' => 'Tabung Pembangunan A',
            'aktif' => true,
        ]);

        $jumaatRecord = Hasil::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => '2026-04-10',
            'no_resit' => 'HSL-A-001',
            'id_akaun' => $akaunA->id,
            'id_sumber_hasil' => $sumberA->id,
            'amaun_tunai' => 500,
            'amaun_online' => 0,
            'jumlah' => 500,
            'id_tabung_khas' => $tabungA->id,
            'jenis_jumaat' => 'biasa',
            'created_by' => $bendahari->id,
        ]);

        $regularRecord = Hasil::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => '2026-04-12',
            'no_resit' => 'HSL-A-002',
            'id_akaun' => $akaunB->id,
            'id_sumber_hasil' => $sumberB->id,
            'amaun_tunai' => 300,
            'amaun_online' => 0,
            'jumlah' => 300,
            'id_tabung_khas' => null,
            'jenis_jumaat' => null,
            'created_by' => $bendahari->id,
        ]);

        Hasil::query()->create([
            'id_masjid' => $masjidB->id,
            'tarikh' => '2026-04-11',
            'no_resit' => 'HSL-B-001',
            'id_akaun' => $akaunHidden->id,
            'id_sumber_hasil' => $sumberHidden->id,
            'amaun_tunai' => 999,
            'amaun_online' => 0,
            'jumlah' => 999,
            'id_tabung_khas' => null,
            'jenis_jumaat' => null,
            'created_by' => $bendahari->id,
        ]);

        $this->actingAs($bendahari)
            ->get(route('admin.hasil.index'))
            ->assertOk()
            ->assertSee('RM 500.00')
            ->assertSee('RM 300.00')
            ->assertDontSee('RM 999.00');

        $this->actingAs($bendahari)
            ->get(route('admin.hasil.index', ['from' => '2026-04-09', 'to' => '2026-04-10']))
            ->assertOk()
            ->assertSee('RM 500.00')
            ->assertDontSee('RM 300.00');

        $this->actingAs($bendahari)
            ->get(route('admin.hasil.index', ['akaun_id' => $akaunB->id]))
            ->assertOk()
            ->assertSee('RM 300.00')
            ->assertDontSee('RM 500.00');

        $this->actingAs($bendahari)
            ->get(route('admin.hasil.index', ['jumaat' => 'yes']))
            ->assertOk()
            ->assertSee('RM 500.00')
            ->assertDontSee('RM 300.00');

        $this->actingAs($bendahari)
            ->post(route('admin.hasil.store'), [
                'id_masjid' => $masjidB->id,
                'tarikh' => '2026-04-14',
                'amaun' => 425.50,
                'id_akaun' => $akaunA->id,
                'id_sumber_hasil' => $sumberA->id,
                'id_tabung_khas' => $tabungA->id,
                'is_jumaat' => false,
                'catatan' => 'Hasil baharu ujian',
            ])
            ->assertRedirect();

        $created = Hasil::query()->where('catatan', 'Hasil baharu ujian')->first();
        $this->assertNotNull($created);
        $this->assertSame($masjidA->id, $created->id_masjid);
        $this->assertSame('425.50', (string) $created->jumlah);

        $this->actingAs($bendahari)
            ->put(route('admin.hasil.update', $regularRecord), [
                'id_masjid' => $masjidA->id,
                'tarikh' => '2026-04-12',
                'amaun' => 650,
                'id_akaun' => $akaunB->id,
                'id_sumber_hasil' => $sumberB->id,
                'id_tabung_khas' => null,
                'is_jumaat' => false,
                'catatan' => 'Kemaskini hasil biasa',
            ])
            ->assertRedirect();

        $this->assertSame('650.00', (string) $regularRecord->fresh()->jumlah);
        $this->assertSame('Kemaskini hasil biasa', $regularRecord->fresh()->catatan);

        $this->actingAs($bendahari)
            ->delete(route('admin.hasil.destroy', $jumaatRecord))
            ->assertRedirect();

        $this->assertNull($jumaatRecord->fresh());
    }
}
