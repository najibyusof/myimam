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
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FinanceReportsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_requires_authentication(): void
    {
        $this->getJson('/api/finance/reports/jumaat?tahun=2026')
            ->assertUnauthorized();
    }

    public function test_reports_requires_permission(): void
    {
        [, $user] = $this->createContext(withReportsPermission: false);

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/reports/jumaat?tahun=2026')
            ->assertForbidden();
    }

    public function test_superadmin_reports_require_id_masjid(): void
    {
        [$masjid] = $this->createContext(withReportsPermission: true);

        $superadmin = User::factory()->create([
            'peranan' => 'superadmin',
            'id_masjid' => null,
            'aktif' => true,
        ]);

        $superadmin->assignRole('ReportViewer');

        Sanctum::actingAs($superadmin);

        $this->getJson('/api/finance/reports/jumaat?tahun=2026')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id_masjid']);

        $this->getJson('/api/finance/reports/jumaat?tahun=2026&id_masjid=' . $masjid->id)
            ->assertOk();
    }

    public function test_reports_endpoints_return_success_payloads_for_authorized_user(): void
    {
        [, $user, $akaun] = $this->createContext(withReportsPermission: true, seedFinanceData: true);

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/reports/buku-tunai?akaun_id=' . $akaun->id . '&tarikh_mula=2026-04-01&tarikh_tamat=2026-04-30')
            ->assertOk()
            ->assertJsonStructure(['data' => ['akaun', 'tempoh', 'rows', 'ringkasan']]);

        $this->getJson('/api/finance/reports/jumaat?tahun=2026&jenis_paparan=senarai_jumaat')
            ->assertOk()
            ->assertJsonStructure(['data' => ['rows', 'senarai_rows', 'chart_labels', 'chart_data']]);

        $this->getJson('/api/finance/reports/derma?tarikh_dari=2026-04-01&tarikh_hingga=2026-04-30&jenis_paparan=senarai_transaksi')
            ->assertOk()
            ->assertJsonStructure(['data' => ['rows', 'ringkasan_bulan', 'senarai_rows', 'jumlah_keseluruhan']]);

        $this->getJson('/api/finance/reports/belanja?tarikh_dari=2026-04-01&tarikh_hingga=2026-04-30&jenis_paparan=senarai_transaksi')
            ->assertOk()
            ->assertJsonStructure(['data' => ['rows', 'ringkasan_bulan', 'senarai_rows', 'jumlah_keseluruhan']]);

        $this->getJson('/api/finance/reports/penyata?jenis_penyata=bulanan&tahun=2026&bulan=4')
            ->assertOk()
            ->assertJsonStructure(['data' => ['tempoh_label', 'pendapatan_rows', 'perbelanjaan_rows', 'lebihan_kurangan']]);

        $this->getJson('/api/finance/reports/tabung?tarikh_dari=2026-04-01&tarikh_hingga=2026-04-30')
            ->assertOk()
            ->assertJsonStructure(['data' => ['rows', 'chart', 'total_masuk', 'total_keluar', 'total_baki']]);
    }

    /**
     * @return array{0:Masjid,1:User,2:Akaun}
     */
    private function createContext(bool $withReportsPermission, bool $seedFinanceData = false): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $reportsPermission = Permission::query()->firstOrCreate([
            'name' => 'reports.view',
            'guard_name' => 'web',
        ]);

        $role = Role::query()->firstOrCreate([
            'name' => 'ReportViewer',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($withReportsPermission ? [$reportsPermission] : []);

        $masjid = Masjid::factory()->create();

        $user = User::factory()->create([
            'id_masjid' => $masjid->id,
            'peranan' => 'staff',
            'aktif' => true,
        ]);
        $user->assignRole($role);

        $akaun = Akaun::factory()->create([
            'id_masjid' => $masjid->id,
            'jenis' => 'bank',
            'status_aktif' => true,
            'nama_akaun' => 'Akaun Operasi API',
        ]);

        if ($seedFinanceData) {
            $this->seedFinanceData($masjid, $user, $akaun);
        }

        return [$masjid, $user, $akaun];
    }

    private function seedFinanceData(Masjid $masjid, User $user, Akaun $akaun): void
    {
        $sumberDerma = SumberHasil::query()->create([
            'id_masjid' => $masjid->id,
            'kod' => 'DERMA-API',
            'nama_sumber' => 'Derma Umum API',
            'jenis' => 'derma',
            'aktif' => true,
        ]);

        $sumberJumaat = SumberHasil::query()->create([
            'id_masjid' => $masjid->id,
            'kod' => 'JUMAAT-API',
            'nama_sumber' => 'Kutipan Jumaat API',
            'jenis' => 'jumaat',
            'aktif' => true,
        ]);

        $kategori = KategoriBelanja::query()->create([
            'id_masjid' => $masjid->id,
            'kod' => 'UTIL-API',
            'nama_kategori' => 'Utiliti API',
            'aktif' => true,
        ]);

        $tabung = TabungKhas::query()->create([
            'id_masjid' => $masjid->id,
            'nama_tabung' => 'Tabung Operasi API',
            'aktif' => true,
        ]);

        Hasil::query()->create([
            'id_masjid' => $masjid->id,
            'tarikh' => '2026-04-05',
            'no_resit' => 'RES-API-001',
            'id_akaun' => $akaun->id,
            'id_sumber_hasil' => $sumberDerma->id,
            'amaun_tunai' => 100.00,
            'amaun_online' => 50.00,
            'jumlah' => 150.00,
            'id_tabung_khas' => $tabung->id,
            'id_program' => null,
            'jenis_jumaat' => null,
            'catatan' => 'Derma untuk laporan API',
            'created_by' => $user->id,
        ]);

        Hasil::query()->create([
            'id_masjid' => $masjid->id,
            'tarikh' => '2026-04-12',
            'no_resit' => 'RES-API-002',
            'id_akaun' => $akaun->id,
            'id_sumber_hasil' => $sumberJumaat->id,
            'amaun_tunai' => 200.00,
            'amaun_online' => 0.00,
            'jumlah' => 200.00,
            'id_tabung_khas' => null,
            'id_program' => null,
            'jenis_jumaat' => 'biasa',
            'catatan' => 'Kutipan jumaat untuk laporan API',
            'created_by' => $user->id,
        ]);

        Belanja::query()->create([
            'id_masjid' => $masjid->id,
            'tarikh' => '2026-04-18',
            'id_akaun' => $akaun->id,
            'id_kategori_belanja' => $kategori->id,
            'amaun' => 80.00,
            'id_tabung_khas' => $tabung->id,
            'id_program' => null,
            'penerima' => 'TNB API',
            'catatan' => 'Bil utiliti untuk laporan API',
            'created_by' => $user->id,
            'status' => 'LULUS',
            'id_baucar' => null,
            'is_deleted' => false,
            'deleted_by' => null,
            'deleted_at' => null,
            'dilulus_oleh' => $user->id,
            'tarikh_lulus' => now(),
        ]);
    }
}
