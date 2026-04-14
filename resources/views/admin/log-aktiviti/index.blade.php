<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            {{ __('Log Aktiviti') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Jumlah Log</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Hari Ini</p>
                    <p class="text-2xl font-bold text-indigo-700">{{ number_format($stats['hari_ini']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Login Berjaya (7h)</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['login_ok']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Login Gagal (7h)</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($stats['login_fail']) }}</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white shadow rounded-xl p-5">
                <form method="GET" action="{{ route('admin.log-aktiviti.index') }}"
                      class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-4 items-end">

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis</label>
                        <select name="jenis"
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Semua --</option>
                            @foreach ($jenisOptions as $j)
                                <option value="{{ $j }}" @selected($jenis === $j)>{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Modul</label>
                        <select name="modul"
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Semua --</option>
                            @foreach ($modulOptions as $m)
                                <option value="{{ $m }}" @selected($modul === $m)>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pengguna</label>
                        <select name="user_id"
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Semua --</option>
                            @foreach ($userOptions as $u)
                                <option value="{{ $u->id }}" @selected($userId === $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tarikh</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Hingga Tarikh</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 rounded-lg transition">
                            Tapis
                        </button>
                        <a href="{{ route('admin.log-aktiviti.index') }}"
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
                                <th class="px-4 py-3 text-left">Masa</th>
                                <th class="px-4 py-3 text-left">Jenis</th>
                                <th class="px-4 py-3 text-left">Modul / Aksi</th>
                                <th class="px-4 py-3 text-left">Pengguna</th>
                                <th class="px-4 py-3 text-left">Butiran</th>
                                <th class="px-4 py-3 text-left">IP</th>
                                <th class="px-4 py-3 text-right">Terperinci</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($records as $record)
                                @php
                                    $jenisBadge = match($record->jenis) {
                                        'LOGIN_OK'   => 'bg-green-100 text-green-800',
                                        'LOGIN_FAIL' => 'bg-red-100 text-red-800',
                                        'CREATE'     => 'bg-blue-100 text-blue-800',
                                        'UPDATE'     => 'bg-amber-100 text-amber-800',
                                        'DELETE'     => 'bg-rose-100 text-rose-800',
                                        'APPROVE'    => 'bg-purple-100 text-purple-800',
                                        'EXPORT'     => 'bg-teal-100 text-teal-800',
                                        default      => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <tr class="hover:bg-indigo-50/30 transition">
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap text-xs">
                                        {{ $record->created_at?->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $jenisBadge }}">
                                            {{ $record->jenis }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($record->modul)
                                            <span class="font-medium text-gray-700">{{ $record->modul }}</span>
                                        @endif
                                        @if ($record->aksi)
                                            <span class="block text-gray-500 text-xs">{{ $record->aksi }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $record->user?->name ?? '–' }}
                                        @if ($record->user?->email)
                                            <span class="block text-xs text-gray-400">{{ $record->user->email }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 max-w-xs truncate">
                                        {{ $record->butiran ?? '–' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 text-xs font-mono whitespace-nowrap">
                                        {{ $record->ip ?? '–' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.log-aktiviti.show', $record) }}"
                                           class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-xs font-medium px-2.5 py-1.5 rounded-md border border-indigo-200 hover:border-indigo-400 transition">
                                            Lihat
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">
                                        Tiada rekod log dijumpai.
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
