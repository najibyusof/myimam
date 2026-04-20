<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BankImportSampleExport;
use App\Http\Controllers\Controller;
use App\Imports\BankStatementPreviewImport;
use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\SumberHasil;
use App\Services\BelanjaManagementService;
use App\Services\HasilManagementService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class BankImportController extends Controller
{
    private const CACHE_TTL_MINUTES = 30;

    public function __construct(
        private readonly HasilManagementService $hasilService,
        private readonly BelanjaManagementService $belanjaService
    ) {}

    public function index(Request $request): View
    {
        $this->ensureImportAccess($request);

        return view('bank.import', [
            'masjidOptions' => $this->masjidOptions($request),
            'selectedMasjidId' => $this->selectedMasjidId($request),
            'previewRows' => [],
            'previewToken' => null,
            'fileName' => null,
            'totalRows' => 0,
            'validRows' => 0,
            'invalidRows' => 0,
            'matchedRows' => 0,
            'unmatchedRows' => 0,
        ]);
    }

    public function sample(Request $request)
    {
        $this->ensureImportAccess($request);

        return Excel::download(new BankImportSampleExport(), 'sample-import-bank-statement.xlsx');
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $this->ensureImportAccess($request);

        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'id_masjid' => ['nullable', 'integer', 'exists:masjid,id'],
        ]);

        $masjidId = $this->selectedMasjidId($request);
        if ($masjidId === null) {
            return back()->withErrors([
                'id_masjid' => 'Sila pilih masjid untuk import bank statement.',
            ])->withInput();
        }

        $defaults = $this->resolveDefaults($masjidId);
        if (!empty($defaults['errors'])) {
            return back()->withErrors($defaults['errors'])->withInput();
        }

        $file = $request->file('excel_file');
        $import = new BankStatementPreviewImport();
        Excel::import($import, $file);

        $rows = $import->rows();
        if (count($rows) === 0) {
            return back()->withErrors([
                'excel_file' => 'Fail bank statement tidak mempunyai data untuk dipratonton.',
            ])->withInput();
        }

        $previewRows = $this->buildPreviewRows($rows, $masjidId);
        $validRows = collect($previewRows)->where('valid', true)->count();
        $invalidRows = count($previewRows) - $validRows;
        $matchedRows = collect($previewRows)->where('reconciliation_status', 'matched')->count();
        $unmatchedRows = count($previewRows) - $matchedRows;

        $previewToken = (string) Str::uuid();
        Cache::put($this->cacheKey((int) $request->user()->id, $previewToken), [
            'id_masjid' => $masjidId,
            'file_name' => (string) $file->getClientOriginalName(),
            'defaults' => $defaults,
            'rows' => $previewRows,
        ], now()->addMinutes(self::CACHE_TTL_MINUTES));

        return view('bank.import', [
            'masjidOptions' => $this->masjidOptions($request),
            'selectedMasjidId' => $masjidId,
            'previewRows' => $previewRows,
            'previewToken' => $previewToken,
            'fileName' => (string) $file->getClientOriginalName(),
            'totalRows' => count($previewRows),
            'validRows' => $validRows,
            'invalidRows' => $invalidRows,
            'matchedRows' => $matchedRows,
            'unmatchedRows' => $unmatchedRows,
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
            return redirect()->route('admin.bank.import.index')->with('error', 'Sesi pratonton telah tamat. Sila muat naik semula fail bank statement.');
        }

        $defaults = $payload['defaults'] ?? [];
        if (!is_array($defaults) || !empty($defaults['errors'])) {
            return redirect()->route('admin.bank.import.index')->with('error', 'Tetapan lalai akaun/kategori tidak lengkap. Sila semak data master anda.');
        }

        $choices = (array) $request->input('choices', []);
        $importedHasil = 0;
        $importedBelanja = 0;
        $skipped = 0;
        $runtimeErrors = [];
        $batchFingerprints = [];

        foreach ((array) $payload['rows'] as $row) {
            $rowNumber = (int) ($row['row_number'] ?? 0);
            $choice = (string) ($choices[$rowNumber] ?? ($row['suggested_type'] ?? 'abaikan'));

            if (!in_array($choice, ['hasil', 'belanja', 'abaikan'], true)) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': Pilihan klasifikasi tidak sah.';
                $skipped++;
                continue;
            }

            if ($choice === 'abaikan') {
                $skipped++;
                continue;
            }

            $validationErrors = $this->validateRowForStore($row);
            if (!empty($validationErrors)) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': ' . implode('; ', $validationErrors);
                $skipped++;
                continue;
            }

            $mapped = (array) ($row['mapped'] ?? []);
            $resolvedAkaunId = $this->resolveAkaunIdForChoice($choice, $mapped, $defaults);
            if ($resolvedAkaunId <= 0) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': Akaun tidak dapat dipadankan untuk transaksi ini.';
                $skipped++;
                continue;
            }

            $batchKey = $choice . ':' . $resolvedAkaunId . ':' . $this->transactionFingerprint(
                (string) ($mapped['tarikh'] ?? ''),
                (float) ($mapped['jumlah'] ?? 0),
                $mapped['keterangan'] ?? null
            );
            if (isset($batchFingerprints[$batchKey])) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': Duplikasi dalam fail import (baris sama telah diproses sebelum ini).';
                $skipped++;
                continue;
            }

            if ($this->existsDuplicateInDatabase(
                (int) $payload['id_masjid'],
                $choice,
                (string) ($mapped['tarikh'] ?? ''),
                (float) ($mapped['jumlah'] ?? 0),
                $mapped['keterangan'] ?? null,
                null
            )) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': Duplikasi dikesan pada rekod sedia ada.';
                $skipped++;
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

                    $batchFingerprints[$batchKey] = true;
                    $importedHasil++;
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
                    'id_kategori_belanja' => (int) ($defaults['kategori_belanja_id'] ?? 0),
                    'submit_action' => 'submitted',
                    'penerima' => null,
                    'catatan' => (string) ($mapped['keterangan'] ?? ''),
                ]);

                $batchFingerprints[$batchKey] = true;
                $importedBelanja++;
            } catch (ValidationException $exception) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': ' . collect($exception->errors())->flatten()->implode('; ');
                $skipped++;
            } catch (Throwable $exception) {
                $runtimeErrors[] = 'Baris ' . $rowNumber . ': ' . $exception->getMessage();
                $skipped++;
            }
        }

        Cache::forget($cacheKey);

        return redirect()
            ->route('admin.bank.import.index')
            ->with('status', 'Import selesai. ' . $importedHasil . ' hasil, ' . $importedBelanja . ' belanja, ' . $skipped . ' diabaikan.')
            ->with('import_errors', $runtimeErrors);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function buildPreviewRows(array $rows, int $masjidId): array
    {
        $akaunIndex = $this->buildAkaunIndex($masjidId);
        $seenFingerprints = [];
        $previewRows = [];

        foreach (array_values($rows) as $index => $row) {
            $rowNumber = $index + 2;

            $tarikhRaw = $this->displayValue($row['tarikh'] ?? null);
            $keterangan = $this->displayValue($row['description'] ?? null);
            $akaunRaw = $this->displayValue($row['akaun'] ?? null);
            $debit = $this->parseAmountValue($row['debit'] ?? null);
            $credit = $this->parseAmountValue($row['credit'] ?? null);
            $jumlah = $credit !== null && $credit > 0 ? $credit : ($debit !== null && $debit > 0 ? $debit : null);
            $tarikh = $this->parseDateValue($tarikhRaw);
            $akaunMapped = $this->resolveMappedAkaun($akaunRaw, $keterangan, $akaunIndex);
            $matchRecord = $this->findExistingMatchRecord($masjidId, $tarikh, $jumlah, $akaunMapped['id']);
            $reconciliationStatus = $matchRecord !== null ? 'matched' : 'unmatched';

            $suggestedType = $this->suggestType($keterangan, $debit, $credit);

            $validator = Validator::make([
                'tarikh' => $tarikh,
                'jumlah' => $jumlah,
                'type' => $suggestedType,
            ], [
                'tarikh' => ['required', 'date'],
                'jumlah' => ['required', 'numeric', 'gt:0'],
                'type' => ['required', 'in:hasil,belanja,abaikan'],
            ], [
                'tarikh.required' => 'Tarikh wajib diisi.',
                'tarikh.date' => 'Tarikh tidak sah.',
                'jumlah.required' => 'Amaun wajib diisi.',
                'jumlah.numeric' => 'Amaun mesti nombor.',
                'jumlah.gt' => 'Amaun mesti lebih besar daripada 0.',
                'type.required' => 'Jenis transaksi perlu dipilih.',
                'type.in' => 'Jenis transaksi tidak sah.',
            ]);

            $errors = $validator->errors()->all();

            $duplicateSource = $this->detectFileDuplicate($tarikh, $jumlah, $keterangan, $seenFingerprints);
            if ($duplicateSource === null && $matchRecord !== null && $this->isSameDescription($keterangan, $matchRecord['description'])) {
                $duplicateSource = 'rekod ' . $matchRecord['type'] . ' sedia ada';
            }
            if ($duplicateSource !== null) {
                $errors[] = 'Duplikasi dikesan (' . $duplicateSource . ').';
            }

            $previewRows[] = [
                'row_number' => $rowNumber,
                'data' => [
                    'tarikh' => $tarikhRaw,
                    'keterangan' => $keterangan,
                    'amaun' => $jumlah,
                    'akaun' => $akaunRaw,
                    'debit' => $debit,
                    'credit' => $credit,
                ],
                'mapped' => [
                    'tarikh' => $tarikh,
                    'keterangan' => $keterangan,
                    'jumlah' => $jumlah,
                    'id_akaun' => $akaunMapped['id'],
                ],
                'mapped_akaun_name' => $akaunMapped['name'],
                'suggested_type' => $suggestedType,
                'reconciliation_status' => $reconciliationStatus,
                'matched_record' => $matchRecord,
                'is_duplicate' => $duplicateSource !== null,
                'valid' => empty($errors),
                'errors' => $errors,
            ];
        }

        return $previewRows;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<int, string>
     */
    private function validateRowForStore(array $row): array
    {
        $mapped = (array) ($row['mapped'] ?? []);

        $validator = Validator::make([
            'tarikh' => $mapped['tarikh'] ?? null,
            'jumlah' => $mapped['jumlah'] ?? null,
        ], [
            'tarikh' => ['required', 'date'],
            'jumlah' => ['required', 'numeric', 'gt:0'],
        ], [
            'tarikh.required' => 'Tarikh wajib diisi.',
            'tarikh.date' => 'Tarikh tidak sah.',
            'jumlah.required' => 'Amaun wajib diisi.',
            'jumlah.numeric' => 'Amaun mesti nombor.',
            'jumlah.gt' => 'Amaun mesti lebih besar daripada 0.',
        ]);

        return $validator->errors()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveDefaults(int $masjidId): array
    {
        $hasilAkaun = Akaun::query()->byMasjid($masjidId)->aktif()->orderBy('nama_akaun')->first(['id', 'nama_akaun']);
        $belanjaAkaun = Akaun::query()->byMasjid($masjidId)->aktif()->orderBy('nama_akaun')->first(['id', 'nama_akaun']);
        $sumberHasil = SumberHasil::query()->byMasjid($masjidId)->aktif()->orderBy('nama_sumber')->first(['id', 'nama_sumber']);
        $kategoriBelanja = KategoriBelanja::query()->byMasjid($masjidId)->aktif()->orderBy('nama_kategori')->first(['id', 'nama_kategori']);

        $errors = [];
        if (!$hasilAkaun) {
            $errors['default_hasil_akaun'] = 'Tiada akaun aktif untuk import hasil.';
        }
        if (!$belanjaAkaun) {
            $errors['default_belanja_akaun'] = 'Tiada akaun aktif untuk import belanja.';
        }
        if (!$sumberHasil) {
            $errors['default_sumber_hasil'] = 'Tiada sumber hasil aktif untuk import hasil.';
        }
        if (!$kategoriBelanja) {
            $errors['default_kategori_belanja'] = 'Tiada kategori belanja aktif untuk import belanja.';
        }

        return [
            'hasil_akaun_id' => $hasilAkaun?->id,
            'belanja_akaun_id' => $belanjaAkaun?->id,
            'sumber_hasil_id' => $sumberHasil?->id,
            'kategori_belanja_id' => $kategoriBelanja?->id,
            'errors' => $errors,
        ];
    }

    private function suggestType(?string $description, ?float $debit, ?float $credit): string
    {
        $text = Str::upper((string) $description);

        foreach (['DERMA', 'SUMBANGAN'] as $keyword) {
            if (str_contains($text, $keyword)) {
                return 'hasil';
            }
        }

        foreach (['BAYAR', 'UTILITI', 'BIL'] as $keyword) {
            if (str_contains($text, $keyword)) {
                return 'belanja';
            }
        }

        if ($credit !== null && $credit > 0) {
            return 'hasil';
        }

        if ($debit !== null && $debit > 0) {
            return 'belanja';
        }

        return 'abaikan';
    }

    private function parseDateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
            } catch (Throwable) {
                return null;
            }
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $text);
                if ($parsed !== false) {
                    return $parsed->toDateString();
                }
            } catch (Throwable) {
                // Try next format.
            }
        }

        try {
            return Carbon::parse($text)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function parseAmountValue(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        $text = str_replace(' ', '', $text);
        if (str_contains($text, ',') && !str_contains($text, '.')) {
            $text = str_replace(',', '.', $text);
        } else {
            $text = str_replace(',', '', $text);
        }

        return is_numeric($text) ? (float) $text : null;
    }

    /**
     * @return array<string, array{id: int, name: string}>
     */
    private function buildAkaunIndex(int $masjidId): array
    {
        $akaunList = Akaun::query()
            ->byMasjid($masjidId)
            ->aktif()
            ->orderBy('nama_akaun')
            ->get(['id', 'nama_akaun', 'no_akaun', 'nama_bank']);

        $index = [];

        foreach ($akaunList as $akaun) {
            $aliases = [
                $akaun->nama_akaun,
                $akaun->no_akaun,
                $akaun->nama_bank,
            ];

            foreach ($aliases as $alias) {
                $key = $this->normalizeLookupKey((string) $alias);
                if ($key !== '' && !isset($index[$key])) {
                    $index[$key] = [
                        'id' => (int) $akaun->id,
                        'name' => (string) $akaun->nama_akaun,
                    ];
                }
            }
        }

        return $index;
    }

    /**
     * @param array<string, array{id: int, name: string}> $akaunIndex
     * @return array{id: ?int, name: ?string}
     */
    private function resolveMappedAkaun(?string $akaunText, ?string $description, array $akaunIndex): array
    {
        $candidates = [$akaunText, $description];

        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeLookupKey((string) $candidate);
            if ($normalized === '') {
                continue;
            }

            if (isset($akaunIndex[$normalized])) {
                return [
                    'id' => $akaunIndex[$normalized]['id'],
                    'name' => $akaunIndex[$normalized]['name'],
                ];
            }

            foreach ($akaunIndex as $alias => $entry) {
                if (str_contains($normalized, $alias) || str_contains($alias, $normalized)) {
                    return [
                        'id' => $entry['id'],
                        'name' => $entry['name'],
                    ];
                }
            }
        }

        return ['id' => null, 'name' => null];
    }

    private function resolveAkaunIdForChoice(string $choice, array $mapped, array $defaults): int
    {
        $mappedAkaunId = (int) ($mapped['id_akaun'] ?? 0);
        if ($mappedAkaunId > 0) {
            return $mappedAkaunId;
        }

        if ($choice === 'hasil') {
            return (int) ($defaults['hasil_akaun_id'] ?? 0);
        }

        return (int) ($defaults['belanja_akaun_id'] ?? 0);
    }

    /**
     * @param array<string, bool> $seenFingerprints
     */
    private function detectFileDuplicate(?string $tarikh, ?float $jumlah, ?string $keterangan, array &$seenFingerprints): ?string
    {
        if ($tarikh === null || $jumlah === null || $jumlah <= 0) {
            return null;
        }

        $fingerprint = $this->transactionFingerprint($tarikh, $jumlah, $keterangan);
        if (isset($seenFingerprints[$fingerprint])) {
            return 'dalam fail';
        }
        $seenFingerprints[$fingerprint] = true;

        return null;
    }

    /**
     * @return array{type: string, id: int, description: ?string}|null
     */
    private function findExistingMatchRecord(int $masjidId, ?string $tarikh, ?float $jumlah, ?int $akaunId): ?array
    {
        if ($tarikh === null || $jumlah === null || $jumlah <= 0) {
            return null;
        }

        $hasilQuery = Hasil::query()
            ->where('id_masjid', $masjidId)
            ->whereDate('tarikh', $tarikh)
            ->where('jumlah', $jumlah)
            ->orderByDesc('id');
        if ($akaunId !== null) {
            $hasilQuery->where('id_akaun', $akaunId);
        }

        $hasil = $hasilQuery->first(['id', 'catatan']);
        if ($hasil) {
            return [
                'type' => 'hasil',
                'id' => (int) $hasil->id,
                'description' => $hasil->catatan,
            ];
        }

        $belanjaQuery = Belanja::query()
            ->where('id_masjid', $masjidId)
            ->whereDate('tarikh', $tarikh)
            ->where('amaun', $jumlah)
            ->orderByDesc('id');
        if ($akaunId !== null) {
            $belanjaQuery->where('id_akaun', $akaunId);
        }

        $belanja = $belanjaQuery->first(['id', 'catatan']);
        if ($belanja) {
            return [
                'type' => 'belanja',
                'id' => (int) $belanja->id,
                'description' => $belanja->catatan,
            ];
        }

        return null;
    }

    private function isSameDescription(?string $left, ?string $right): bool
    {
        return $this->normalizeDescription($left) === $this->normalizeDescription($right);
    }

    private function existsDuplicateInDatabase(
        int $masjidId,
        string $type,
        string $tarikh,
        float $jumlah,
        ?string $keterangan,
        ?int $akaunId
    ): bool {
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

    private function normalizeLookupKey(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/i', '')
            ->toString();
    }

    private function normalizeDescription(?string $value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->trim()
            ->replaceMatches('/\s+/', ' ')
            ->toString();
    }

    private function transactionFingerprint(string $tarikh, float $jumlah, ?string $keterangan): string
    {
        return $tarikh . '|' . number_format($jumlah, 2, '.', '') . '|' . $this->normalizeDescription($keterangan);
    }

    private function displayValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
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
        return 'bank_import_preview:' . $userId . ':' . $token;
    }

    private function ensureImportAccess(Request $request): void
    {
        if (!Gate::allows('create', Hasil::class) && !Gate::allows('create', Belanja::class)) {
            abort(403);
        }
    }
}
