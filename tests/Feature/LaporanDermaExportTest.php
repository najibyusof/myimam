<?php

namespace Tests\Feature;

use App\Models\Hasil;
use App\Models\Masjid;
use App\Models\SumberHasil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LaporanDermaExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Masjid $masjid;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions and roles
        $permission = Permission::firstOrCreate(['name' => 'view laporan derma']);
        $role = Role::firstOrCreate(['name' => 'Viewer']);
        $role->givePermissionTo($permission);

        $this->masjid = Masjid::factory()->create();
        $this->user = User::factory()->create(['id_masjid' => $this->masjid->id]);
        $this->user->assignRole($role);
        $this->actingAs($this->user);
    }

    public function test_can_access_derma_report_page()
    {
        $response = $this->get(route('laporan.derma'));

        $response->assertStatus(200);
        $response->assertViewIs('laporan.derma');
    }

    public function test_can_export_derma_to_excel()
    {
        $sumberHasil = SumberHasil::factory([
            'id_masjid' => $this->masjid->id,
            'jenis' => 'derma',
        ])->create();

        Hasil::factory([
            'id_masjid' => $this->masjid->id,
            'id_sumber_hasil' => $sumberHasil->id,
            'created_by' => $this->user->id,
        ])->count(5)->create();

        $response = $this->get(route('laporan.derma.export.excel'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_can_export_derma_to_pdf()
    {
        $sumberHasil = SumberHasil::factory([
            'id_masjid' => $this->masjid->id,
            'jenis' => 'derma',
        ])->create();

        Hasil::factory([
            'id_masjid' => $this->masjid->id,
            'id_sumber_hasil' => $sumberHasil->id,
            'created_by' => $this->user->id,
        ])->count(5)->create();

        $response = $this->get(route('laporan.derma.export.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_export_with_filters()
    {
        $sumberHasil = SumberHasil::factory([
            'id_masjid' => $this->masjid->id,
            'jenis' => 'derma',
        ])->create();

        Hasil::factory([
            'id_masjid' => $this->masjid->id,
            'id_sumber_hasil' => $sumberHasil->id,
            'created_by' => $this->user->id,
        ])->count(10)->create();

        $startDate = now()->subDays(5)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->get(route('laporan.derma.export.excel', [
            'tarikh_dari' => $startDate,
            'tarikh_hingga' => $endDate,
        ]));

        $response->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_export()
    {
        $unauthorizedUser = User::factory()->create(['id_masjid' => $this->masjid->id]);
        $this->actingAs($unauthorizedUser);

        $response = $this->get(route('laporan.derma.export.excel'));

        $response->assertStatus(403);
    }
}
