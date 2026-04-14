<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengurusan Belanja</h2>
                <p class="mt-1 text-sm text-gray-500">Urus rekod belanja aktif, bezakan draf dan submitted, serta tapis mengikut baucar.</p>
            </div>
            @can('create', \App\Models\Belanja::class)
                <a href="{{ route('admin.belanja.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Tambah Belanja
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Aktif</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-amber-700">Draf</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-900">{{ $stats['draft'] }}</p>
                </div>
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-emerald-700">Submitted</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ $stats['submitted'] }}</p>
                </div>
                <div class="rounded-3xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-sky-700">Pautan Baucar</p>
                    <p class="mt-2 text-3xl font-semibold text-sky-900">{{ $stats['linked_baucar'] }}</p>
                </div>
            </div>

            <form method="GET" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-[220px_220px_auto_auto]">
                    <select name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all" @selected($status === 'all')>Semua status</option>
                        <option value="draft" @selected($status === 'draft')>Draf</option>
                        <option value="submitted" @selected($status === 'submitted')>Submitted</option>
                    </select>
                    <select name="baucar_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="0">Semua baucar</option>
                        @foreach ($baucarOptions as $option)
                            <option value="{{ $option->id }}" @selected($baucarId === $option->id)>{{ $option->no_baucar }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>Tapis</x-primary-button>
                    <a href="{{ route('admin.belanja.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tarikh</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Amaun</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Akaun</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Baucar</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($belanja as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ optional($item->tarikh)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">RM {{ number_format((float) $item->amaun, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->kategoriBelanja->nama_kategori ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->akaun->nama_akaun ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->baucar->no_baucar ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $item->status === 'DRAF' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $item->status === 'DRAF' ? 'Draf' : 'Submitted' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-3">
                                    @can('update', $item)
                                        <a href="{{ route('admin.belanja.edit', $item) }}" class="text-indigo-600 hover:text-indigo-900">Ubah</a>
                                    @endcan
                                    @can('delete', $item)
                                        <form method="POST" action="{{ route('admin.belanja.destroy', $item) }}" class="inline" onsubmit="return confirm('Padam rekod belanja ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Padam</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">Tiada rekod belanja aktif dijumpai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $belanja->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
