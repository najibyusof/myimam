<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\BaucarBayaran;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\PindahanAkaun;
use App\Models\SumberHasil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportingModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporting_module_financial_reports_with_filters_and_scope(): void
    {
        $permission = Permission::query()->firstOrCreate(['name' => 'reports.view', 'guard_name' => 'web']);

        $managerRole = Role::query()->firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $managerRole->syncPermissions([$permission]);

        $userRole = Role::query()->firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $userRole->syncPermissions([]);

        $masjidA = Masjid::query()->create(['nama' => 'Masjid Report A']);
        $masjidB = Masjid::query()->create(['nama' => 'Masjid Report B']);

        $managerA = User::query()->create([
            'name' => 'Manager Report A',
            'email' => 'manager.report.a@example.test',
            'password' => 'password',
            'id_masjid' => $masjidA->id,
            'aktif' => true,
        ]);
        $managerA->assignRole($managerRole);

        $regular = User::query()->create([
            'name' => 'Regular User',
            'email' => 'regular.report@example.test',
            'password' => 'password',
            'id_masjid' => $masjidA->id,
            'aktif' => true,
        ]);
        $regular->assignRole($userRole);

        $akaunA1 = Akaun::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_akaun' => 'Bank Operasi A',
            'jenis' => 'bank',
            'status_aktif' => true,
        ]);
        $akaunA2 = Akaun::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_akaun' => 'Tunai A',
            'jenis' => 'tunai',
            'status_aktif' => true,
        ]);
        $akaunB1 = Akaun::query()->create([
            'id_masjid' => $masjidB->id,
            'nama_akaun' => 'Bank Operasi B',
            'jenis' => 'bank',
            'status_aktif' => true,
        ]);

        $sumberA = SumberHasil::query()->create([
            'id_masjid' => $masjidA->id,
            'kod' => 'SUMA',
            'nama_sumber' => 'Sumbangan A',
            'jenis' => 'online',
            'aktif' => true,
        ]);
        $sumberB = SumberHasil::query()->create([
            'id_masjid' => $masjidB->id,
            'kod' => 'SUMB',
            'nama_sumber' => 'Sumbangan B',
            'jenis' => 'online',
            'aktif' => true,
        ]);

        $kategoriA = KategoriBelanja::query()->create([
            'id_masjid' => $masjidA->id,
            'kod' => 'KATA',
            'nama_kategori' => 'Operasi A',
            'aktif' => true,
        ]);
        $kategoriB = KategoriBelanja::query()->create([
            'id_masjid' => $masjidB->id,
            'kod' => 'KATB',
            'nama_kategori' => 'Operasi B',
            'aktif' => true,
        ]);

        Hasil::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => '2026-04-10',
            'id_akaun' => $akaunA1->id,
            'id_sumber_hasil' => $sumberA->id,
            'amaun_tunai' => 0,
            'amaun_online' => 1000,
            'jumlah' => 1000,
            'catatan' => 'Hasil A1',
            'created_by' => $managerA->id,
        ]);
        Hasil::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => '2026-05-01',
            'id_akaun' => $akaunA2->id,
            'id_sumber_hasil' => $sumberA->id,
            'amaun_tunai' => 300,
            'amaun_online' => 0,
            'jumlah' => 300,
            'catatan' => 'Hasil A2',
            'created_by' => $managerA->id,
        ]);
        Hasil::query()->create([
            'id_masjid' => $masjidB->id,
            'tarikh' => '2026-04-10',
            'id_akaun' => $akaunB1->id,
            'id_sumber_hasil' => $sumberB->id,
            'amaun_tunai' => 0,
            'amaun_online' => 999,
            'jumlah' => 999,
            'catatan' => 'Hasil B',
            'created_by' => $managerA->id,
        ]);

        Belanja::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => '2026-04-11',
            'id_akaun' => $akaunA1->id,
            'id_kategori_belanja' => $kategoriA->id,
            'amaun' => 400,
            'penerima' => 'Vendor A',
            'created_by' => $managerA->id,
            'status' => 'LULUS',
            'is_deleted' => false,
        ]);
        Belanja::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => '2026-04-12',
            'id_akaun' => $akaunA1->id,
            'id_kategori_belanja' => $kategoriA->id,
            'amaun' => 50,
            'penerima' => 'Deleted A',
            'created_by' => $managerA->id,
            'status' => 'LULUS',
            'is_deleted' => true,
        ]);
        Belanja::query()->create([
            'id_masjid' => $masjidB->id,
            'tarikh' => '2026-04-11',
            'id_akaun' => $akaunB1->id,
            'id_kategori_belanja' => $kategoriB->id,
            'amaun' => 888,
            'penerima' => 'Vendor B',
            'created_by' => $managerA->id,
            'status' => 'LULUS',
            'is_deleted' => false,
        ]);

        BaucarBayaran::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => '2026-04-15',
            'no_baucar' => 'BV-A-001',
            'id_akaun' => $akaunA1->id,
            'kaedah' => 'bank',
            'jumlah' => 400,
            'status' => 'LULUS',
            'created_by' => $managerA->id,
        ]);
        BaucarBayaran::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => '2026-04-16',
            'no_baucar' => 'BV-A-002',
            'id_akaun' => $akaunA1->id,
            'kaedah' => 'bank',
            'jumlah' => 999,
            'status' => 'DRAF',
            'created_by' => $managerA->id,
        ]);

        PindahanAkaun::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => '2026-04-20',
            'dari_akaun_id' => $akaunA1->id,
            'ke_akaun_id' => $akaunA2->id,
            'amaun' => 100,
            'created_by' => $managerA->id,
        ]);
        PindahanAkaun::query()->create([
            'id_masjid' => $masjidB->id,
            'tarikh' => '2026-04-20',
            'dari_akaun_id' => $akaunB1->id,
            'ke_akaun_id' => $akaunB1->id,
            'amaun' => 777,
            'created_by' => $managerA->id,
        ]);

        $this->actingAs($regular)
            ->get(route('admin.reporting.index'))
            ->assertForbidden();

        $this->actingAs($managerA)
            ->get(route('admin.reporting.index', [
                'date_from' => '2026-04-01',
                'date_to' => '2026-04-30',
            ]))
            ->assertOk()
            ->assertSee('RM 1,000.00')
            ->assertSee('RM 400.00')
            ->assertSee('RM 600.00')
            ->assertSee('RM 100.00')
            ->assertDontSee('RM 999.00');

        $this->actingAs($managerA)
            ->get(route('admin.reporting.index', [
                'date_from' => '2026-04-01',
                'date_to' => '2026-04-30',
                'akaun_id' => $akaunA2->id,
            ]))
            ->assertOk()
            ->assertSee('RM 0.00')
            ->assertSee('Tunai A');

        $this->actingAs($managerA)
            ->get(route('admin.reporting.index', [
                'date_from' => '2026-05-01',
                'date_to' => '2026-05-31',
            ]))
            ->assertOk()
            ->assertSee('2026-05')
            ->assertSee('RM 300.00');
    }
}
