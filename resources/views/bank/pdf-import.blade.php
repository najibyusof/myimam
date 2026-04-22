<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Import Bank Statement PDF</h2>
                <p class="mt-1 text-sm text-gray-500">Muat naik PDF, semak cadangan klasifikasi, dan import transaksi
                    yang dipilih.</p>
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
                <form method="POST" action="{{ route('admin.bank.pdf-import.preview') }}" enctype="multipart/form-data"
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
                            <x-input-label for="pdf_file" value="Fail PDF" />
                            <input id="pdf_file" name="pdf_file" type="file" accept="application/pdf,.pdf"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                                required>
                            <x-input-error class="mt-2" :messages="$errors->get('pdf_file')" />
                            <p class="mt-1 text-xs text-gray-500">Format dijangka: tarikh, keterangan, amaun (+/-),
                                baki.</p>
                        </div>
                    </div>

                    <div class="flex items-end">
                        <x-primary-button>Pratonton Transaksi PDF</x-primary-button>
                    </div>
                </form>
            </div>

            @if ($previewToken)
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Pratonton Import PDF</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Fail: <span class="font-medium text-slate-700">{{ $fileName }}</span>
                                | Jumlah baris dikesan: <span
                                    class="font-medium text-slate-700">{{ $totalRows }}</span>
                                | Valid: <span class="font-medium text-emerald-700">{{ $validRows }}</span>
                                | Invalid: <span class="font-medium text-red-700">{{ $invalidRows }}</span>
                            </p>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                <span
                                    class="inline-flex rounded-full bg-amber-100 px-2 py-1 font-semibold text-amber-800">Duplikasi
                                    Fail</span>
                                <span class="text-slate-500">Transaksi berulang dalam fail PDF yang sama</span>
                                <span
                                    class="inline-flex rounded-full bg-orange-100 px-2 py-1 font-semibold text-orange-800">Duplikasi
                                    DB</span>
                                <span class="text-slate-500">Transaksi telah wujud dalam rekod sedia ada</span>
                                <span class="mx-1 text-slate-300">|</span>
                                <span
                                    class="inline-flex rounded-full bg-violet-100 px-1.5 py-0.5 font-semibold text-violet-700">Terbina</span>
                                <span class="text-slate-500">Peraturan terbina (hardcoded)</span>
                                <span
                                    class="inline-flex rounded-full bg-teal-100 px-1.5 py-0.5 font-semibold text-teal-700">Dipelajari</span>
                                <span class="text-slate-500">Dipelajari daripada import lepas</span>
                                <span
                                    class="inline-flex rounded-full bg-gray-100 px-1.5 py-0.5 font-semibold text-gray-500">Lalai</span>
                                <span class="text-slate-500">Tiada padanan, guna lalai</span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.bank.pdf-import.store') }}">
                        @csrf
                        <input type="hidden" name="preview_token" value="{{ $previewToken }}">

                        <div class="overflow-hidden rounded-2xl border border-slate-200 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Tarikh</th>
                                        <th class="px-4 py-3 text-left">Keterangan</th>
                                        <th class="px-4 py-3 text-right">Amaun</th>
                                        <th class="px-4 py-3 text-left">Cadangan</th>
                                        <th class="px-4 py-3 text-left">Auto Akaun</th>
                                        <th class="px-4 py-3 text-left">Auto Kategori</th>
                                        <th class="px-4 py-3 text-left">Rekonsiliasi</th>
                                        <th class="px-4 py-3 text-left">Pilihan</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($previewRows as $row)
                                        <tr class="{{ $row['valid'] ? 'bg-emerald-50/40' : 'bg-red-50/50' }}">
                                            <td class="px-4 py-3 text-gray-700">{{ $row['data']['tarikh'] ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">{{ $row['data']['keterangan'] ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-medium text-gray-900">
                                                {{ $row['data']['amaun'] !== null ? number_format((float) $row['data']['amaun'], 2) : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">
                                                <span>{{ ucfirst($row['suggested_type']) }}</span>
                                                @php
                                                    $src = $row['suggestion_source'] ?? 'fallback';
                                                    $srcLabel = match ($src) {
                                                        'builtin' => 'Terbina',
                                                        'learned' => 'Dipelajari',
                                                        default => 'Lalai',
                                                    };
                                                    $srcClass = match ($src) {
                                                        'builtin' => 'bg-violet-100 text-violet-700',
                                                        'learned' => 'bg-teal-100 text-teal-700',
                                                        default => 'bg-gray-100 text-gray-500',
                                                    };
                                                @endphp
                                                <span
                                                    class="mt-0.5 inline-flex rounded-full px-1.5 py-0.5 text-xs {{ $srcClass }}">{{ $srcLabel }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">{{ $row['auto_akaun_name'] ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">{{ $row['auto_kategori_name'] ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if (($row['reconciliation_status'] ?? 'unmatched') === 'matched')
                                                    <span
                                                        class="inline-flex rounded-full bg-sky-100 px-2 py-1 text-xs font-semibold text-sky-800">Matched</span>
                                                    @if (!empty($row['matched_record']['type']) && !empty($row['matched_record']['id']))
                                                        <p class="mt-1 text-xs text-sky-700">
                                                            {{ ucfirst($row['matched_record']['type']) }}
                                                            #{{ $row['matched_record']['id'] }}
                                                        </p>
                                                    @endif
                                                @else
                                                    <span
                                                        class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">Unmatched</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <select name="choices[{{ $row['row_number'] }}]"
                                                    class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="hasil" @selected($row['suggested_type'] === 'hasil')>Hasil</option>
                                                    <option value="belanja" @selected($row['suggested_type'] === 'belanja')>Belanja
                                                    </option>
                                                    <option value="abaikan" @selected($row['suggested_type'] === 'abaikan')>Abaikan
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if (!empty($row['is_duplicate']))
                                                    @if (($row['duplicate_source'] ?? null) === 'database')
                                                        <span
                                                            class="mr-1 inline-flex rounded-full bg-orange-100 px-2 py-1 text-xs font-semibold text-orange-800">Duplikasi
                                                            DB</span>
                                                    @else
                                                        <span
                                                            class="mr-1 inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Duplikasi
                                                            Fail</span>
                                                    @endif
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
</x-app-layout>
