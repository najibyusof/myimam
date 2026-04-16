<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('akaun.management_title') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('akaun.management_subtitle') }}</p>
            </div>
            @can('create', \App\Models\Akaun::class)
                <a href="{{ route('admin.akaun.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    {{ __('akaun.add') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ __('akaun.stats.total_accounts') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-emerald-700">{{ __('akaun.stats.active_accounts') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ $stats['active'] }}</p>
                </div>
                <div class="rounded-3xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-sky-700">{{ __('akaun.stats.cash_type') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-sky-900">{{ $stats['tunai'] }}</p>
                </div>
                <div class="rounded-3xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-indigo-700">{{ __('akaun.stats.bank_type') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-indigo-900">{{ $stats['bank'] }}</p>
                </div>
            </div>

            <form method="GET" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_180px_auto_auto]">
                    <x-text-input name="q" type="text" class="block w-full" :placeholder="__('akaun.filters.search_placeholder')"
                        :value="$search" />
                    <select name="jenis"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all" @selected($jenis === 'all')>{{ __('akaun.filters.all_types') }}
                        </option>
                        <option value="tunai" @selected($jenis === 'tunai')>{{ __('akaun.filters.cash') }}</option>
                        <option value="bank" @selected($jenis === 'bank')>{{ __('akaun.filters.bank') }}</option>
                    </select>
                    <select name="status"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all" @selected($status === 'all')>{{ __('akaun.filters.all_status') }}
                        </option>
                        <option value="active" @selected($status === 'active')>{{ __('akaun.filters.active') }}</option>
                        <option value="inactive" @selected($status === 'inactive')>{{ __('akaun.filters.inactive') }}
                        </option>
                    </select>
                    <x-primary-button>{{ __('akaun.filters.filter') }}</x-primary-button>
                    <a href="{{ route('admin.akaun.index') }}"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('akaun.filters.reset') }}
                    </a>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('akaun.table.account_name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('akaun.table.type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('akaun.table.account_or_bank') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('akaun.table.masjid') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('akaun.table.status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('akaun.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($akaun as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->nama_akaun }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $item->jenis === 'tunai' ? 'bg-sky-100 text-sky-700' : 'bg-indigo-100 text-indigo-700' }}">
                                        {{ strtoupper($item->jenis) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    @if ($item->jenis === 'bank')
                                        <p>{{ $item->no_akaun ?: '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->nama_bank ?: '-' }}</p>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->masjid->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $item->status_aktif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $item->status_aktif ? __('akaun.table.active') : __('akaun.table.inactive') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-3">
                                    @can('update', $item)
                                        <a href="{{ route('admin.akaun.edit', $item) }}"
                                            class="text-indigo-600 hover:text-indigo-900">{{ __('akaun.table.edit') }}</a>
                                    @endcan
                                    @can('delete', $item)
                                        <form method="POST" action="{{ route('admin.akaun.destroy', $item) }}"
                                            class="inline" data-confirm="{{ __('akaun.confirm_delete') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900">{{ __('akaun.table.delete') }}</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                    {{ __('akaun.table.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $akaun->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
