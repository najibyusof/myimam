<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LaporanBelanjaReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Masjid $masjid;
    protected Akaun $akaun;
    protected KategoriBelanja $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::firstOrCreate([
            'name' => 'view laporan belanja',
            'guard_name' => 'web',
        ]);

        $role = Role::firstOrCreate([
            'name' => 'Belanja Reporter',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo($permission);

        $this->masjid = Masjid::factory()->create();
        $this->user = User::factory()->create(['id_masjid' => $this->masjid->id]);
        $this->user->assignRole($role);
        $this->actingAs($this->user);

        $this->akaun = Akaun::query()->create([
            'id_masjid' => $this->masjid->id,
            'nama_akaun' => 'Akaun Operasi',
            'jenis' => 'bank',
            'no_akaun' => '1234567890',
            'nama_bank' => 'Bank Ujian',
            'status_aktif' => true,
        ]);

        $this->kategori = KategoriBelanja::query()->create([
            'id_masjid' => $this->masjid->id,
            'kod' => 'UTIL',
            'nama_kategori' => 'Utiliti',
            'aktif' => true,
        ]);
    }

    public function test_can_access_laporan_belanja_page(): void
    {
        $response = $this->get(route('laporan.belanja'));

        $response->assertOk();
        $response->assertViewIs('laporan.belanja');
        $response->assertSee('Laporan Belanja');
    }

    public function test_can_group_laporan_belanja_by_month(): void
    {
        Belanja::query()->create([
            'id_masjid' => $this->masjid->id,
            'tarikh' => '2026-03-10',
            'id_akaun' => $this->akaun->id,
            'id_kategori_belanja' => $this->kategori->id,
            'amaun' => 300.00,
            'penerima' => 'Vendor A',
            'created_by' => $this->user->id,
            'status' => 'DRAF',
            'is_deleted' => false,
        ]);

        Belanja::query()->create([
            'id_masjid' => $this->masjid->id,
            'tarikh' => '2026-04-12',
            'id_akaun' => $this->akaun->id,
            'id_kategori_belanja' => $this->kategori->id,
            'amaun' => 500.00,
            'penerima' => 'Vendor B',
            'created_by' => $this->user->id,
            'status' => 'LULUS',
            'is_deleted' => false,
        ]);

        $response = $this->get(route('laporan.belanja', [
            'jenis_paparan' => 'ringkasan_bulan',
            'tarikh_dari' => '2026-03-01',
            'tarikh_hingga' => '2026-04-30',
        ]));

        $response->assertOk();
        $response->assertSee('Ringkasan Belanja Mengikut Bulan');
        $response->assertSee('300.00');
        $response->assertSee('500.00');
    }

    public function test_can_filter_laporan_belanja_by_status(): void
    {
        Belanja::query()->create([
            'id_masjid' => $this->masjid->id,
            'tarikh' => '2026-04-10',
            'id_akaun' => $this->akaun->id,
            'id_kategori_belanja' => $this->kategori->id,
            'amaun' => 111.00,
            'penerima' => 'Draf Vendor',
            'created_by' => $this->user->id,
            'status' => 'DRAF',
            'is_deleted' => false,
        ]);

        Belanja::query()->create([
            'id_masjid' => $this->masjid->id,
            'tarikh' => '2026-04-11',
            'id_akaun' => $this->akaun->id,
            'id_kategori_belanja' => $this->kategori->id,
            'amaun' => 222.00,
            'penerima' => 'Lulus Vendor',
            'created_by' => $this->user->id,
            'status' => 'LULUS',
            'is_deleted' => false,
        ]);

        $response = $this->get(route('laporan.belanja', [
            'jenis_paparan' => 'senarai_transaksi',
            'status' => 'draf',
            'tarikh_dari' => '2026-04-01',
            'tarikh_hingga' => '2026-04-30',
        ]));

        $response->assertOk();
        $response->assertSee('Draf Vendor');
        $response->assertDontSee('Lulus Vendor');
    }

    public function test_senarai_transaksi_rows_have_clickable_edit_link(): void
    {
        $belanja = Belanja::query()->create([
            'id_masjid' => $this->masjid->id,
            'tarikh' => '2026-04-13',
            'id_akaun' => $this->akaun->id,
            'id_kategori_belanja' => $this->kategori->id,
            'amaun' => 345.00,
            'penerima' => 'Klik Vendor',
            'catatan' => 'Klik untuk edit',
            'created_by' => $this->user->id,
            'status' => 'DRAF',
            'is_deleted' => false,
        ]);

        $response = $this->get(route('laporan.belanja', [
            'jenis_paparan' => 'senarai_transaksi',
            'tarikh_dari' => '2026-04-01',
            'tarikh_hingga' => '2026-04-30',
        ]));

        $response->assertOk();
        $response->assertSee(route('admin.belanja.edit', $belanja), false);
        $response->assertSee('Buka / Edit');
    }

    public function test_can_export_laporan_belanja_to_pdf_and_excel(): void
    {
        Belanja::query()->create([
            'id_masjid' => $this->masjid->id,
            'tarikh' => '2026-04-14',
            'id_akaun' => $this->akaun->id,
            'id_kategori_belanja' => $this->kategori->id,
            'amaun' => 678.00,
            'penerima' => 'Export Vendor',
            'created_by' => $this->user->id,
            'status' => 'LULUS',
            'is_deleted' => false,
        ]);

        $pdfResponse = $this->get(route('laporan.belanja.export.pdf', [
            'jenis_paparan' => 'ringkasan_bulan',
            'status' => 'lulus',
            'tarikh_dari' => '2026-04-01',
            'tarikh_hingga' => '2026-04-30',
        ]));

        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');

        $excelResponse = $this->get(route('laporan.belanja.export.excel', [
            'jenis_paparan' => 'senarai_transaksi',
            'status' => 'lulus',
            'tarikh_dari' => '2026-04-01',
            'tarikh_hingga' => '2026-04-30',
        ]));

        $excelResponse->assertOk();
        $excelResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_unauthorized_user_cannot_access_or_export_laporan_belanja(): void
    {
        $unauthorizedUser = User::factory()->create(['id_masjid' => $this->masjid->id]);
        $this->actingAs($unauthorizedUser);

        $this->get(route('laporan.belanja'))->assertForbidden();
        $this->get(route('laporan.belanja.export.pdf'))->assertForbidden();
        $this->get(route('laporan.belanja.export.excel'))->assertForbidden();
    }
}
