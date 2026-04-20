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
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BankImportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_download_returns_excel_file(): void
    {
        [$user] = $this->createContext();

        $response = $this->actingAs($user)->get(route('admin.bank.import.sample'));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=sample-import-bank-statement.xlsx');
    }

    public function test_preview_reads_transactions_without_inserting(): void
    {
        [$user] = $this->createContext();

        $file = UploadedFile::fake()->createWithContent('bank.csv', implode("\n", [
            'tarikh,description,debit,credit,balance',
            '01/01/2026,DERMA Program,0,150.00,1150.00',
            '02/01/2026,BAYAR BIL UTILITI,200.00,0,950.00',
        ]));

        $response = $this->actingAs($user)->post(route('admin.bank.import.preview'), [
            'excel_file' => $file,
        ]);

        $response->assertOk()
            ->assertSeeText('Pratonton Bank Statement')
            ->assertSeeText('DERMA Program')
            ->assertSeeText('BAYAR BIL UTILITI')
            ->assertSeeText('Cadangan')
            ->assertSeeText('Rekonsiliasi')
            ->assertSeeText('Pilihan')
            ->assertSeeText('Pilih Semua Hasil')
            ->assertSeeText('Pilih Semua Belanja')
            ->assertSeeText('Muat Turun Sampel Excel')
            ->assertSeeText('Papar Semua Baris')
            ->assertSeeText('Papar Isu Sahaja')
            ->assertSeeText('Papar Matched Sahaja')
            ->assertSeeText('Unmatched');

        $this->assertSame(0, Hasil::query()->count());
        $this->assertSame(0, Belanja::query()->count());
        $this->assertNotEmpty($response->viewData('previewToken'));
    }

    public function test_store_inserts_into_hasil_and_belanja_based_on_user_choice(): void
    {
        [$user] = $this->createContext();

        $file = UploadedFile::fake()->createWithContent('bank-override.csv', implode("\n", [
            'tarikh,description,debit,credit,balance',
            '01/01/2026,DERMA Program,0,150.00,1150.00',
            '02/01/2026,BAYAR BIL UTILITI,200.00,0,950.00',
            '03/01/2026,UNKNOWN,0,0,950.00',
        ]));

        $preview = $this->actingAs($user)->post(route('admin.bank.import.preview'), [
            'excel_file' => $file,
        ]);

        $preview->assertOk();
        $token = (string) $preview->viewData('previewToken');

        $store = $this->actingAs($user)->post(route('admin.bank.import.store'), [
            'preview_token' => $token,
            'choices' => [
                2 => 'hasil',
                3 => 'belanja',
                4 => 'abaikan',
            ],
        ]);

        $store->assertRedirect(route('admin.bank.import.index'));
        $store->assertSessionHas('status', fn ($value) => str_contains((string) $value, 'Import selesai.'));

        $this->assertSame(1, Hasil::query()->count());
        $this->assertSame(1, Belanja::query()->count());

        $hasil = Hasil::query()->first();
        $belanja = Belanja::query()->first();

        $this->assertNotNull($hasil);
        $this->assertNotNull($belanja);
        $this->assertSame('150.00', (string) $hasil->jumlah);
        $this->assertSame('200.00', (string) $belanja->amaun);
    }

    public function test_store_uses_auto_mapped_akaun_from_account_column(): void
    {
        [$user, , , $mappedAkaunId] = $this->createContext();

        $file = UploadedFile::fake()->createWithContent('bank-account-map.csv', implode("\n", [
            'tarikh,description,account,debit,credit,balance',
            '04/01/2026,DERMA Khas,Maybank 1234,0,99.90,1099.90',
            '05/01/2026,BAYAR SERVIS,Maybank 1234,55.50,0,1044.40',
        ]));

        $preview = $this->actingAs($user)->post(route('admin.bank.import.preview'), [
            'excel_file' => $file,
        ]);

        $preview->assertOk()->assertSeeText('Maybank 1234');
        $token = (string) $preview->viewData('previewToken');

        $store = $this->actingAs($user)->post(route('admin.bank.import.store'), [
            'preview_token' => $token,
            'choices' => [
                2 => 'hasil',
                3 => 'belanja',
            ],
        ]);

        $store->assertRedirect(route('admin.bank.import.index'));

        $hasil = Hasil::query()->latest('id')->first();
        $belanja = Belanja::query()->latest('id')->first();

        $this->assertNotNull($hasil);
        $this->assertNotNull($belanja);
        $this->assertSame($mappedAkaunId, (int) $hasil->id_akaun);
        $this->assertSame($mappedAkaunId, (int) $belanja->id_akaun);
    }

    public function test_duplicate_rows_in_same_file_are_detected_and_skipped(): void
    {
        [$user] = $this->createContext();

        $file = UploadedFile::fake()->createWithContent('bank-duplicate.csv', implode("\n", [
            'tarikh,description,debit,credit,balance',
            '06/01/2026,DERMA DUPLIKAT,0,88.00,1088.00',
            '06/01/2026,DERMA DUPLIKAT,0,88.00,1176.00',
        ]));

        $preview = $this->actingAs($user)->post(route('admin.bank.import.preview'), [
            'excel_file' => $file,
        ]);

        $preview->assertOk()->assertSeeText('Duplikasi dikesan');
        $token = (string) $preview->viewData('previewToken');

        $store = $this->actingAs($user)->post(route('admin.bank.import.store'), [
            'preview_token' => $token,
            'choices' => [
                2 => 'hasil',
                3 => 'hasil',
            ],
        ]);

        $store->assertRedirect(route('admin.bank.import.index'));
        $store->assertSessionHas('import_errors', function ($errors): bool {
            if (!is_array($errors)) {
                return false;
            }

            return collect($errors)->contains(fn ($line) => str_contains((string) $line, 'Duplikasi'));
        });

        $this->assertSame(1, Hasil::query()->count());
    }

    public function test_preview_marks_existing_record_as_matched_and_duplicate(): void
    {
        [$user, $masjid, $defaultAkaunId] = $this->createContext();

        $sumberHasil = SumberHasil::query()->withoutGlobalScopes()->where('id_masjid', $masjid->id)->first();
        $this->assertNotNull($sumberHasil);

        $existing = Hasil::query()->create([
            'id_masjid' => $masjid->id,
            'tarikh' => '2026-01-07',
            'no_resit' => 'TEST-REC-001',
            'id_akaun' => $defaultAkaunId,
            'id_sumber_hasil' => (int) $sumberHasil->id,
            'amaun_tunai' => 120.50,
            'amaun_online' => 0,
            'jumlah' => 120.50,
            'id_tabung_khas' => null,
            'id_program' => null,
            'jenis_jumaat' => null,
            'catatan' => 'DERMA PADANAN',
            'created_by' => $user->id,
        ]);

        $file = UploadedFile::fake()->createWithContent('bank-match.csv', implode("\n", [
            'tarikh,description,debit,credit,balance',
            '07/01/2026,DERMA PADANAN,0,120.50,1320.50',
            '08/01/2026,TRANSAKSI BARU,0,55.00,1375.50',
        ]));

        $preview = $this->actingAs($user)->post(route('admin.bank.import.preview'), [
            'excel_file' => $file,
        ]);

        $preview->assertOk()
            ->assertSeeText('Matched')
            ->assertSeeText('Hasil')
            ->assertSeeText('#' . $existing->id)
            ->assertSeeText('Duplikasi dikesan')
            ->assertSeeText('Unmatched');

        $token = (string) $preview->viewData('previewToken');

        $store = $this->actingAs($user)->post(route('admin.bank.import.store'), [
            'preview_token' => $token,
            'choices' => [
                2 => 'hasil',
                3 => 'hasil',
            ],
        ]);

        $store->assertRedirect(route('admin.bank.import.index'));
        $store->assertSessionHas('import_errors', function ($errors): bool {
            if (!is_array($errors)) {
                return false;
            }

            return collect($errors)->contains(fn ($line) => str_contains((string) $line, 'Duplikasi dikesan pada rekod sedia ada'));
        });

        $this->assertSame(2, Hasil::query()->count());
    }

    /**
     * @return array{0: User, 1: Masjid, 2: int, 3: int}
     */
    private function createContext(): array
    {
        $permissions = collect([
            'hasil.create',
            'belanja.create',
        ])->map(function (string $name) {
            return Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        });

        $role = Role::query()->firstOrCreate([
            'name' => 'FinanceOfficer',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($permissions);

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Bank Import',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addDays(30),
        ]);

        $user = User::query()->create([
            'name' => 'Bank Import User',
            'email' => 'bank.import.user@example.test',
            'password' => 'password',
            'id_masjid' => $masjid->id,
            'aktif' => true,
        ]);
        $user->assignRole($role);

        $defaultAkaun = Akaun::query()->create([
            'id_masjid' => $masjid->id,
            'nama_akaun' => 'Tunai Utama',
            'jenis' => 'tunai',
            'status_aktif' => true,
        ]);

        $mappedAkaun = Akaun::query()->create([
            'id_masjid' => $masjid->id,
            'nama_akaun' => 'Maybank 1234',
            'no_akaun' => '1234',
            'nama_bank' => 'Maybank',
            'jenis' => 'bank',
            'status_aktif' => true,
        ]);

        SumberHasil::query()->create([
            'id_masjid' => $masjid->id,
            'kod' => 'DERMA',
            'nama_sumber' => 'Derma Individu',
            'jenis' => 'Derma',
            'aktif' => true,
        ]);

        KategoriBelanja::query()->create([
            'id_masjid' => $masjid->id,
            'kod' => 'UTIL',
            'nama_kategori' => 'Utiliti',
            'aktif' => true,
        ]);

        TabungKhas::query()->create([
            'id_masjid' => $masjid->id,
            'nama_tabung' => 'Tabung Umum',
            'aktif' => true,
        ]);

        return [$user, $masjid, (int) $defaultAkaun->id, (int) $mappedAkaun->id];
    }
}
