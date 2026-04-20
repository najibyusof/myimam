<?php

namespace App\Http\Controllers\Admin;

use App\Exports\HasilImportSampleExport;
use App\Exports\HasilImportErrorExport;
use App\Http\Controllers\Controller;
use App\Imports\HasilImportPreview;
use App\Models\Akaun;
use App\Models\Hasil;
use App\Models\Masjid;
use App\Models\SumberHasil;
use App\Models\TabungKhas;
use App\Services\HasilManagementService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class HasilImportController extends Controller
{
    private const CACHE_TTL_MINUTES = 30;

    public function __construct(private readonly HasilManagementService $hasilService) {}

    public function index(Request $request): View
    {
        $this->authorize('create', Hasil::class);

        return view('admin.hasil.import', [
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
        $this->authorize('create', Hasil::class);

        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'id_masjid' => ['nullable', 'integer', 'exists:masjid,id'],
        ]);

        $masjidId = $this->selectedMasjidId($request);
        if ($masjidId === null) {
            return back()->withErrors([
                'id_masjid' => __('hasil.import.select_masjid_required'),
            ])->withInput();
        }

        $file = $request->file('excel_file');

        $import = new HasilImportPreview();
        Excel::import($import, $file);
        $rows = $import->rows();

        if (count($rows) === 0) {
            return back()->withErrors([
                'excel_file' => __('hasil.import.empty_file'),
            ])->withInput();
        }

        $previewRows = $this->buildPreviewRows($rows, $masjidId);
        $validRows = collect($previewRows)->where('valid', true)->count();
        $invalidRows = count($previewRows) - $validRows;

        $previewToken = (string) Str::uuid();
        Cache::put($this->cacheKey((int) $request->user()->id, $previewToken), [
            'id_masjid' => $masjidId,
            'file_name' => (string) $file->getClientOriginalName(),
            'total_rows' => count($previewRows),
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
            'rows' => $previewRows,
        ], now()->addMinutes(self::CACHE_TTL_MINUTES));

        return view('admin.hasil.import', [
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
        $this->authorize('create', Hasil::class);

        $request->validate([
            'preview_token' => ['required', 'string'],
        ]);

        $token = (string) $request->input('preview_token');
        $cacheKey = $this->cacheKey((int) $request->user()->id, $token);
        $payload = Cache::get($cacheKey);

        if (!is_array($payload) || empty($payload['rows'])) {
            return redirect()->route('admin.hasil.import.index')->with('error', __('hasil.import.preview_session_expired'));
        }

        $rows = collect($payload['rows']);
        $validRows = $rows->filter(fn (array $row): bool => (bool) ($row['valid'] ?? false))->values();
        $failed = (int) ($rows->count() - $validRows->count());
        $success = 0;
        $runtimeErrors = [];

        foreach ($validRows as $row) {
            $mapped = $row['mapped'] ?? [];

            try {
                $this->hasilService->create($request->user(), [
                    'id_masjid' => (int) $payload['id_masjid'],
                    'tarikh' => (string) ($mapped['tarikh'] ?? ''),
                    'amaun' => (float) ($mapped['amaun'] ?? 0),
                    'id_akaun' => (int) ($mapped['id_akaun'] ?? 0),
                    'id_sumber_hasil' => (int) ($mapped['id_sumber_hasil'] ?? 0),
                    'id_tabung_khas' => $mapped['id_tabung_khas'] ?? null,
                    'catatan' => $mapped['catatan'] ?? null,
                    'is_jumaat' => false,
                ]);

                $success++;
            } catch (ValidationException $exception) {
                $failed++;
                $messages = collect($exception->errors())->flatten()->implode('; ');
                $runtimeErrors[] = 'Baris ' . ($row['row_number'] ?? '?') . ': ' . $messages;
            } catch (Throwable $exception) {
                $failed++;
                $runtimeErrors[] = 'Baris ' . ($row['row_number'] ?? '?') . ': ' . $exception->getMessage();
            }
        }

        Cache::forget($cacheKey);

        return redirect()
            ->route('admin.hasil.import.index')
            ->with('status', __('hasil.import.summary', ['success' => $success, 'failed' => $failed]))
            ->with('import_errors', $runtimeErrors);
    }

    public function sample()
    {
        $this->authorize('create', Hasil::class);

        return Excel::download(new HasilImportSampleExport(), 'sample-import-hasil.xlsx');
    }

    public function errorReport(Request $request, string $token): RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('create', Hasil::class);

        $payload = Cache::get($this->cacheKey((int) $request->user()->id, $token));
        if (!is_array($payload) || empty($payload['rows'])) {
            return redirect()->route('admin.hasil.import.index')->with('error', __('hasil.import.preview_session_expired'));
        }

        $invalidRows = collect($payload['rows'])
            ->filter(fn (array $row): bool => !((bool) ($row['valid'] ?? false)))
            ->values()
            ->all();

        if (count($invalidRows) === 0) {
            return redirect()->route('admin.hasil.import.index')->with('status', __('hasil.import.no_errors_to_export'));
        }

        return Excel::download(
            new HasilImportErrorExport($invalidRows),
            'hasil-import-ralat-' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function buildPreviewRows(array $rows, int $masjidId): array
    {
        $akaunMap = $this->nameToIdMap(
            Akaun::query()->byMasjid($masjidId)->aktif()->orderBy('nama_akaun')->pluck('id', 'nama_akaun')->all()
        );
        $akaunAliasMap = $this->aliasToIdMap(
            Akaun::query()->byMasjid($masjidId)->aktif()->orderBy('nama_akaun')->pluck('id', 'nama_akaun')->all()
        );
        $sumberMap = $this->nameToIdMap(
            SumberHasil::query()->byMasjid($masjidId)->aktif()->orderBy('nama_sumber')->pluck('id', 'nama_sumber')->all()
        );
        $tabungMap = $this->nameToIdMap(
            TabungKhas::query()->byMasjid($masjidId)->aktif()->orderBy('nama_tabung')->pluck('id', 'nama_tabung')->all()
        );
        $tabungAliasMap = $this->aliasToIdMap(
            TabungKhas::query()->byMasjid($masjidId)->aktif()->orderBy('nama_tabung')->pluck('id', 'nama_tabung')->all()
        );

        $previewRows = [];

        foreach (array_values($rows) as $index => $row) {
            $rowNumber = $index + 2;

            $tarikhValue = $row['tarikh'] ?? null;
            $sumberRaw = $this->displayValue($row['sumber'] ?? null);
            $amaunRaw = $row['amaun'] ?? null;
            $akaunRaw = $this->displayValue($row['akaun'] ?? null);
            $catatan = $this->displayValue($row['catatan'] ?? null);
            $tabungRaw = $this->displayValue($row['tabung_khas'] ?? null);

            $sumber = $this->normalizeTextValue($sumberRaw);
            $akaun = $this->normalizeTextValue($akaunRaw);
            $tabung = $this->normalizeTextValue($tabungRaw);

            $parsedDate = $this->parseDateValue($tarikhValue);
            $amount = $this->parseAmountValue($amaunRaw);

            $errors = [];

            $validator = Validator::make([
                'tarikh' => $parsedDate,
                'sumber' => $sumber,
                'amaun' => $amount,
                'akaun' => $akaun,
            ], [
                'tarikh' => ['required', 'date'],
                'sumber' => ['required', 'string'],
                'amaun' => ['required', 'numeric', 'gt:0'],
                'akaun' => ['required', 'string'],
            ], [
                'tarikh.required' => 'Tarikh wajib diisi.',
                'tarikh.date' => 'Format tarikh tidak sah.',
                'sumber.required' => 'Sumber wajib diisi.',
                'amaun.required' => 'Amaun wajib diisi.',
                'amaun.numeric' => 'Amaun mesti nombor.',
                'amaun.gt' => 'Amaun mesti lebih besar daripada 0.',
                'akaun.required' => 'Akaun wajib diisi.',
            ]);

            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
            }

            $akaunId = $this->resolveIdByName($akaunMap, $akaun, $akaunRaw, $akaunAliasMap);
            if ($akaun !== null && $akaunId === null) {
                $errors[] = 'Akaun tidak ditemui: ' . $akaun;
            }

            $sumberId = $this->resolveIdByName($sumberMap, $sumber);
            if ($sumber !== null && $sumberId === null) {
                $errors[] = 'Sumber tidak ditemui: ' . $sumber;
            }

            $tabungId = null;
            if ($tabung !== null) {
                $tabungId = $this->resolveIdByName($tabungMap, $tabung, $tabungRaw, $tabungAliasMap);
                if ($tabungId === null) {
                    $errors[] = 'Tabung khas tidak ditemui: ' . $tabung;
                }
            }

            $previewRows[] = [
                'row_number' => $rowNumber,
                'data' => [
                    'tarikh' => $this->displayValue($tarikhValue),
                    'sumber' => $sumberRaw,
                    'amaun' => $this->displayValue($amaunRaw),
                    'akaun' => $akaunRaw,
                    'catatan' => $catatan,
                    'tabung_khas' => $tabungRaw,
                ],
                'mapped' => [
                    'tarikh' => $parsedDate,
                    'amaun' => $amount,
                    'id_akaun' => $akaunId,
                    'id_sumber_hasil' => $sumberId,
                    'id_tabung_khas' => $tabungId,
                    'catatan' => $catatan,
                ],
                'valid' => count($errors) === 0,
                'errors' => array_values(array_unique($errors)),
            ];
        }

        return $previewRows;
    }

    /**
     * @param array<string, mixed> $lookup
     * @return array<string, int>
     */
    private function nameToIdMap(array $lookup): array
    {
        $map = [];

        foreach ($lookup as $name => $id) {
            $normalized = $this->normalizeTextValue($name);
            if ($normalized === null) {
                continue;
            }

            $map[$normalized] = (int) $id;
        }

        return $map;
    }

    private function resolveIdByName(array $map, ?string $normalizedValue, ?string $rawValue = null, array $aliasMap = []): ?int
    {
        if ($normalizedValue === null) {
            return null;
        }

        if (array_key_exists($normalizedValue, $map)) {
            return $map[$normalizedValue];
        }

        if ($rawValue !== null) {
            $alias = $this->normalizeAliasValue($rawValue);
            if ($alias !== null && array_key_exists($alias, $aliasMap)) {
                return $aliasMap[$alias];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $lookup
     * @return array<string, int>
     */
    private function aliasToIdMap(array $lookup): array
    {
        $map = [];

        foreach ($lookup as $name => $id) {
            $alias = $this->normalizeAliasValue((string) $name);
            if ($alias === null) {
                continue;
            }

            // Keep first match to avoid ambiguous remap when multiple names collapse into the same alias.
            if (!array_key_exists($alias, $map)) {
                $map[$alias] = (int) $id;
            }
        }

        return $map;
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

        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $text);
                if ($parsed !== false) {
                    return $parsed->toDateString();
                }
            } catch (Throwable) {
                // Try the next format.
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

    private function displayValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }

    private function normalizeTextValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        return Str::of($text)->lower()->squish()->toString();
    }

    private function normalizeAliasValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = Str::of($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/i', '')
            ->toString();

        return $text !== '' ? $text : null;
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
        return 'hasil_import_preview:' . $userId . ':' . $token;
    }
}
