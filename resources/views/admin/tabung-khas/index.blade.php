<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengurusan Tabung Khas</h2>
                <p class="mt-1 text-sm text-gray-500">Urus tabung khas untuk dana seperti Tabung Pembangunan dan Tabung Kebajikan, kemudian gunakan tabung aktif pada transaksi hasil atau belanja.</p>
            </div>
            @can('create', \App\Models\TabungKhas::class)
                <a href="{{ route('admin.tabung-khas.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Tambah Tabung Khas
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->has('tabung_khas'))
                <div class="rounded-md bg-rose-50 p-3 text-sm text-rose-800">{{ $errors->first('tabung_khas') }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Jumlah Tabung</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-emerald-700">Aktif</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ $stats['active'] }}</p>
                </div>
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-amber-700">Telah Diguna Transaksi</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-900">{{ $stats['linked'] }}</p>
                </div>
            </div>

            <form method="GET" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto_auto]">
                    <x-text-input name="q" type="text" class="block w-full" placeholder="Cari nama tabung" :value="$search" />
                    <select name="status"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all" @selected($status === 'all')>Semua status</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Tidak aktif</option>
                        <option value="linked" @selected($status === 'linked')>Ada transaksi</option>
                    </select>
                    <x-primary-button>Tapis</x-primary-button>
                    <a href="{{ route('admin.tabung-khas.index') }}"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama Tabung</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Masjid</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Hasil</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Belanja</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($tabungKhas as $item)
                            @php($usageCount = $item->hasil_count + $item->belanja_count)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <div>{{ $item->nama_tabung }}</div>
                                    @if ($usageCount > 0)
                                        <p class="mt-1 text-xs text-amber-700">Digunakan dalam {{ $usageCount }} transaksi</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->masjid->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->hasil_count }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->belanja_count }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $item->aktif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $item->aktif ? 'Aktif' : 'Tidak aktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-3">
                                    @can('update', $item)
                                        <a href="{{ route('admin.tabung-khas.edit', $item) }}" class="text-indigo-600 hover:text-indigo-900">Ubah</a>
                                    @endcan
                                    @can('toggleStatus', $item)
                                        <form method="POST" action="{{ route('admin.tabung-khas.status', $item) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-amber-600 hover:text-amber-900">
                                                {{ $item->aktif ? 'Nyahaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endcan
                                    @can('delete', $item)
                                        <form method="POST" action="{{ route('admin.tabung-khas.destroy', $item) }}" class="inline" onsubmit="return confirm('Padam tabung khas ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Padam</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">Tiada tabung khas dijumpai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $tabungKhas->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
