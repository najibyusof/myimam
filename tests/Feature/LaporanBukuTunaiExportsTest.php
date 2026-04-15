<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\Hasil;
use App\Models\Masjid;
use App\Models\SumberHasil;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LaporanBukuTunaiExportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_and_print_routes_require_permission(): void
    {
        [$authorized, $unauthorized, $akaun] = $this->seedLaporanBukuTunaiScenario();
        $today = Carbon::today();

        $params = [
            'akaun_id' => $akaun->id,
            'tarikh_mula' => $today->copy()->startOfMonth()->toDateString(),
            'tarikh_akhir' => $today->toDateString(),
            'baki_awal' => 100,
        ];

        $this->actingAs($unauthorized)
            ->get(route('laporan.buku-tunai.export.pdf', $params))
            ->assertForbidden();

        $this->actingAs($unauthorized)
            ->get(route('laporan.buku-tunai.export.excel', $params))
            ->assertForbidden();

        $this->actingAs($unauthorized)
            ->get(route('laporan.buku-tunai.print', $params))
            ->assertForbidden();

        $this->actingAs($authorized)
            ->get(route('laporan.buku-tunai.print', $params))
            ->assertOk();
    }

    public function test_date_range_validation_is_enforced_for_buku_tunai_routes(): void
    {
        [$authorized,, $akaun] = $this->seedLaporanBukuTunaiScenario();
        $today = Carbon::today();

        $invalidOrder = [
            'akaun_id' => $akaun->id,
            'tarikh_mula' => $today->toDateString(),
            'tarikh_akhir' => $today->copy()->subDay()->toDateString(),
            'baki_awal' => 0,
        ];

        $this->actingAs($authorized)
            ->from(route('laporan.buku-tunai'))
            ->get(route('laporan.buku-tunai', $invalidOrder))
            ->assertRedirect(route('laporan.buku-tunai'))
            ->assertSessionHasErrors(['tarikh_akhir']);

        $overLimit = [
            'akaun_id' => $akaun->id,
            'tarikh_mula' => $today->copy()->subMonths(13)->startOfMonth()->toDateString(),
            'tarikh_akhir' => $today->toDateString(),
            'baki_awal' => 0,
        ];

        $this->actingAs($authorized)
            ->from(route('laporan.buku-tunai'))
            ->get(route('laporan.buku-tunai.export.pdf', $overLimit))
            ->assertRedirect(route('laporan.buku-tunai'))
            ->assertSessionHasErrors(['tarikh_akhir']);
    }

    public function test_pdf_excel_and_print_endpoints_return_successful_responses(): void
    {
        [$authorized,, $akaun] = $this->seedLaporanBukuTunaiScenario();
        $today = Carbon::today();

        $params = [
            'akaun_id' => $akaun->id,
            'tarikh_mula' => $today->copy()->startOfMonth()->toDateString(),
            'tarikh_akhir' => $today->toDateString(),
            'baki_awal' => 100,
        ];

        $pdfResponse = $this->actingAs($authorized)
            ->get(route('laporan.buku-tunai.export.pdf', $params));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdfResponse->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', (string) $pdfResponse->headers->get('content-disposition'));

        $excelResponse = $this->actingAs($authorized)
            ->get(route('laporan.buku-tunai.export.excel', $params))
            ->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            (string) $excelResponse->headers->get('content-type')
        );
        $this->assertStringContainsString('.xlsx', (string) $excelResponse->headers->get('content-disposition'));

        $this->actingAs($authorized)
            ->get(route('laporan.buku-tunai.print', $params))
            ->assertOk()
            ->assertSee('window.print()', false)
            ->assertSee('Laporan Buku Tunai');
    }

    /**
     * @return array{0:User,1:User,2:Akaun}
     */
    private function seedLaporanBukuTunaiScenario(): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::query()->firstOrCreate([
            'name' => 'view laporan buku tunai',
            'guard_name' => 'web',
        ]);

        $allowedRole = Role::query()->firstOrCreate([
            'name' => 'AllowedLaporanTunai',
            'guard_name' => 'web',
        ]);
        $allowedRole->syncPermissions([$permission]);

        $blockedRole = Role::query()->firstOrCreate([
            'name' => 'BlockedLaporanTunai',
            'guard_name' => 'web',
        ]);
        $blockedRole->syncPermissions([]);

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Ujian Laporan Tunai',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addYear(),
        ]);

        $authorized = User::query()->create([
            'name' => 'Pengguna Dibenarkan',
            'email' => 'allowed.laporan.tunai@example.test',
            'password' => 'password',
            'id_masjid' => $masjid->id,
            'aktif' => true,
        ]);
        $authorized->assignRole($allowedRole);

        $unauthorized = User::query()->create([
            'name' => 'Pengguna Disekat',
            'email' => 'blocked.laporan.tunai@example.test',
            'password' => 'password',
            'id_masjid' => $masjid->id,
            'aktif' => true,
        ]);
        $unauthorized->assignRole($blockedRole);

        $akaun = Akaun::query()->create([
            'id_masjid' => $masjid->id,
            'nama_akaun' => 'Akaun Ujian Tunai',
            'jenis' => 'tunai',
            'status_aktif' => true,
        ]);

        $sumberHasil = SumberHasil::query()->create([
            'id_masjid' => $masjid->id,
            'kod' => 'UJIAN',
            'nama_sumber' => 'Sumber Ujian',
            'jenis' => 'tunai',
            'aktif' => true,
        ]);

        Hasil::query()->create([
            'id_masjid' => $masjid->id,
            'tarikh' => Carbon::today()->subDays(2)->toDateString(),
            'id_akaun' => $akaun->id,
            'id_sumber_hasil' => $sumberHasil->id,
            'amaun_tunai' => 250,
            'amaun_online' => 0,
            'jumlah' => 250,
            'catatan' => 'Hasil ujian eksport',
            'created_by' => $authorized->id,
        ]);

        return [$authorized, $unauthorized, $akaun];
    }
}
