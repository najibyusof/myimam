<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('hasil.management_title') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('hasil.management_subtitle') }}</p>
            </div>
            @can('create', \App\Models\Hasil::class)
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.hasil.create') }}"
                        class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                        {{ __('hasil.add') }}
                    </a>
                    <a href="{{ route('admin.hasil.jumaat.create') }}"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        {{ __('hasil.add_jumaat') }}
                    </a>
                </div>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-4 sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ __('hasil.stats.total_transactions') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-emerald-700">{{ __('hasil.stats.total_amount') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-900">RM {{ number_format($stats['jumlah'], 2) }}
                    </p>
                </div>
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-amber-700">{{ __('hasil.stats.jumaat_collection') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-900">{{ $stats['jumaat'] }}</p>
                </div>
            </div>

            <form method="GET" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-[160px_160px_220px_180px_auto_auto]">
                    <x-text-input name="from" type="date" class="block w-full" :value="$from" />
                    <x-text-input name="to" type="date" class="block w-full" :value="$to" />
                    <select name="akaun_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="0">{{ __('hasil.filters.all_accounts') }}</option>
                        @foreach ($akaunOptions as $option)
                            <option value="{{ $option->id }}" @selected($akaunId === $option->id)>{{ $option->nama_akaun }}
                            </option>
                        @endforeach
                    </select>
                    <select name="jumaat"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all" @selected($jumaat === 'all')>{{ __('hasil.filters.all_collections') }}
                        </option>
                        <option value="yes" @selected($jumaat === 'yes')>{{ __('hasil.filters.jumaat_only') }}
                        </option>
                        <option value="no" @selected($jumaat === 'no')>{{ __('hasil.filters.non_jumaat') }}
                        </option>
                    </select>
                    <x-primary-button>{{ __('hasil.filters.filter') }}</x-primary-button>
                    <a href="{{ route('admin.hasil.index') }}"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('hasil.filters.reset') }}
                    </a>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('hasil.table.date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('hasil.table.amount') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('hasil.table.account') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('hasil.table.source') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('hasil.table.fund') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('hasil.table.jumaat') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('hasil.table.actions') }}</th>
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
                                        {{ $item->jenis_jumaat ? __('hasil.table.yes') : __('hasil.table.no') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-3">
                                    @can('update', $item)
                                        <a href="{{ $item->jenis_jumaat ? route('admin.hasil.jumaat.edit', $item) : route('admin.hasil.edit', $item) }}"
                                            class="text-indigo-600 hover:text-indigo-900">{{ __('hasil.table.edit') }}</a>
                                    @endcan
                                    @can('delete', $item)
                                        <form method="POST" action="{{ route('admin.hasil.destroy', $item) }}"
                                            class="inline" onsubmit="return confirm('{{ __('hasil.confirm_delete') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900">{{ __('hasil.table.delete') }}</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                    {{ __('hasil.table.empty') }}</td>
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
