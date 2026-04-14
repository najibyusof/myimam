<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                {{ __('Nombor Rujukan') }}
            </h2>
            @can('running_no.generate')
                <a href="{{ route('admin.running-no.generate') }}"
                    class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Jana Nombor
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filter --}}
            <div class="bg-white shadow rounded-xl p-5">
                <form method="GET" action="{{ route('admin.running-no.index') }}"
                    class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Prefix</label>
                        <select name="prefix"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Semua --</option>
                            @foreach ($prefixes as $p)
                                <option value="{{ $p }}" @selected($prefix === $p)>{{ $p }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tahun</label>
                        <input type="number" name="tahun" value="{{ $tahun ?: '' }}" min="2000" max="2100"
                            placeholder="e.g. 2026"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
                        <select name="bulan"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Semua --</option>
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($bulan === $m)>
                                    {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 rounded-lg transition">
                            Tapis
                        </button>
                        <a href="{{ route('admin.running-no.index') }}"
                            class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 rounded-lg transition">
                            Set Semula
                        </a>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white shadow rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Prefix</th>
                                <th class="px-4 py-3 text-left">Tahun</th>
                                <th class="px-4 py-3 text-left">Bulan</th>
                                <th class="px-4 py-3 text-left">Kaunter Terakhir</th>
                                <th class="px-4 py-3 text-left">Nombor Terakhir</th>
                                @if (auth()->user()->hasRole('Admin'))
                                    <th class="px-4 py-3 text-left">Masjid</th>
                                @endif
                                @can('running_no.update')
                                    <th class="px-4 py-3 text-right">Tindakan</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($records as $record)
                                @php
                                    $yy = substr((string) $record->tahun, -2);
                                    $mm = str_pad($record->bulan, 2, '0', STR_PAD_LEFT);
                                    $noTerakhir = sprintf('%s-%s%s-%03d', $record->prefix, $yy, $mm, $record->last_no);
                                @endphp
                                <tr class="hover:bg-indigo-50/30 transition">
                                    <td class="px-4 py-3 font-mono font-semibold text-indigo-700">{{ $record->prefix }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $record->tahun }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $mm }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $record->last_no }}</td>
                                    <td class="px-4 py-3 font-mono text-gray-600">{{ $noTerakhir }}</td>
                                    @if (auth()->user()->hasRole('Admin'))
                                        <td class="px-4 py-3 text-gray-600">{{ $record->masjid?->nama ?? '-' }}</td>
                                    @endif
                                    @can('running_no.update')
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('admin.running-no.edit', [$record->id_masjid, $record->prefix, $record->tahun, $record->bulan]) }}"
                                                class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-xs font-medium px-3 py-1.5 rounded-md border border-indigo-200 hover:border-indigo-400 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">
                                        Tiada rekod kaunter dijumpai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($records->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
