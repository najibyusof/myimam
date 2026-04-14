<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pindahan Akaun') }}
            </h2>
            @can('pindahan_akaun.create')
                <a href="{{ route('admin.pindahan-akaun.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Tambah Pindahan') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Jumlah Rekod</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Jumlah Amaun Dipindah</p>
                    <p class="mt-1 text-2xl font-bold text-indigo-600">RM {{ number_format($stats['jumlah'], 2) }}</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <form method="GET" action="{{ route('admin.pindahan-akaun.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Akaun</label>
                        <select name="akaun_id"
                                class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0">Semua Akaun</option>
                            @foreach ($akaunOptions as $akaun)
                                <option value="{{ $akaun->id }}" @selected($akaunId === $akaun->id)>
                                    {{ $akaun->nama_akaun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tarikh Dari</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                               class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tarikh Hingga</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                               class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button type="submit"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Tapis
                    </button>
                    @if ($akaunId || $dateFrom || $dateTo)
                        <a href="{{ route('admin.pindahan-akaun.index') }}"
                           class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                            Padam Tapis
                        </a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                @if ($records->isEmpty())
                    <div class="px-6 py-16 text-center text-sm text-gray-500">
                        Tiada rekod pindahan akaun dijumpai.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Tarikh</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Dari Akaun</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Ke Akaun</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Amaun (RM)</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                                    <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase tracking-wider">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($records as $record)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ $record->tarikh->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-medium text-red-700">{{ $record->dariAkaun?->nama_akaun ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-medium text-green-700">{{ $record->keAkaun?->nama_akaun ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                            {{ number_format($record->amaun, 2) }}
                                        </td>
                                        <td class="px-4 py-3 max-w-xs truncate text-gray-600">
                                            {{ $record->catatan ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex justify-center gap-2">
                                                @can('update', $record)
                                                    <a href="{{ route('admin.pindahan-akaun.edit', $record) }}"
                                                       class="inline-flex items-center gap-1 rounded-md border border-indigo-300 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Edit
                                                    </a>
                                                @endcan
                                                @can('delete', $record)
                                                    <form method="POST"
                                                          action="{{ route('admin.pindahan-akaun.destroy', $record) }}"
                                                          onsubmit="return confirm('Padam pindahan ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1 rounded-md border border-rose-300 bg-rose-50 px-3 py-1 text-xs font-medium text-rose-700 hover:bg-rose-100">
                                                            Padam
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3 border-t border-gray-100">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
