<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\ProgramMasjid;
use App\Models\SumberHasil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProgramMasjidModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_masjid_module_scoped_crud_and_transaction_guard_flow(): void
    {
        $permissions = collect([
            'program_masjid.view',
            'program_masjid.create',
            'program_masjid.update',
            'program_masjid.delete',
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

        $masjidA = Masjid::query()->create(['nama' => 'Masjid Program A']);
        $masjidB = Masjid::query()->create(['nama' => 'Masjid Program B']);

        $bendahari = User::query()->create([
            'name' => 'Bendahari Program',
            'email' => 'bendahari.program@example.test',
            'password' => 'password',
            'id_masjid' => $masjidA->id,
            'aktif' => true,
        ]);
        $bendahari->assignRole($role);

        $linkedProgram = ProgramMasjid::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_program' => 'Kuliah Dhuha Ujian',
            'aktif' => true,
        ]);

        $unusedProgram = ProgramMasjid::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_program' => 'Ziarah Komuniti Ujian',
            'aktif' => false,
        ]);

        ProgramMasjid::query()->create([
            'id_masjid' => $masjidB->id,
            'nama_program' => 'Program Tersembunyi B',
            'aktif' => true,
        ]);

        $akaun = Akaun::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_akaun' => 'Bank Aktiviti',
            'jenis' => 'bank',
            'no_akaun' => '2233445566',
            'nama_bank' => 'Bank Aktiviti Ujian',
            'status_aktif' => true,
        ]);

        $sumberHasil = SumberHasil::query()->create([
            'id_masjid' => $masjidA->id,
            'kod' => 'PRG',
            'nama_sumber' => 'Sumbangan Program',
            'jenis' => 'Sumbangan',
            'aktif' => true,
        ]);

        $kategoriBelanja = KategoriBelanja::query()->create([
            'id_masjid' => $masjidA->id,
            'kod' => 'AKT',
            'nama_kategori' => 'Aktiviti Program',
            'aktif' => true,
        ]);

        Hasil::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => now()->toDateString(),
            'no_resit' => 'RCP-PRG-001',
            'id_akaun' => $akaun->id,
            'id_sumber_hasil' => $sumberHasil->id,
            'amaun_tunai' => 800,
            'amaun_online' => 0,
            'jumlah' => 800,
            'id_program' => $linkedProgram->id,
            'catatan' => 'Hasil program ujian',
            'created_by' => $bendahari->id,
        ]);

        Belanja::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => now()->subDay()->toDateString(),
            'id_akaun' => $akaun->id,
            'id_kategori_belanja' => $kategoriBelanja->id,
            'amaun' => 320,
            'id_program' => $linkedProgram->id,
            'penerima' => 'Pembekal Program',
            'catatan' => 'Belanja program ujian',
            'created_by' => $bendahari->id,
            'status' => 'DRAF',
        ]);

        $this->actingAs($bendahari)
            ->get(route('admin.program-masjid.index'))
            ->assertOk()
            ->assertSee('Kuliah Dhuha Ujian')
            ->assertSee('Ziarah Komuniti Ujian')
            ->assertSee('Dipaut dalam 2 transaksi')
            ->assertDontSee('Program Tersembunyi B');

        $this->actingAs($bendahari)
            ->get(route('admin.program-masjid.index', ['status' => 'linked']))
            ->assertOk()
            ->assertSee('Kuliah Dhuha Ujian')
            ->assertDontSee('Ziarah Komuniti Ujian');

        $this->actingAs($bendahari)
            ->post(route('admin.program-masjid.store'), [
                'id_masjid' => $masjidB->id,
                'nama_program' => 'Program Ramadan Perdana',
                'aktif' => true,
            ])
            ->assertRedirect();

        $created = ProgramMasjid::query()->where('nama_program', 'Program Ramadan Perdana')->first();
        $this->assertNotNull($created);
        $this->assertSame($masjidA->id, $created->id_masjid);

        $this->actingAs($bendahari)
            ->put(route('admin.program-masjid.update', $unusedProgram), [
                'id_masjid' => $masjidA->id,
                'nama_program' => 'Ziarah Komuniti Asnaf',
                'aktif' => true,
            ])
            ->assertRedirect();

        $this->assertSame('Ziarah Komuniti Asnaf', $unusedProgram->fresh()->nama_program);
        $this->assertTrue($unusedProgram->fresh()->aktif);

        $this->actingAs($bendahari)
            ->delete(route('admin.program-masjid.destroy', $linkedProgram))
            ->assertSessionHasErrors('program_masjid');

        $this->assertNotNull($linkedProgram->fresh());

        $this->actingAs($bendahari)
            ->patch(route('admin.program-masjid.status', $unusedProgram))
            ->assertRedirect();

        $this->assertFalse($unusedProgram->fresh()->aktif);

        $this->actingAs($bendahari)
            ->delete(route('admin.program-masjid.destroy', $created))
            ->assertRedirect();

        $this->assertNull($created->fresh());
    }
}
