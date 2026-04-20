<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('hasil.import.title') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('hasil.import.subtitle') }}</p>
            </div>
            <a href="{{ route('admin.hasil.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                {{ __('hasil.import.back_to_management') }}
            </a>
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
                    <p class="font-semibold">{{ __('hasil.import.partial_failures') }}</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($importErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-xs text-amber-800">{{ __('hasil.import.fix_and_reupload_help') }}</p>
                </div>
            @endif

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <form method="POST" action="{{ route('admin.hasil.import.preview') }}"
                        enctype="multipart/form-data" class="grid flex-1 gap-3 md:grid-cols-[1fr_auto]">
                        @csrf

                        <div class="space-y-3">
                            @if (auth()->user()->hasRole('Superadmin'))
                                <div>
                                    <x-input-label for="id_masjid" :value="__('hasil.form.masjid')" />
                                    <select id="id_masjid" name="id_masjid"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                        <option value="">{{ __('hasil.form.select_masjid') }}</option>
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
                                <x-input-label for="excel_file" :value="__('hasil.import.file')" />
                                <input id="excel_file" name="excel_file" type="file" accept=".xlsx,.xls,.csv"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                                    required />
                                <x-input-error class="mt-2" :messages="$errors->get('excel_file')" />
                                <p class="mt-1 text-xs text-gray-500">{{ __('hasil.import.file_help') }}</p>
                            </div>
                        </div>

                        <div class="flex items-end gap-2">
                            <x-primary-button>{{ __('hasil.import.preview_button') }}</x-primary-button>
                        </div>
                    </form>

                    <a href="{{ route('admin.hasil.import.sample') }}"
                        class="inline-flex items-center rounded-md border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
                        {{ __('hasil.import.download_sample') }}
                    </a>
                </div>
            </div>

            @if ($previewToken)
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('hasil.import.preview_title') }}
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ __('hasil.import.file_name') }}: <span
                                    class="font-medium text-slate-700">{{ $fileName }}</span>
                                | {{ __('hasil.import.total_rows') }}: <span
                                    class="font-medium text-slate-700">{{ $totalRows }}</span>
                                | {{ __('hasil.import.valid_rows') }}: <span
                                    class="font-medium text-emerald-700">{{ $validRows }}</span>
                                | {{ __('hasil.import.invalid_rows') }}: <span
                                    class="font-medium text-red-700">{{ $invalidRows }}</span>
                            </p>
                            @if ($invalidRows > 0)
                                <p class="mt-2 text-xs text-amber-700">{{ __('hasil.import.fix_and_reupload_help') }}
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if ($invalidRows > 0)
                                <a href="{{ route('admin.hasil.import.error-report', ['token' => $previewToken]) }}"
                                    class="inline-flex items-center rounded-md border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">
                                    {{ __('hasil.import.download_error_report') }}
                                </a>
                            @endif

                            <form method="POST" action="{{ route('admin.hasil.import.store') }}">
                                @csrf
                                <input type="hidden" name="preview_token" value="{{ $previewToken }}">
                                <button type="submit"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                                    @disabled($validRows === 0)>
                                    {{ __('hasil.import.import_button') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">{{ __('hasil.table.date') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('hasil.table.source') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('hasil.table.amount') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('hasil.table.account') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('hasil.import.status') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('hasil.import.error_message') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($previewRows as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-700">{{ $row['data']['tarikh'] ?: '-' }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $row['data']['sumber'] ?: '-' }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-900">
                                            {{ $row['data']['amaun'] ?: '-' }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $row['data']['akaun'] ?: '-' }}</td>
                                        <td class="px-4 py-3">
                                            @if ($row['valid'])
                                                <span
                                                    class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">{{ __('hasil.import.valid') }}</span>
                                            @else
                                                <span
                                                    class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">{{ __('hasil.import.invalid') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-red-700">
                                            @if ($row['valid'])
                                                -
                                            @else
                                                {{ __('hasil.import.row_prefix', ['row' => $row['row_number']]) }}:
                                                {{ implode('; ', $row['errors']) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
