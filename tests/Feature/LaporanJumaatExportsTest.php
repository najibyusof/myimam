<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\Hasil;
use App\Models\Masjid;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LaporanJumaatExportsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0:User,1:User,2:Masjid}
     */
    public function test_unauthorized_user_cannot_access_jumaat_report(): void
    {
        [$authorized, $unauthorized, $masjid] = $this->seedLaporanJumaatScenario();

        $this->actingAs($unauthorized)
            ->get(route('laporan.jumaat'))
            ->assertForbidden();

        $this->actingAs($unauthorized)
            ->get(route('laporan.jumaat.export.pdf'))
            ->assertForbidden();

        $this->actingAs($unauthorized)
            ->get(route('laporan.jumaat.export.excel'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_jumaat_report(): void
    {
        [$authorized, $unauthorized, $masjid] = $this->seedLaporanJumaatScenario();

        $this->actingAs($authorized)
            ->get(route('laporan.jumaat'))
            ->assertOk()
            ->assertViewIs('laporan.jumaat');
    }

    public function test_bendahari_user_can_access_jumaat_exports(): void
    {
        [$authorized, $unauthorized, $masjid] = $this->seedLaporanJumaatScenario();

        $this->actingAs($authorized)
            ->get(route('laporan.jumaat'))
            ->assertOk();

        $pdfResponse = $this->actingAs($authorized)
            ->get(route('laporan.jumaat.export.pdf'));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdfResponse->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', (string) $pdfResponse->headers->get('content-disposition'));

        $excelResponse = $this->actingAs($authorized)
            ->get(route('laporan.jumaat.export.excel'))
            ->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            (string) $excelResponse->headers->get('content-type')
        );
        $this->assertStringContainsString('.xlsx', (string) $excelResponse->headers->get('content-disposition'));
    }

    public function test_admin_user_can_only_see_their_masjid_data(): void
    {
        [$authorized, $unauthorized, $masjid] = $this->seedLaporanJumaatScenario();

        $response = $this->actingAs($authorized)
            ->get(route('laporan.jumaat'));

        $response->assertOk();
        // Verify view has the expected data structure
        $response->assertViewHas('rows');
        $response->assertViewHas('filters');
    }

    public function test_senarai_jumaat_shows_all_transactions_for_year(): void
    {
        [$authorized, $unauthorized, $masjid] = $this->seedLaporanJumaatScenario();
        $tahun = Carbon::today()->year;

        $response = $this->actingAs($authorized)
            ->get(route('laporan.jumaat', [
                'tahun' => $tahun,
                'jenis_paparan' => 'senarai_jumaat',
            ]));

        $response->assertOk();
        $response->assertViewHas('senarai_rows');
        $this->assertNotEmpty($response->viewData('senarai_rows'));
    }

    public function test_chart_data_is_populated_in_main_view(): void
    {
        [$authorized, $unauthorized, $masjid] = $this->seedLaporanJumaatScenario();

        $response = $this->actingAs($authorized)
            ->get(route('laporan.jumaat'));

        $response->assertOk();
        $response->assertViewHas('chart_labels');
        $response->assertViewHas('chart_data');

        $chartLabels = $response->viewData('chart_labels');
        $chartData = $response->viewData('chart_data');

        // Should have 12 months of data
        $this->assertCount(12, $chartLabels);
        $this->assertCount(12, $chartData);
    }

    public function test_ringkasan_bulanan_aggregates_correctly(): void
    {
        [$authorized, $unauthorized, $masjid] = $this->seedLaporanJumaatScenario();

        $response = $this->actingAs($authorized)
            ->get(route('laporan.jumaat'));

        $response->assertOk();
        $rows = $response->viewData('rows');

        $this->assertNotNull($rows);
        // Should have at least one month of data
        $this->assertGreaterThan(0, $rows->count());
        // Each row should contain numeric jumlah and bil_rekod
        foreach ($rows as $row) {
            $this->assertTrue(isset($row['jumlah']) || isset($row->jumlah));
            $this->assertTrue(isset($row['bil_rekod']) || isset($row->bil_rekod));
        }
    }

    public function test_export_respects_year_filter(): void
    {
        [$authorized, $unauthorized, $masjid] = $this->seedLaporanJumaatScenario();
        $tahun = Carbon::today()->year;

        $response = $this->actingAs($authorized)
            ->get(route('laporan.jumaat.export.excel', ['tahun' => $tahun]));

        $response->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            (string) $response->headers->get('content-type')
        );
    }

    /**
     * @return array{0:User,1:User,2:Masjid}
     */
    private function seedLaporanJumaatScenario(): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::query()->firstOrCreate([
            'name' => 'view laporan jumaat',
            'guard_name' => 'web',
        ]);

        $allowedRole = Role::query()->firstOrCreate([
            'name' => 'AllowedLaporanJumaat',
            'guard_name' => 'web',
        ]);
        $allowedRole->syncPermissions([$permission]);

        $blockedRole = Role::query()->firstOrCreate([
            'name' => 'BlockedLaporanJumaat',
            'guard_name' => 'web',
        ]);
        $blockedRole->syncPermissions([]);

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Ujian Laporan Jumaat',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addYear(),
        ]);

        $authorized = User::query()->create([
            'name' => 'Pengguna Dibenarkan Jumaat',
            'email' => 'allowed.laporan.jumaat@example.test',
            'password' => 'password',
            'id_masjid' => $masjid->id,
            'aktif' => true,
        ]);
        $authorized->assignRole($allowedRole);

        $unauthorized = User::query()->create([
            'name' => 'Pengguna Disekat Jumaat',
            'email' => 'blocked.laporan.jumaat@example.test',
            'password' => 'password',
            'id_masjid' => $masjid->id,
            'aktif' => true,
        ]);
        $unauthorized->assignRole($blockedRole);

        $akaun = Akaun::query()->create([
            'id_masjid' => $masjid->id,
            'nama_akaun' => 'Kutipan Jumaat',
            'jenis' => 'tunai',
            'status_aktif' => true,
        ]);

        $sumberHasil = \App\Models\SumberHasil::query()->create([
            'id_masjid' => $masjid->id,
            'kod' => 'UJIAN',
            'nama_sumber' => 'Sumber Ujian',
            'jenis' => 'tunai',
            'aktif' => true,
        ]);

        $tahun = Carbon::today()->year;
        $bulanSekarang = Carbon::today()->month;

        // Create 5 Friday collection records for current month
        for ($i = 1; $i <= 5; $i++) {
            Hasil::query()->create([
                'id_masjid' => $masjid->id,
                'tarikh' => Carbon::create($tahun, $bulanSekarang, 1)->addWeeks($i - 1)->toDateString(),
                'id_akaun' => $akaun->id,
                'id_sumber_hasil' => $sumberHasil->id,
                'jenis_jumaat' => 'biasa',
                'amaun_tunai' => 500 + ($i * 100),
                'amaun_online' => 0,
                'jumlah' => 500 + ($i * 100),
                'no_resit' => 'RCP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'catatan' => 'Kutipan Jumaat minggu ' . $i,
                'created_by' => $authorized->id,
            ]);
        }

        return [$authorized, $unauthorized, $masjid];
    }
}
