<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengurusan Hasil</h2>
                <p class="mt-1 text-sm text-gray-500">Urus transaksi hasil mengikut tempoh, akaun, dan kutipan Jumaat.
                </p>
            </div>
            @can('create', \App\Models\Hasil::class)
                <a href="{{ route('admin.hasil.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Tambah Hasil
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Jumlah Transaksi</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-emerald-700">Jumlah Amaun</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-900">RM {{ number_format($stats['jumlah'], 2) }}
                    </p>
                </div>
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-amber-700">Kutipan Jumaat</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-900">{{ $stats['jumaat'] }}</p>
                </div>
            </div>

            <form method="GET" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-[160px_160px_220px_180px_auto_auto]">
                    <x-text-input name="from" type="date" class="block w-full" :value="$from" />
                    <x-text-input name="to" type="date" class="block w-full" :value="$to" />
                    <select name="akaun_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="0">Semua akaun</option>
                        @foreach ($akaunOptions as $option)
                            <option value="{{ $option->id }}" @selected($akaunId === $option->id)>{{ $option->nama_akaun }}
                            </option>
                        @endforeach
                    </select>
                    <select name="jumaat"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all" @selected($jumaat === 'all')>Semua kutipan</option>
                        <option value="yes" @selected($jumaat === 'yes')>Jumaat sahaja</option>
                        <option value="no" @selected($jumaat === 'no')>Bukan Jumaat</option>
                    </select>
                    <x-primary-button>Tapis</x-primary-button>
                    <a href="{{ route('admin.hasil.index') }}"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Tarikh</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Amaun</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Akaun</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Sumber Hasil</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Tabung Khas</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Jumaat</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($hasil as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ optional($item->tarikh)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">RM
                                    {{ number_format((float) $item->jumlah, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->akaun->nama_akaun ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $item->sumberHasil->nama_sumber ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->tabungKhas->nama_tabung ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $item->jenis_jumaat ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $item->jenis_jumaat ? 'Ya' : 'Tidak' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-3">
                                    @can('update', $item)
                                        <a href="{{ route('admin.hasil.edit', $item) }}"
                                            class="text-indigo-600 hover:text-indigo-900">Ubah</a>
                                    @endcan
                                    @can('delete', $item)
                                        <form method="POST" action="{{ route('admin.hasil.destroy', $item) }}"
                                            class="inline" onsubmit="return confirm('Padam transaksi hasil ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Padam</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">Tiada transaksi
                                    hasil dijumpai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $hasil->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
