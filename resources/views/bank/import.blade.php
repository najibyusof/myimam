<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Import Bank Statement</h2>
                <p class="mt-1 text-sm text-gray-500">Muat naik fail, semak cadangan klasifikasi, dan sahkan import
                    transaksi.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @php
                $importErrors = session('import_errors', []);
            @endphp
            @if (!empty($importErrors))
                <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    <p class="font-semibold">Sebahagian baris tidak diimport:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($importErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('admin.bank.import.preview') }}" enctype="multipart/form-data"
                    class="grid gap-4 md:grid-cols-[1fr_auto]">
                    @csrf

                    <div class="space-y-3">
                        @if (auth()->user()->hasRole('Superadmin'))
                            <div>
                                <x-input-label for="id_masjid" value="Masjid" />
                                <select id="id_masjid" name="id_masjid"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                    <option value="">Pilih masjid</option>
                                    @foreach ($masjidOptions as $option)
                                        <option value="{{ $option->id }}" @selected((int) old('id_masjid', $selectedMasjidId) === (int) $option->id)>
                                            {{ $option->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('id_masjid')" />
                            </div>
                        @endif

                        <div>
                            <x-input-label for="excel_file" value="Fail Bank Statement" />
                            <input id="excel_file" name="excel_file" type="file" accept=".xlsx,.xls,.csv"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                                required>
                            <x-input-error class="mt-2" :messages="$errors->get('excel_file')" />
                            <p class="mt-1 text-xs text-gray-500">Kolum dijangka: tarikh, description, debit, credit,
                                balance.</p>
                            <div class="mt-2">
                                <a href="{{ route('admin.bank.import.sample') }}"
                                    class="inline-flex items-center rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                    Muat Turun Sampel Excel
                                </a>
                                <a href="{{ route('admin.bank.pdf-import.index') }}"
                                    class="ml-2 inline-flex items-center rounded-md border border-indigo-300 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                    Pergi Ke Import PDF
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-end">
                        <x-primary-button>Pratonton Transaksi</x-primary-button>
                    </div>
                </form>
            </div>

            @if ($previewToken)
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    @php
                        $issueRows = collect($previewRows)
                            ->filter(fn($row) => !$row['valid'] || !empty($row['is_duplicate']))
                            ->count();
                    @endphp
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Pratonton Bank Statement</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Fail: <span class="font-medium text-slate-700">{{ $fileName }}</span>
                                | Jumlah baris: <span class="font-medium text-slate-700">{{ $totalRows }}</span>
                                | Valid: <span class="font-medium text-emerald-700">{{ $validRows }}</span>
                                | Invalid: <span class="font-medium text-red-700">{{ $invalidRows }}</span>
                                | Matched: <span class="font-medium text-sky-700">{{ $matchedRows ?? 0 }}</span>
                                | Unmatched: <span class="font-medium text-slate-700">{{ $unmatchedRows ?? 0 }}</span>
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" id="select-all-hasil"
                                class="inline-flex items-center rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                Pilih Semua Hasil
                            </button>
                            <button type="button" id="select-all-belanja"
                                class="inline-flex items-center rounded-md border border-sky-300 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-100">
                                Pilih Semua Belanja
                            </button>
                        </div>
                    </div>

                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <button type="button" id="show-all-rows"
                            class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            Papar Semua Baris
                        </button>
                        <button type="button" id="show-issues-only"
                            class="inline-flex items-center rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                            Papar Isu Sahaja ({{ $issueRows }})
                        </button>
                        <button type="button" id="show-matched-only"
                            class="inline-flex items-center rounded-md border border-sky-300 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-800 hover:bg-sky-100">
                            Papar Matched Sahaja ({{ $matchedRows ?? 0 }})
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.bank.import.store') }}">
                        @csrf
                        <input type="hidden" name="preview_token" value="{{ $previewToken }}">

                        <div class="overflow-hidden rounded-2xl border border-slate-200 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Tarikh</th>
                                        <th class="px-4 py-3 text-left">Keterangan</th>
                                        <th class="px-4 py-3 text-left">Akaun (Auto)</th>
                                        <th class="px-4 py-3 text-right">Amaun</th>
                                        <th class="px-4 py-3 text-left">Cadangan</th>
                                        <th class="px-4 py-3 text-left">Rekonsiliasi</th>
                                        <th class="px-4 py-3 text-left">Pilihan</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($previewRows as $row)
                                        <tr class="preview-row"
                                            data-has-issue="{{ !$row['valid'] || !empty($row['is_duplicate']) ? '1' : '0' }}"
                                            data-reconciliation-status="{{ $row['reconciliation_status'] ?? 'unmatched' }}">
                                            <td class="px-4 py-3 text-gray-700">{{ $row['data']['tarikh'] ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">{{ $row['data']['keterangan'] ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">
                                                @if (!empty($row['mapped_akaun_name']))
                                                    <span
                                                        class="inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-800">
                                                        {{ $row['mapped_akaun_name'] }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-500">Lalai sistem</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right font-medium text-gray-900">
                                                {{ $row['data']['amaun'] !== null ? number_format((float) $row['data']['amaun'], 2) : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">
                                                {{ ucfirst($row['suggested_type']) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if (($row['reconciliation_status'] ?? 'unmatched') === 'matched')
                                                    <div class="space-y-1">
                                                        <span
                                                            class="inline-flex rounded-full bg-sky-100 px-2 py-1 text-xs font-semibold text-sky-800">Matched</span>
                                                        @if (!empty($row['matched_record']['type']) && !empty($row['matched_record']['id']))
                                                            <p class="text-xs text-sky-700">
                                                                {{ ucfirst($row['matched_record']['type']) }}
                                                                #{{ $row['matched_record']['id'] }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span
                                                        class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">Unmatched</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <select name="choices[{{ $row['row_number'] }}]"
                                                    class="choice-select block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="hasil" @selected($row['suggested_type'] === 'hasil')>Hasil</option>
                                                    <option value="belanja" @selected($row['suggested_type'] === 'belanja')>Belanja
                                                    </option>
                                                    <option value="abaikan" @selected($row['suggested_type'] === 'abaikan')>Abaikan
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if (!empty($row['is_duplicate']))
                                                    <span
                                                        class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Duplikasi</span>
                                                @endif
                                                @if ($row['valid'])
                                                    <span
                                                        class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">Valid</span>
                                                @else
                                                    <div class="space-y-1">
                                                        <span
                                                            class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">Invalid</span>
                                                        <p class="text-xs text-red-700">
                                                            {{ implode('; ', $row['errors']) }}</p>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex items-center justify-end">
                            <button type="submit"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Import Transaksi
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    @if ($previewToken)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const filterStorageKey = 'bank_import_preview_filter_mode';

                const applyChoiceToAll = function(choice) {
                    document.querySelectorAll('.choice-select').forEach(function(selectEl) {
                        selectEl.value = choice;
                    });
                };

                const showRows = function(mode) {
                    document.querySelectorAll('.preview-row').forEach(function(rowEl) {
                        const hasIssue = rowEl.dataset.hasIssue === '1';
                        const isMatched = rowEl.dataset.reconciliationStatus === 'matched';

                        if (mode === 'issues') {
                            rowEl.style.display = hasIssue ? '' : 'none';
                            return;
                        }

                        if (mode === 'matched') {
                            rowEl.style.display = isMatched ? '' : 'none';
                            return;
                        }

                        rowEl.style.display = '';
                    });
                };

                const updateFilterButtonState = function(mode) {
                    const showAllRowsButton = document.getElementById('show-all-rows');
                    const showIssuesOnlyButton = document.getElementById('show-issues-only');
                    const showMatchedOnlyButton = document.getElementById('show-matched-only');
                    const isAll = mode === 'all';
                    const isIssues = mode === 'issues';
                    const isMatched = mode === 'matched';

                    if (showAllRowsButton) {
                        showAllRowsButton.classList.toggle('bg-slate-900', isAll);
                        showAllRowsButton.classList.toggle('text-white', isAll);
                        showAllRowsButton.classList.toggle('border-slate-900', isAll);
                    }

                    if (showIssuesOnlyButton) {
                        showIssuesOnlyButton.classList.toggle('bg-amber-600', isIssues);
                        showIssuesOnlyButton.classList.toggle('text-white', isIssues);
                        showIssuesOnlyButton.classList.toggle('border-amber-600', isIssues);
                    }

                    if (showMatchedOnlyButton) {
                        showMatchedOnlyButton.classList.toggle('bg-sky-600', isMatched);
                        showMatchedOnlyButton.classList.toggle('text-white', isMatched);
                        showMatchedOnlyButton.classList.toggle('border-sky-600', isMatched);
                    }
                };

                const applyFilterMode = function(mode) {
                    const resolvedMode = ['all', 'issues', 'matched'].includes(mode) ? mode : 'all';
                    showRows(resolvedMode);
                    updateFilterButtonState(resolvedMode);
                    localStorage.setItem(filterStorageKey, resolvedMode);
                };

                const hasilButton = document.getElementById('select-all-hasil');
                if (hasilButton) {
                    hasilButton.addEventListener('click', function() {
                        applyChoiceToAll('hasil');
                    });
                }

                const belanjaButton = document.getElementById('select-all-belanja');
                if (belanjaButton) {
                    belanjaButton.addEventListener('click', function() {
                        applyChoiceToAll('belanja');
                    });
                }

                const showAllRowsButton = document.getElementById('show-all-rows');
                if (showAllRowsButton) {
                    showAllRowsButton.addEventListener('click', function() {
                        applyFilterMode('all');
                    });
                }

                const showIssuesOnlyButton = document.getElementById('show-issues-only');
                if (showIssuesOnlyButton) {
                    showIssuesOnlyButton.addEventListener('click', function() {
                        applyFilterMode('issues');
                    });
                }

                const showMatchedOnlyButton = document.getElementById('show-matched-only');
                if (showMatchedOnlyButton) {
                    showMatchedOnlyButton.addEventListener('click', function() {
                        applyFilterMode('matched');
                    });
                }

                const savedMode = localStorage.getItem(filterStorageKey);
                applyFilterMode(savedMode ?? 'all');
            });
        </script>
    @endif
</x-app-layout>
