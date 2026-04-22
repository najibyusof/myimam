<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\SumberHasil;
use App\Services\BankPdfLearningClassifierService;
use App\Services\BankPdfParsingService;
use App\Services\BelanjaManagementService;
use App\Services\HasilManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class BankPdfImportController extends Controller
{
    private const CACHE_TTL_MINUTES = 30;

    public function __construct(
        private readonly BankPdfParsingService $pdfParsingService,
        private readonly BankPdfLearningClassifierService $classifierService,
        private readonly HasilManagementService $hasilService,
        private readonly BelanjaManagementService $belanjaService,
    ) {}

    public function index(Request $request): View
    {
        $this->ensureImportAccess($request);

        return view('bank.pdf-import', [
            'masjidOptions' => $this->masjidOptions($request),
            'selectedMasjidId' => $this->selectedMasjidId($request),
            'previewRows' => [],
            'previewToken' => null,
            'fileName' => null,
            'totalRows' => 0,
            'validRows' => 0,
            'invalidRows' => 0,
        ]);
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $this->ensureImportAccess($request);

        $request->validate([
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'id_masjid' => ['nullable', 'integer', 'exists:masjid,id'],
        ]);

        $masjidId = $this->selectedMasjidId($request);
        if ($masjidId === null) {
            return back()->withErrors([
                'id_masjid' => 'Sila pilih masjid untuk import penyata bank PDF.',
            ])->withInput();
        }

        $defaults = $this->resolveDefaults($masjidId);
        if (!empty($defaults['errors'])) {
            return back()->withErrors($defaults['errors'])->withInput();
        }

        $file = $request->file('pdf_file');
        try {
            $rows = $this->pdfParsingService->parse((string) $file->getRealPath());
        } catch (Throwable $exception) {
            $message = strtolower($exception->getMessage());

            if (str_contains($message, 'secured pdf file') || str_contains($message, 'encrypted')) {
                return back()->withErrors([
                    'pdf_file' => 'Fail PDF dilindungi kata laluan (encrypted) tidak disokong. Sila eksport semula PDF tanpa kata laluan.',
                ])->withInput();
            }

            return back()->withErrors([
                'pdf_file' => 'Fail PDF tidak dapat diproses. Sila semak format penyata bank anda dan cuba semula.',
            ])->withInput();
        }

        if (count($rows) === 0) {
            return back()->withErrors([
                'pdf_file' => 'Tiada transaksi dikesan daripada fail PDF.',
            ])->withInput();
        }

        $akaunCatalog = Akaun::query()
            ->byMasjid($masjidId)
            ->aktif()
            ->orderBy('nama_akaun')
            ->get(['id', 'nama_akaun', 'no_akaun', 'nama_bank']);

        $kategoriCatalog = KategoriBelanja::query()
            ->byMasjid($masjidId)
            ->aktif()
            ->orderBy('nama_kategori')
            ->get(['id', 'nama_kategori']);

        $previewRows = collect($rows)
            ->map(function (array $row) use ($masjidId, $defaults, $akaunCatalog, $kategoriCatalog): array {
                $keterangan = (string) ($row['keterangan'] ?? '');
                $fallbackType = (string) ($row['type_auto'] ?? 'abaikan');

                $suggestion = $this->classifierService->suggest($masjidId, $keterangan, $fallbackType);
                $suggestedType = in_array((string) ($suggestion['type'] ?? ''), ['hasil', 'belanja', 'abaikan'], true)
                    ? (string) $suggestion['type']
                    : $fallbackType;

                [$autoAkaunId, $akaunSource] = $this->resolveAutoAkaunId(
                    $keterangan,
                    $suggestedType,
                    $defaults,
                    $akaunCatalog->all(),
                    isset($suggestion['akaun_id']) ? (int) $suggestion['akaun_id'] : null
                );

                $autoKategoriId = $suggestedType === 'belanja'
                    ? $this->resolveAutoKategoriBelanjaId(
                        $keterangan,
                        $defaults,
                        $kategoriCatalog->all(),
                        isset($suggestion['kategori_id']) ? (int) $suggestion['kategori_id'] : null
                    )
                    : null;

                $matchedRecord = $this->findExistingMatchRecord(
                    $masjidId,
                    (string) ($row['tarikh'] ?? ''),
                    (float) ($row['jumlah'] ?? 0),
                    $autoAkaunId
                );

                return [
                    'row_number' => (int) ($row['row_number'] ?? 0),
                    'data' => [
                        'tarikh' => $row['tarikh'] ?? null,
                        'keterangan' => $keterangan,
                        'amaun' => $row['jumlah'] ?? null,
                    ],
                    'suggested_type' => $suggestedType,
                    'valid' => (bool) ($row['valid'] ?? false),
                    'errors' => (array) ($row['errors'] ?? []),
                    'is_duplicate' => false,
                    'duplicate_source' => null,
                    'suggestion_source' => (string) ($suggestion['source'] ?? 'fallback'),
                    'akaun_source' => $akaunSource,
                    'reconciliation_status' => $matchedRecord !== null ? 'matched' : 'unmatched',
                    'matched_record' => $matchedRecord,
                    'auto_akaun_name' => $this->resolveAkaunNameById($akaunCatalog->all(), $autoAkaunId),
                    'auto_kategori_name' => $this->resolveKategoriNameById($kategoriCatalog->all(), $autoKategoriId),
                    'mapped' => [
                        'tarikh' => $row['tarikh'] ?? null,
                        'keterangan' => $keterangan,
                        'jumlah' => $row['jumlah'] ?? null,
                        'id_akaun' => $autoAkaunId,
                        'id_kategori_belanja' => $autoKategoriId,
                    ],
                ];
            })
            ->values()
            ->all();

        $previewRows = $this->markDuplicateRows($previewRows);
        $previewRows = $this->markDatabaseDuplicateRows($previewRows, $masjidId);

        $validRows = collect($previewRows)->where('valid', true)->count();
        $invalidRows = count($previewRows) - $validRows;

        $previewToken = (string) Str::uuid();
        Cache::put($this->cacheKey((int) $request->user()->id, $previewToken), [
            'id_masjid' => $masjidId,
            'file_name' => (string) $file->getClientOriginalName(),
            'defaults' => $defaults,
            'rows' => $previewRows,
        ], now()->addMinutes(self::CACHE_TTL_MINUTES));

        return view('bank.pdf-import', [
            'masjidOptions' => $this->masjidOptions($request),
            'selectedMasjidId' => $masjidId,
            'previewRows' => $previewRows,
            'previewToken' => $previewToken,
            'fileName' => (string) $file->getClientOriginalName(),
            'totalRows' => count($previewRows),
            'validRows' => $validRows,
            'invalidRows' => $invalidRows,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureImportAccess($request);

        $request->validate([
            'preview_token' => ['required', 'string'],
            'choices' => ['nullable', 'array'],
        ]);

        $cacheKey = $this->cacheKey((int) $request->user()->id, (string) $request->input('preview_token'));
        $payload = Cache::get($cacheKey);

        if (!is_array($payload) || empty($payload['rows'])) {
            return redirect()->route('admin.bank.pdf-import.index')->with('error', 'Sesi pratonton telah tamat. Sila muat naik semula fail PDF.');
        }

        $defaults = (array) ($payload['defaults'] ?? []);
        if (!empty($defaults['errors'])) {
            return redirect()->route('admin.bank.pdf-import.index')->with('error', 'Tetapan lalai tidak lengkap. Sila semak data master anda.');
        }

        $choices = (array) $request->input('choices', []);
        $jumlahHasil = 0;
        $jumlahBelanja = 0;
        $jumlahSkip = 0;
        $runtimeErrors = [];
        $processedFingerprints = [];

        foreach ((array) $payload['rows'] as $row) {
            $rowNumber = (int) ($row['row_number'] ?? 0);
            $choice = (string) ($choices[$rowNumber] ?? ($row['suggested_type'] ?? 'abaikan'));

            if (!in_array($choice, ['hasil', 'belanja', 'abaikan'], true)) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': Pilihan klasifikasi tidak sah.';
                $jumlahSkip++;
                continue;
            }

            if ($choice === 'abaikan') {
                $jumlahSkip++;
                continue;
            }

            $validationErrors = $this->validateStoreRow($row);
            if (!empty($validationErrors)) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': ' . implode('; ', $validationErrors);
                $jumlahSkip++;
                continue;
            }

            $mapped = (array) ($row['mapped'] ?? []);
            $fingerprint = $this->transactionFingerprint(
                (string) ($mapped['tarikh'] ?? ''),
                (float) ($mapped['jumlah'] ?? 0),
                $mapped['keterangan'] ?? null
            );

            $resolvedAkaunId = $this->resolveAkaunIdForChoice($mapped, $choice, $defaults);
            $resolvedKategoriBelanjaId = $this->resolveKategoriBelanjaIdForChoice($mapped, $defaults);

            if ($fingerprint === null) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': Data transaksi tidak lengkap untuk semakan duplikasi.';
                $jumlahSkip++;
                continue;
            }

            if ($resolvedAkaunId <= 0) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': Akaun tidak dapat dipadankan.';
                $jumlahSkip++;
                continue;
            }

            if ($choice === 'belanja' && $resolvedKategoriBelanjaId <= 0) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': Kategori belanja tidak dapat dipadankan.';
                $jumlahSkip++;
                continue;
            }

            if (isset($processedFingerprints[$fingerprint])) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': Duplikasi transaksi dikesan dalam fail import.';
                $jumlahSkip++;
                continue;
            }

            if ($this->existsDuplicateInDatabase(
                (int) $payload['id_masjid'],
                $choice,
                (string) ($mapped['tarikh'] ?? ''),
                (float) ($mapped['jumlah'] ?? 0),
                $mapped['keterangan'] ?? null,
                $resolvedAkaunId > 0 ? $resolvedAkaunId : null
            )) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': Duplikasi dikesan pada rekod sedia ada.';
                $jumlahSkip++;
                continue;
            }

            try {
                if ($choice === 'hasil') {
                    if (!Gate::allows('create', Hasil::class)) {
                        throw ValidationException::withMessages(['type' => 'Anda tidak mempunyai kebenaran untuk import transaksi hasil.']);
                    }

                    $this->hasilService->create($request->user(), [
                        'id_masjid' => (int) $payload['id_masjid'],
                        'tarikh' => (string) ($mapped['tarikh'] ?? ''),
                        'amaun' => (float) ($mapped['jumlah'] ?? 0),
                        'id_akaun' => $resolvedAkaunId,
                        'id_sumber_hasil' => (int) ($defaults['sumber_hasil_id'] ?? 0),
                        'id_tabung_khas' => null,
                        'catatan' => (string) ($mapped['keterangan'] ?? ''),
                        'is_jumaat' => false,
                    ]);

                    $this->classifierService->learnFromConfirmedImport(
                        (int) $payload['id_masjid'],
                        (string) ($mapped['keterangan'] ?? ''),
                        'hasil',
                        $resolvedAkaunId,
                        null,
                        $choice !== (string) ($row['suggested_type'] ?? 'abaikan')
                    );

                    $processedFingerprints[$fingerprint] = true;
                    $jumlahHasil++;
                    continue;
                }

                if (!Gate::allows('create', Belanja::class)) {
                    throw ValidationException::withMessages(['type' => 'Anda tidak mempunyai kebenaran untuk import transaksi belanja.']);
                }

                $this->belanjaService->create($request->user(), [
                    'id_masjid' => (int) $payload['id_masjid'],
                    'tarikh' => (string) ($mapped['tarikh'] ?? ''),
                    'amaun' => (float) ($mapped['jumlah'] ?? 0),
                    'id_akaun' => $resolvedAkaunId,
                    'id_kategori_belanja' => $resolvedKategoriBelanjaId,
                    'submit_action' => 'submitted',
                    'penerima' => null,
                    'catatan' => (string) ($mapped['keterangan'] ?? ''),
                ]);

                $this->classifierService->learnFromConfirmedImport(
                    (int) $payload['id_masjid'],
                    (string) ($mapped['keterangan'] ?? ''),
                    'belanja',
                    $resolvedAkaunId,
                    $resolvedKategoriBelanjaId,
                    $choice !== (string) ($row['suggested_type'] ?? 'abaikan')
                );

                $processedFingerprints[$fingerprint] = true;
                $jumlahBelanja++;
            } catch (ValidationException $exception) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': ' . collect($exception->errors())->flatten()->implode('; ');
                $jumlahSkip++;
            } catch (Throwable $exception) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': ' . $exception->getMessage();
                $jumlahSkip++;
            }
        }

        Cache::forget($cacheKey);

        return redirect()
            ->route('admin.bank.pdf-import.index')
            ->with('status', 'Import selesai. Jumlah hasil: ' . $jumlahHasil . ', jumlah belanja: ' . $jumlahBelanja . ', jumlah skip: ' . $jumlahSkip . '.')
            ->with('import_errors', $runtimeErrors);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveDefaults(int $masjidId): array
    {
        $akaunId = Akaun::query()->byMasjid($masjidId)->aktif()->orderBy('id')->value('id');
        $sumberHasilId = SumberHasil::query()->byMasjid($masjidId)->aktif()->orderBy('id')->value('id');
        $kategoriBelanjaId = KategoriBelanja::query()->byMasjid($masjidId)->aktif()->orderBy('id')->value('id');

        $errors = [];
        if (!$akaunId) {
            $errors['defaults_akaun'] = 'Tiada akaun aktif ditemui untuk masjid ini.';
        }

        if (!$sumberHasilId) {
            $errors['defaults_sumber_hasil'] = 'Tiada sumber hasil aktif ditemui untuk masjid ini.';
        }

        if (!$kategoriBelanjaId) {
            $errors['defaults_kategori_belanja'] = 'Tiada kategori belanja aktif ditemui untuk masjid ini.';
        }

        return [
            'akaun_hasil_id' => (int) $akaunId,
            'akaun_belanja_id' => (int) $akaunId,
            'sumber_hasil_id' => (int) $sumberHasilId,
            'kategori_belanja_id' => (int) $kategoriBelanjaId,
            'errors' => $errors,
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<int, string>
     */
    private function validateStoreRow(array $row): array
    {
        $errors = [];

        if (!((bool) ($row['valid'] ?? false))) {
            $errors = array_merge($errors, (array) ($row['errors'] ?? []));
        }

        if ((bool) ($row['is_duplicate'] ?? false)) {
            $duplicateSource = (string) ($row['duplicate_source'] ?? 'file');
            if ($duplicateSource === 'database') {
                $errors[] = 'Duplikasi dikesan pada rekod sedia ada.';
            } else {
                $errors[] = 'Duplikasi transaksi dikesan dalam fail import.';
            }
        }

        $mapped = (array) ($row['mapped'] ?? []);

        if (empty($mapped['tarikh']) || strtotime((string) $mapped['tarikh']) === false) {
            $errors[] = 'Tarikh tidak sah.';
        }

        if (empty($mapped['keterangan']) || trim((string) $mapped['keterangan']) === '') {
            $errors[] = 'Keterangan wajib diisi.';
        }

        if (!isset($mapped['jumlah']) || (float) $mapped['jumlah'] <= 0) {
            $errors[] = 'Amaun mesti lebih besar daripada 0.';
        }

        return array_values(array_unique($errors));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function markDuplicateRows(array $rows): array
    {
        $seen = [];

        foreach ($rows as $index => $row) {
            $mapped = (array) ($row['mapped'] ?? []);
            $fingerprint = $this->transactionFingerprint(
                (string) ($mapped['tarikh'] ?? ''),
                (float) ($mapped['jumlah'] ?? 0),
                $mapped['keterangan'] ?? null
            );

            if ($fingerprint === null) {
                continue;
            }

            if (!isset($seen[$fingerprint])) {
                $seen[$fingerprint] = (int) ($row['row_number'] ?? ($index + 1));
                continue;
            }

            $sourceRow = $seen[$fingerprint];
            $rows[$index]['is_duplicate'] = true;
            $rows[$index]['duplicate_source'] = 'file';
            $rows[$index]['valid'] = false;
            $rows[$index]['errors'] = array_values(array_unique(array_merge(
                (array) ($rows[$index]['errors'] ?? []),
                ['Duplikasi transaksi dikesan (sama seperti baris ' . $sourceRow . ').']
            )));
        }

        return $rows;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function markDatabaseDuplicateRows(array $rows, int $masjidId): array
    {
        foreach ($rows as $index => $row) {
            $mapped = (array) ($row['mapped'] ?? []);
            $type = (string) ($row['suggested_type'] ?? 'abaikan');

            if (!in_array($type, ['hasil', 'belanja'], true)) {
                continue;
            }

            $akaunId = isset($mapped['id_akaun']) ? (int) $mapped['id_akaun'] : null;
            if (!$this->existsDuplicateInDatabase(
                $masjidId,
                $type,
                (string) ($mapped['tarikh'] ?? ''),
                (float) ($mapped['jumlah'] ?? 0),
                $mapped['keterangan'] ?? null,
                ($akaunId !== null && $akaunId > 0) ? $akaunId : null
            )) {
                continue;
            }

            $rows[$index]['is_duplicate'] = true;
            if (empty($rows[$index]['duplicate_source'])) {
                $rows[$index]['duplicate_source'] = 'database';
            }
            $rows[$index]['valid'] = false;
            $rows[$index]['errors'] = array_values(array_unique(array_merge(
                (array) ($rows[$index]['errors'] ?? []),
                ['Duplikasi dikesan pada rekod sedia ada.']
            )));
        }

        return $rows;
    }

    private function resolveAkaunIdForChoice(array $mapped, string $choice, array $defaults): int
    {
        $mappedAkaunId = (int) ($mapped['id_akaun'] ?? 0);
        if ($mappedAkaunId > 0) {
            return $mappedAkaunId;
        }

        if ($choice === 'hasil') {
            return (int) ($defaults['akaun_hasil_id'] ?? 0);
        }

        return (int) ($defaults['akaun_belanja_id'] ?? 0);
    }

    private function resolveKategoriBelanjaIdForChoice(array $mapped, array $defaults): int
    {
        $mappedKategoriId = (int) ($mapped['id_kategori_belanja'] ?? 0);
        if ($mappedKategoriId > 0) {
            return $mappedKategoriId;
        }

        return (int) ($defaults['kategori_belanja_id'] ?? 0);
    }

    private function existsDuplicateInDatabase(
        int $masjidId,
        string $type,
        string $tarikh,
        float $jumlah,
        ?string $keterangan,
        ?int $akaunId
    ): bool {
        if ($tarikh === '' || $jumlah <= 0) {
            return false;
        }

        $description = $this->normalizeDescription($keterangan);

        if ($type === 'hasil') {
            $query = Hasil::query()
                ->where('id_masjid', $masjidId)
                ->whereDate('tarikh', $tarikh)
                ->where('jumlah', $jumlah);

            if ($akaunId !== null) {
                $query->where('id_akaun', $akaunId);
            }

            return $this->applyDescriptionMatch($query, 'catatan', $description)->exists();
        }

        $query = Belanja::query()
            ->where('id_masjid', $masjidId)
            ->whereDate('tarikh', $tarikh)
            ->where('amaun', $jumlah);

        if ($akaunId !== null) {
            $query->where('id_akaun', $akaunId);
        }

        return $this->applyDescriptionMatch($query, 'catatan', $description)->exists();
    }

    private function applyDescriptionMatch($query, string $column, string $description)
    {
        if ($description === '') {
            return $query->where(function ($inner) use ($column) {
                $inner->whereNull($column)->orWhere($column, '');
            });
        }

        return $query->whereRaw('LOWER(TRIM(COALESCE(' . $column . ", ''))) = ?", [$description]);
    }

    private function normalizeDescription(?string $value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->trim()
            ->replaceMatches('/\s+/', ' ')
            ->toString();
    }

    private function transactionFingerprint(string $tarikh, float $jumlah, ?string $keterangan): ?string
    {
        $tarikh = trim($tarikh);
        $keterangan = trim((string) $keterangan);

        if ($tarikh === '' || $keterangan === '' || $jumlah <= 0) {
            return null;
        }

        $normalizedDescription = Str::of($keterangan)
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        return $tarikh . '|' . number_format($jumlah, 2, '.', '') . '|' . $normalizedDescription;
    }

    /**
     * @param array<int, mixed> $akaunCatalog
     * @return array{0: ?int, 1: string}
     */
    private function resolveAutoAkaunId(
        string $description,
        string $suggestedType,
        array $defaults,
        array $akaunCatalog,
        ?int $learnedAkaunId
    ): array {
        if ($learnedAkaunId !== null && $learnedAkaunId > 0) {
            return [$learnedAkaunId, 'learned'];
        }

        $historyAkaunId = $this->findHistoricalAkaunIdByDescription($description, $suggestedType);
        if ($historyAkaunId !== null && $historyAkaunId > 0) {
            return [$historyAkaunId, 'historical'];
        }

        $byKeyword = $this->matchAkaunFromDescription($description, $akaunCatalog);
        if ($byKeyword !== null) {
            return [$byKeyword, 'keyword'];
        }

        if ($suggestedType === 'hasil') {
            return [(int) ($defaults['akaun_hasil_id'] ?? 0), 'default'];
        }

        if ($suggestedType === 'belanja') {
            return [(int) ($defaults['akaun_belanja_id'] ?? 0), 'default'];
        }

        return [null, 'default'];
    }

    /**
     * @param array<int, mixed> $kategoriCatalog
     */
    private function resolveAutoKategoriBelanjaId(
        string $description,
        array $defaults,
        array $kategoriCatalog,
        ?int $learnedKategoriId
    ): ?int {
        if ($learnedKategoriId !== null && $learnedKategoriId > 0) {
            return $learnedKategoriId;
        }

        $historyKategoriId = $this->findHistoricalKategoriIdByDescription($description);
        if ($historyKategoriId !== null && $historyKategoriId > 0) {
            return $historyKategoriId;
        }

        $normalized = Str::upper($description);
        foreach ($kategoriCatalog as $kategori) {
            $nama = Str::upper((string) ($kategori->nama_kategori ?? ''));
            if ($nama !== '' && str_contains($normalized, $nama)) {
                return (int) $kategori->id;
            }
        }

        return (int) ($defaults['kategori_belanja_id'] ?? 0);
    }

    /**
     * @param array<int, mixed> $akaunCatalog
     */
    private function matchAkaunFromDescription(string $description, array $akaunCatalog): ?int
    {
        $normalized = Str::upper($description);

        foreach ($akaunCatalog as $akaun) {
            $candidates = [
                (string) ($akaun->nama_akaun ?? ''),
                (string) ($akaun->no_akaun ?? ''),
                (string) ($akaun->nama_bank ?? ''),
            ];

            foreach ($candidates as $candidate) {
                $needle = Str::upper(trim($candidate));
                if ($needle === '') {
                    continue;
                }

                if (str_contains($normalized, $needle)) {
                    return (int) $akaun->id;
                }
            }
        }

        return null;
    }

    private function findHistoricalAkaunIdByDescription(string $description, string $type): ?int
    {
        $normalized = $this->normalizeDescription($description);
        if ($normalized === '') {
            return null;
        }

        if ($type === 'hasil') {
            $record = Hasil::query()
                ->whereRaw("LOWER(TRIM(COALESCE(catatan, ''))) = ?", [$normalized])
                ->latest('id')
                ->first(['id_akaun']);

            return $record ? (int) $record->id_akaun : null;
        }

        if ($type === 'belanja') {
            $record = Belanja::query()
                ->whereRaw("LOWER(TRIM(COALESCE(catatan, ''))) = ?", [$normalized])
                ->latest('id')
                ->first(['id_akaun']);

            return $record ? (int) $record->id_akaun : null;
        }

        return null;
    }

    private function findHistoricalKategoriIdByDescription(string $description): ?int
    {
        $normalized = $this->normalizeDescription($description);
        if ($normalized === '') {
            return null;
        }

        $record = Belanja::query()
            ->whereRaw("LOWER(TRIM(COALESCE(catatan, ''))) = ?", [$normalized])
            ->latest('id')
            ->first(['id_kategori_belanja']);

        return $record ? (int) $record->id_kategori_belanja : null;
    }

    /**
     * @param array<int, mixed> $akaunCatalog
     */
    private function resolveAkaunNameById(array $akaunCatalog, ?int $akaunId): ?string
    {
        if ($akaunId === null || $akaunId <= 0) {
            return null;
        }

        foreach ($akaunCatalog as $akaun) {
            if ((int) $akaun->id === $akaunId) {
                return (string) $akaun->nama_akaun;
            }
        }

        return null;
    }

    /**
     * @param array<int, mixed> $kategoriCatalog
     */
    private function resolveKategoriNameById(array $kategoriCatalog, ?int $kategoriId): ?string
    {
        if ($kategoriId === null || $kategoriId <= 0) {
            return null;
        }

        foreach ($kategoriCatalog as $kategori) {
            if ((int) $kategori->id === $kategoriId) {
                return (string) $kategori->nama_kategori;
            }
        }

        return null;
    }

    /**
     * @return array{type: string, id: int}|null
     */
    private function findExistingMatchRecord(int $masjidId, string $tarikh, float $jumlah, ?int $akaunId): ?array
    {
        if ($tarikh === '' || $jumlah <= 0) {
            return null;
        }

        $hasilQuery = Hasil::query()
            ->where('id_masjid', $masjidId)
            ->whereDate('tarikh', $tarikh)
            ->where('jumlah', $jumlah)
            ->orderByDesc('id');

        if ($akaunId !== null && $akaunId > 0) {
            $hasilQuery->where('id_akaun', $akaunId);
        }

        $hasil = $hasilQuery->first(['id']);
        if ($hasil) {
            return [
                'type' => 'hasil',
                'id' => (int) $hasil->id,
            ];
        }

        $belanjaQuery = Belanja::query()
            ->where('id_masjid', $masjidId)
            ->whereDate('tarikh', $tarikh)
            ->where('amaun', $jumlah)
            ->orderByDesc('id');

        if ($akaunId !== null && $akaunId > 0) {
            $belanjaQuery->where('id_akaun', $akaunId);
        }

        $belanja = $belanjaQuery->first(['id']);
        if ($belanja) {
            return [
                'type' => 'belanja',
                'id' => (int) $belanja->id,
            ];
        }

        return null;
    }

    private function selectedMasjidId(Request $request): ?int
    {
        $actor = $request->user();

        if ($actor->peranan === 'superadmin') {
            $id = (int) $request->input('id_masjid', 0);
            return $id > 0 ? $id : null;
        }

        return $actor->id_masjid ? (int) $actor->id_masjid : null;
    }

    private function masjidOptions(Request $request)
    {
        if ($request->user()->peranan !== 'superadmin') {
            return collect();
        }

        return Masjid::query()->orderBy('nama')->get(['id', 'nama']);
    }

    private function cacheKey(int $userId, string $token): string
    {
        return 'bank_pdf_import_preview:' . $userId . ':' . $token;
    }

    private function ensureImportAccess(Request $request): void
    {
        if (!Gate::allows('create', Hasil::class) && !Gate::allows('create', Belanja::class)) {
            abort(403);
        }
    }
}
