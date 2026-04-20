<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\Hasil;
use App\Models\Masjid;
use App\Models\SumberHasil;
use App\Models\TabungKhas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HasilImportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_shows_valid_and_invalid_rows_without_inserting_data(): void
    {
        [$user, $masjid, $akaun, $sumber, $tabung] = $this->createImportContext();

        $file = UploadedFile::fake()->createWithContent('hasil-import.csv', implode("\n", [
            'tarikh,sumber,amaun,akaun,catatan,tabung_khas',
            '01/01/2026,Derma Individu,100.00,Tunai Utama,Sumbangan jemaah,Tabung Umum',
            '32/01/2026,,0,Akaun Tidak Wujud,Baris rosak,Tabung Tidak Ada',
        ]));

        $response = $this->actingAs($user)->post(route('admin.hasil.import.preview'), [
            'excel_file' => $file,
        ]);

        $response->assertOk()
            ->assertSeeText('Pratonton Import')
            ->assertSeeText('hasil-import.csv')
            ->assertSeeText('Jumlah baris:')
            ->assertSeeText('Sah:')
            ->assertSeeText('Gagal:')
            ->assertSeeText('Valid')
            ->assertSeeText('Tidak Valid')
            ->assertSeeText('Derma Individu')
            ->assertSeeText('Akaun tidak ditemui: akaun tidak wujud')
            ->assertSeeText('Sumber wajib diisi.');

        $this->assertSame(0, Hasil::query()->count());
        $this->assertSame($masjid->id, (int) $response->viewData('selectedMasjidId'));
        $this->assertSame(2, (int) $response->viewData('totalRows'));
        $this->assertSame(1, (int) $response->viewData('validRows'));
        $this->assertSame(1, (int) $response->viewData('invalidRows'));
        $this->assertNotEmpty($response->viewData('previewToken'));
    }

    public function test_store_imports_only_valid_rows_from_preview_cache(): void
    {
        [$user] = $this->createImportContext();

        $file = UploadedFile::fake()->createWithContent('hasil-import-store.csv', implode("\n", [
            'tarikh,sumber,amaun,akaun,catatan,tabung_khas',
            '02/01/2026,Derma Individu,150.50,Tunai Utama,Baris sah,Tabung Umum',
            '03/01/2026,Sumber Tidak Ada,200.00,Tunai Utama,Baris gagal,Tabung Umum',
        ]));

        $preview = $this->actingAs($user)->post(route('admin.hasil.import.preview'), [
            'excel_file' => $file,
        ]);

        $preview->assertOk();
        $previewToken = (string) $preview->viewData('previewToken');

        $store = $this->actingAs($user)->post(route('admin.hasil.import.store'), [
            'preview_token' => $previewToken,
        ]);

        $store->assertRedirect(route('admin.hasil.import.index'));
        $store->assertSessionHas('status', '1 berjaya, 1 gagal.');

        $this->assertSame(1, Hasil::query()->count());

        $hasil = Hasil::query()->first();
        $this->assertNotNull($hasil);
        $this->assertSame('2026-01-02', $hasil->tarikh?->toDateString());
        $this->assertSame('150.50', (string) $hasil->jumlah);
        $this->assertSame('Baris sah', $hasil->catatan);
        $this->assertNotNull($hasil->no_resit);
    }

    public function test_store_auto_maps_akaun_and_tabung_khas_by_alias_name(): void
    {
        [$user] = $this->createImportContext();

        $file = UploadedFile::fake()->createWithContent('hasil-import-alias.csv', implode("\n", [
            'tarikh,sumber,amaun,akaun,catatan,tabung_khas',
            '02/01/2026,Derma Individu,120.00,Tunai-Utama,Auto map akaun+tabung,Tabung/Umum',
        ]));

        $preview = $this->actingAs($user)->post(route('admin.hasil.import.preview'), [
            'excel_file' => $file,
        ]);

        $preview->assertOk();
        $this->assertSame(1, (int) $preview->viewData('validRows'));

        $previewToken = (string) $preview->viewData('previewToken');
        $this->actingAs($user)->post(route('admin.hasil.import.store'), [
            'preview_token' => $previewToken,
        ])->assertRedirect(route('admin.hasil.import.index'));

        $hasil = Hasil::query()->first();
        $this->assertNotNull($hasil);
        $this->assertSame('Auto map akaun+tabung', $hasil->catatan);
        $this->assertSame('120.00', (string) $hasil->jumlah);
    }

    public function test_sample_download_returns_excel_file(): void
    {
        [$user] = $this->createImportContext();

        $response = $this->actingAs($user)->get(route('admin.hasil.import.sample'));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=sample-import-hasil.xlsx');
    }

    public function test_import_index_hides_masjid_field_for_non_superadmin(): void
    {
        [$user] = $this->createImportContext();

        $response = $this->actingAs($user)->get(route('admin.hasil.import.index'));

        $response->assertOk()
            ->assertSeeText('Import Data Hasil')
            ->assertSeeText('Muat Turun Sampel Excel')
            ->assertDontSee('name="id_masjid"', false)
            ->assertDontSeeText('Pilih masjid');
    }

    public function test_import_index_shows_masjid_field_for_superadmin(): void
    {
        [, $masjid] = $this->createImportContext();

        Permission::query()->firstOrCreate([
            'name' => 'hasil.create',
            'guard_name' => 'web',
        ]);

        $role = Role::query()->firstOrCreate([
            'name' => 'Superadmin',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions(['hasil.create']);

        $superadmin = User::query()->create([
            'name' => 'Superadmin Import',
            'email' => 'superadmin.import@example.test',
            'password' => 'password',
            'peranan' => 'superadmin',
            'aktif' => true,
        ]);
        $superadmin->assignRole($role);

        $response = $this->actingAs($superadmin)->get(route('admin.hasil.import.index'));

        $response->assertOk()
            ->assertSee('name="id_masjid"', false)
            ->assertSeeText('Pilih masjid')
            ->assertSeeText($masjid->nama);
    }

    public function test_error_report_download_available_for_invalid_rows(): void
    {
        [$user] = $this->createImportContext();

        $file = UploadedFile::fake()->createWithContent('hasil-import-errors.csv', implode("\n", [
            'tarikh,sumber,amaun,akaun,catatan,tabung_khas',
            '32/01/2026,Derma Individu,0,Akaun Tidak Wujud,Baris gagal,Tabung Tidak Ada',
        ]));

        $preview = $this->actingAs($user)->post(route('admin.hasil.import.preview'), [
            'excel_file' => $file,
        ]);

        $preview->assertOk();
        $previewToken = (string) $preview->viewData('previewToken');

        $response = $this->actingAs($user)->get(route('admin.hasil.import.error-report', ['token' => $previewToken]));

        $response->assertOk();
        $this->assertStringContainsString('attachment; filename=hasil-import-ralat-', (string) $response->headers->get('content-disposition'));
    }

    /**
     * @return array{0: User, 1: Masjid, 2: Akaun, 3: SumberHasil, 4: TabungKhas}
     */
    private function createImportContext(): array
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

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Import Ujian',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addDays(30),
        ]);

        $user = User::query()->create([
            'name' => 'Bendahari Import',
            'email' => 'bendahari.import@example.test',
            'password' => 'password',
            'id_masjid' => $masjid->id,
            'aktif' => true,
        ]);
        $user->assignRole($role);

        $akaun = Akaun::query()->create([
            'id_masjid' => $masjid->id,
            'nama_akaun' => 'Tunai Utama',
            'jenis' => 'tunai',
            'status_aktif' => true,
        ]);

        $sumber = SumberHasil::query()->create([
            'id_masjid' => $masjid->id,
            'kod' => 'DERMA',
            'nama_sumber' => 'Derma Individu',
            'jenis' => 'Derma',
            'aktif' => true,
        ]);

        $tabung = TabungKhas::query()->create([
            'id_masjid' => $masjid->id,
            'nama_tabung' => 'Tabung Umum',
            'aktif' => true,
        ]);

        return [$user, $masjid, $akaun, $sumber, $tabung];
    }
}
