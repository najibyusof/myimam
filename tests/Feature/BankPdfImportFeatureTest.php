<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\SumberHasil;
use App\Models\User;
use App\Services\BankPdfParsingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Exception;

class BankPdfImportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_reads_pdf_rows_without_inserting_records(): void
    {
        [$user] = $this->createContext();

        $this->app->instance(BankPdfParsingService::class, new class extends BankPdfParsingService {
            public function parse(string $filePath): array
            {
                return [
                    [
                        'row_number' => 1,
                        'tarikh' => '2026-03-02',
                        'keterangan' => 'IBK FUND TFR TO A/C',
                        'jumlah' => 500.00,
                        'type_auto' => 'hasil',
                        'valid' => true,
                        'errors' => [],
                    ],
                    [
                        'row_number' => 2,
                        'tarikh' => null,
                        'keterangan' => '',
                        'jumlah' => 0,
                        'type_auto' => 'belanja',
                        'valid' => false,
                        'errors' => ['Tarikh tidak sah.', 'Keterangan wajib diisi.', 'Amaun mesti lebih besar daripada 0.'],
                    ],
                ];
            }
        });

        $file = UploadedFile::fake()->create('statement.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->post(route('admin.bank.pdf-import.preview'), [
            'pdf_file' => $file,
        ]);

        $response->assertOk()
            ->assertSeeText('Pratonton Import PDF')
            ->assertSeeText('IBK FUND TFR TO A/C')
            ->assertSeeText('Jumlah baris dikesan:')
            ->assertSeeText('Valid:')
            ->assertSeeText('Invalid:')
            ->assertSeeText('Cadangan')
            ->assertSeeText('Pilihan')
            ->assertSeeText('Status');

        $this->assertSame(0, Hasil::query()->count());
        $this->assertSame(0, Belanja::query()->count());
        $this->assertNotEmpty($response->viewData('previewToken'));

        $rows = (array) $response->viewData('previewRows');
        // Row 1: 'IBK FUND TFR TO A/C' matches the builtin 'FUND TFR TO' pattern
        $this->assertSame('builtin', (string) ($rows[0]['suggestion_source'] ?? ''));
        // Row 2: invalid row with no description — falls back to default
        $this->assertSame('fallback', (string) ($rows[1]['suggestion_source'] ?? ''));
    }

    public function test_store_inserts_hasil_and_belanja_based_on_user_override(): void
    {
        [$user] = $this->createContext();

        $this->app->instance(BankPdfParsingService::class, new class extends BankPdfParsingService {
            public function parse(string $filePath): array
            {
                return [
                    [
                        'row_number' => 1,
                        'tarikh' => '2026-03-02',
                        'keterangan' => 'IBK FUND TFR TO A/C',
                        'jumlah' => 500.00,
                        'type_auto' => 'hasil',
                        'valid' => true,
                        'errors' => [],
                    ],
                    [
                        'row_number' => 2,
                        'tarikh' => '2026-03-03',
                        'keterangan' => 'BILL PAYMENT TNB',
                        'jumlah' => 120.00,
                        'type_auto' => 'belanja',
                        'valid' => true,
                        'errors' => [],
                    ],
                    [
                        'row_number' => 3,
                        'tarikh' => '2026-03-04',
                        'keterangan' => 'TRANSFER NOTE',
                        'jumlah' => 10.00,
                        'type_auto' => 'hasil',
                        'valid' => true,
                        'errors' => [],
                    ],
                ];
            }
        });

        $file = UploadedFile::fake()->create('statement-2.pdf', 100, 'application/pdf');

        $preview = $this->actingAs($user)->post(route('admin.bank.pdf-import.preview'), [
            'pdf_file' => $file,
        ]);

        $preview->assertOk();
        $token = (string) $preview->viewData('previewToken');

        $store = $this->actingAs($user)->post(route('admin.bank.pdf-import.store'), [
            'preview_token' => $token,
            'choices' => [
                1 => 'hasil',
                2 => 'belanja',
                3 => 'abaikan',
            ],
        ]);

        $store->assertRedirect(route('admin.bank.pdf-import.index'));
        $store->assertSessionHas('status', fn ($value) => str_contains((string) $value, 'Import selesai.'));

        $this->assertSame(1, Hasil::query()->count());
        $this->assertSame(1, Belanja::query()->count());

        $hasil = Hasil::query()->first();
        $belanja = Belanja::query()->first();

        $this->assertNotNull($hasil);
        $this->assertNotNull($belanja);
        $this->assertSame('500.00', (string) $hasil->jumlah);
        $this->assertSame('120.00', (string) $belanja->amaun);
    }

    public function test_store_skips_invalid_rows_and_collects_error_messages(): void
    {
        [$user] = $this->createContext();

        $this->app->instance(BankPdfParsingService::class, new class extends BankPdfParsingService {
            public function parse(string $filePath): array
            {
                return [
                    [
                        'row_number' => 1,
                        'tarikh' => null,
                        'keterangan' => '',
                        'jumlah' => 0,
                        'type_auto' => 'hasil',
                        'valid' => false,
                        'errors' => ['Tarikh tidak sah.', 'Keterangan wajib diisi.', 'Amaun mesti lebih besar daripada 0.'],
                    ],
                ];
            }
        });

        $file = UploadedFile::fake()->create('statement-3.pdf', 100, 'application/pdf');

        $preview = $this->actingAs($user)->post(route('admin.bank.pdf-import.preview'), [
            'pdf_file' => $file,
        ]);

        $preview->assertOk();
        $token = (string) $preview->viewData('previewToken');

        $store = $this->actingAs($user)->post(route('admin.bank.pdf-import.store'), [
            'preview_token' => $token,
            'choices' => [
                1 => 'hasil',
            ],
        ]);

        $store->assertRedirect(route('admin.bank.pdf-import.index'));
        $store->assertSessionHas('import_errors', function ($errors): bool {
            if (!is_array($errors)) {
                return false;
            }

            return collect($errors)->contains(fn ($line) => str_contains((string) $line, 'Tarikh tidak sah'));
        });

        $this->assertSame(0, Hasil::query()->count());
        $this->assertSame(0, Belanja::query()->count());
    }

    public function test_preview_returns_validation_error_for_encrypted_pdf(): void
    {
        [$user] = $this->createContext();

        $this->app->instance(BankPdfParsingService::class, new class extends BankPdfParsingService {
            public function parse(string $filePath): array
            {
                throw new Exception('Secured pdf file are currently not supported.');
            }
        });

        $file = UploadedFile::fake()->create('secured.pdf', 100, 'application/pdf');

        $response = $this->from(route('admin.bank.pdf-import.index'))
            ->actingAs($user)
            ->post(route('admin.bank.pdf-import.preview'), [
                'pdf_file' => $file,
            ]);

        $response->assertRedirect(route('admin.bank.pdf-import.index'));
        $response->assertSessionHasErrors([
            'pdf_file' => 'Fail PDF dilindungi kata laluan (encrypted) tidak disokong. Sila eksport semula PDF tanpa kata laluan.',
        ]);

        $this->assertSame(0, Hasil::query()->count());
        $this->assertSame(0, Belanja::query()->count());
    }

    public function test_preview_marks_duplicate_transactions_and_store_skips_duplicate_rows(): void
    {
        [$user] = $this->createContext();

        $this->app->instance(BankPdfParsingService::class, new class extends BankPdfParsingService {
            public function parse(string $filePath): array
            {
                return [
                    [
                        'row_number' => 1,
                        'tarikh' => '2026-03-02',
                        'keterangan' => 'IBK FUND TFR TO A/C',
                        'jumlah' => 500.00,
                        'type_auto' => 'hasil',
                        'valid' => true,
                        'errors' => [],
                    ],
                    [
                        'row_number' => 2,
                        'tarikh' => '2026-03-02',
                        'keterangan' => 'IBK FUND TFR TO A/C',
                        'jumlah' => 500.00,
                        'type_auto' => 'hasil',
                        'valid' => true,
                        'errors' => [],
                    ],
                ];
            }
        });

        $file = UploadedFile::fake()->create('duplicate.pdf', 100, 'application/pdf');

        $preview = $this->actingAs($user)->post(route('admin.bank.pdf-import.preview'), [
            'pdf_file' => $file,
        ]);

        $preview->assertOk()
            ->assertSeeText('Duplikasi')
            ->assertSeeText('sama seperti baris');

        $token = (string) $preview->viewData('previewToken');

        $store = $this->actingAs($user)->post(route('admin.bank.pdf-import.store'), [
            'preview_token' => $token,
            'choices' => [
                1 => 'hasil',
                2 => 'hasil',
            ],
        ]);

        $store->assertRedirect(route('admin.bank.pdf-import.index'));
        $store->assertSessionHas('import_errors', function ($errors): bool {
            if (!is_array($errors)) {
                return false;
            }

            return collect($errors)->contains(fn ($line) => str_contains((string) $line, 'Duplikasi transaksi dikesan'));
        });

        $this->assertSame(1, Hasil::query()->count());
    }

    public function test_preview_marks_existing_database_record_as_duplicate_and_store_skips_it(): void
    {
        [$user, $masjid] = $this->createContext();

        $akaunId = (int) Akaun::query()->withoutGlobalScopes()->where('id_masjid', $masjid->id)->value('id');
        $sumberHasilId = (int) SumberHasil::query()->withoutGlobalScopes()->where('id_masjid', $masjid->id)->value('id');

        Hasil::query()->create([
            'id_masjid' => $masjid->id,
            'tarikh' => '2026-03-02',
            'no_resit' => 'PDF-DUP-001',
            'id_akaun' => $akaunId,
            'id_sumber_hasil' => $sumberHasilId,
            'amaun_tunai' => 500.00,
            'amaun_online' => 0,
            'jumlah' => 500.00,
            'id_tabung_khas' => null,
            'id_program' => null,
            'jenis_jumaat' => null,
            'catatan' => 'IBK FUND TFR TO A/C',
            'created_by' => $user->id,
        ]);

        $this->app->instance(BankPdfParsingService::class, new class extends BankPdfParsingService {
            public function parse(string $filePath): array
            {
                return [
                    [
                        'row_number' => 1,
                        'tarikh' => '2026-03-02',
                        'keterangan' => 'IBK FUND TFR TO A/C',
                        'jumlah' => 500.00,
                        'type_auto' => 'hasil',
                        'valid' => true,
                        'errors' => [],
                    ],
                ];
            }
        });

        $file = UploadedFile::fake()->create('db-duplicate.pdf', 100, 'application/pdf');

        $preview = $this->actingAs($user)->post(route('admin.bank.pdf-import.preview'), [
            'pdf_file' => $file,
        ]);

        $preview->assertOk()->assertSeeText('Duplikasi dikesan pada rekod sedia ada');
        $token = (string) $preview->viewData('previewToken');

        $store = $this->actingAs($user)->post(route('admin.bank.pdf-import.store'), [
            'preview_token' => $token,
            'choices' => [
                1 => 'hasil',
            ],
        ]);

        $store->assertRedirect(route('admin.bank.pdf-import.index'));
        $store->assertSessionHas('import_errors', function ($errors): bool {
            if (!is_array($errors)) {
                return false;
            }

            return collect($errors)->contains(fn ($line) => str_contains((string) $line, 'Duplikasi dikesan pada rekod sedia ada'));
        });

        $this->assertSame(1, Hasil::query()->count());
    }

    public function test_preview_shows_reconciliation_matched_and_unmatched(): void
    {
        [$user, $masjid] = $this->createContext();

        $akaunId = (int) Akaun::query()->withoutGlobalScopes()->where('id_masjid', $masjid->id)->value('id');
        $sumberHasilId = (int) SumberHasil::query()->withoutGlobalScopes()->where('id_masjid', $masjid->id)->value('id');

        Hasil::query()->create([
            'id_masjid' => $masjid->id,
            'tarikh' => '2026-03-10',
            'no_resit' => 'PDF-MATCH-001',
            'id_akaun' => $akaunId,
            'id_sumber_hasil' => $sumberHasilId,
            'amaun_tunai' => 300.00,
            'amaun_online' => 0,
            'jumlah' => 300.00,
            'id_tabung_khas' => null,
            'id_program' => null,
            'jenis_jumaat' => null,
            'catatan' => 'MATCH ROW',
            'created_by' => $user->id,
        ]);

        $this->app->instance(BankPdfParsingService::class, new class extends BankPdfParsingService {
            public function parse(string $filePath): array
            {
                return [
                    [
                        'row_number' => 1,
                        'tarikh' => '2026-03-10',
                        'keterangan' => 'MATCH ROW',
                        'jumlah' => 300.00,
                        'type_auto' => 'hasil',
                        'valid' => true,
                        'errors' => [],
                    ],
                    [
                        'row_number' => 2,
                        'tarikh' => '2026-03-11',
                        'keterangan' => 'NEW ROW',
                        'jumlah' => 110.00,
                        'type_auto' => 'hasil',
                        'valid' => true,
                        'errors' => [],
                    ],
                ];
            }
        });

        $file = UploadedFile::fake()->create('reconcile.pdf', 100, 'application/pdf');

        $preview = $this->actingAs($user)->post(route('admin.bank.pdf-import.preview'), [
            'pdf_file' => $file,
        ]);

        $preview->assertOk()
            ->assertSeeText('Matched')
            ->assertSeeText('Unmatched');

        $rows = (array) $preview->viewData('previewRows');
        $this->assertSame('matched', (string) ($rows[0]['reconciliation_status'] ?? ''));
        $this->assertSame('unmatched', (string) ($rows[1]['reconciliation_status'] ?? ''));
    }

    public function test_classifier_learns_from_user_override_and_updates_next_preview(): void
    {
        [$user] = $this->createContext();

        $this->app['cache']->flush();

        $this->app->instance(BankPdfParsingService::class, new class extends BankPdfParsingService {
            public function parse(string $filePath): array
            {
                return [
                    [
                        'row_number' => 1,
                        'tarikh' => '2026-03-12',
                        'keterangan' => 'ZAKAT FITRAH ASNAF 2026',
                        'jumlah' => 95.00,
                        'type_auto' => 'hasil',
                        'valid' => true,
                        'errors' => [],
                    ],
                ];
            }
        });

        $file = UploadedFile::fake()->create('learn-1.pdf', 100, 'application/pdf');

        $preview1 = $this->actingAs($user)->post(route('admin.bank.pdf-import.preview'), [
            'pdf_file' => $file,
        ]);

        $preview1->assertOk();
        $token1 = (string) $preview1->viewData('previewToken');

        $store1 = $this->actingAs($user)->post(route('admin.bank.pdf-import.store'), [
            'preview_token' => $token1,
            'choices' => [
                1 => 'belanja',
            ],
        ]);

        $store1->assertRedirect(route('admin.bank.pdf-import.index'));

        $file2 = UploadedFile::fake()->create('learn-2.pdf', 100, 'application/pdf');
        $preview2 = $this->actingAs($user)->post(route('admin.bank.pdf-import.preview'), [
            'pdf_file' => $file2,
        ]);

        $preview2->assertOk();
        $rows = (array) $preview2->viewData('previewRows');

        $this->assertSame('belanja', (string) ($rows[0]['suggested_type'] ?? ''));
        $this->assertSame('learned', (string) ($rows[0]['suggestion_source'] ?? ''));
        $this->assertNotEmpty($rows[0]['auto_akaun_name'] ?? null);
        $this->assertNotEmpty($rows[0]['auto_kategori_name'] ?? null);
    }

    /**
     * @return array{0: User, 1: Masjid}
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
            'nama' => 'Masjid Bank PDF Import',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addDays(30),
        ]);

        $user = User::query()->create([
            'name' => 'Bank PDF Import User',
            'email' => 'bank.pdf.import.user@example.test',
            'password' => 'password',
            'id_masjid' => $masjid->id,
            'aktif' => true,
        ]);
        $user->assignRole($role);

        Akaun::query()->create([
            'id_masjid' => $masjid->id,
            'nama_akaun' => 'Tunai Utama',
            'jenis' => 'tunai',
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

        return [$user, $masjid];
    }
}
