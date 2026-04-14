<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\SumberHasil;
use App\Models\TabungKhas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TabungKhasModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_tabung_khas_module_scoped_crud_and_transaction_guard_flow(): void
    {
        $permissions = collect([
            'tabung_khas.view',
            'tabung_khas.create',
            'tabung_khas.update',
            'tabung_khas.delete',
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

        $masjidA = Masjid::query()->create(['nama' => 'Masjid Dana A']);
        $masjidB = Masjid::query()->create(['nama' => 'Masjid Dana B']);

        $bendahari = User::query()->create([
            'name' => 'Bendahari Tabung',
            'email' => 'bendahari.tabung@example.test',
            'password' => 'password',
            'id_masjid' => $masjidA->id,
            'aktif' => true,
        ]);
        $bendahari->assignRole($role);

        $linkedFund = TabungKhas::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_tabung' => 'Tabung Pembangunan',
            'aktif' => true,
        ]);

        $unusedFund = TabungKhas::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_tabung' => 'Tabung Wakaf Ujian',
            'aktif' => false,
        ]);

        TabungKhas::query()->create([
            'id_masjid' => $masjidB->id,
            'nama_tabung' => 'Tabung Tersembunyi',
            'aktif' => true,
        ]);

        $akaun = Akaun::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_akaun' => 'Bank Operasi',
            'jenis' => 'bank',
            'no_akaun' => '1234567890',
            'nama_bank' => 'Bank Ujian',
            'status_aktif' => true,
        ]);

        $sumberHasil = SumberHasil::query()->create([
            'id_masjid' => $masjidA->id,
            'kod' => 'DERMA',
            'nama_sumber' => 'Derma Khas',
            'jenis' => 'Derma',
            'aktif' => true,
        ]);

        $kategoriBelanja = KategoriBelanja::query()->create([
            'id_masjid' => $masjidA->id,
            'kod' => 'PROG',
            'nama_kategori' => 'Program Khas',
            'aktif' => true,
        ]);

        Hasil::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => now()->toDateString(),
            'no_resit' => 'RSIT-001',
            'id_akaun' => $akaun->id,
            'id_sumber_hasil' => $sumberHasil->id,
            'amaun_tunai' => 500,
            'amaun_online' => 0,
            'jumlah' => 500,
            'id_tabung_khas' => $linkedFund->id,
            'catatan' => 'Hasil ujian tabung',
            'created_by' => $bendahari->id,
        ]);

        Belanja::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => now()->subDay()->toDateString(),
            'id_akaun' => $akaun->id,
            'id_kategori_belanja' => $kategoriBelanja->id,
            'amaun' => 250,
            'id_tabung_khas' => $linkedFund->id,
            'penerima' => 'Vendor Ujian',
            'catatan' => 'Belanja ujian tabung',
            'created_by' => $bendahari->id,
            'status' => 'DRAF',
        ]);

        $this->actingAs($bendahari)
            ->get(route('admin.tabung-khas.index'))
            ->assertOk()
            ->assertSee('Tabung Pembangunan')
            ->assertSee('Tabung Wakaf Ujian')
            ->assertSee('Digunakan dalam 2 transaksi')
            ->assertDontSee('Tabung Tersembunyi');

        $this->actingAs($bendahari)
            ->get(route('admin.tabung-khas.index', ['status' => 'linked']))
            ->assertOk()
            ->assertSee('Tabung Pembangunan')
            ->assertDontSee('Tabung Wakaf Ujian');

        $this->actingAs($bendahari)
            ->post(route('admin.tabung-khas.store'), [
                'id_masjid' => $masjidB->id,
                'nama_tabung' => 'Tabung Pendidikan',
                'aktif' => true,
            ])
            ->assertRedirect();

        $created = TabungKhas::query()->where('nama_tabung', 'Tabung Pendidikan')->first();
        $this->assertNotNull($created);
        $this->assertSame($masjidA->id, $created->id_masjid);

        $this->actingAs($bendahari)
            ->put(route('admin.tabung-khas.update', $unusedFund), [
                'id_masjid' => $masjidA->id,
                'nama_tabung' => 'Tabung Wakaf Asnaf',
                'aktif' => true,
            ])
            ->assertRedirect();

        $this->assertSame('Tabung Wakaf Asnaf', $unusedFund->fresh()->nama_tabung);
        $this->assertTrue($unusedFund->fresh()->aktif);

        $this->actingAs($bendahari)
            ->delete(route('admin.tabung-khas.destroy', $linkedFund))
            ->assertSessionHasErrors('tabung_khas');

        $this->assertNotNull($linkedFund->fresh());

        $this->actingAs($bendahari)
            ->patch(route('admin.tabung-khas.status', $unusedFund))
            ->assertRedirect();

        $this->assertFalse($unusedFund->fresh()->aktif);

        $this->actingAs($bendahari)
            ->delete(route('admin.tabung-khas.destroy', $created))
            ->assertRedirect();

        $this->assertNull($created->fresh());
    }
}
