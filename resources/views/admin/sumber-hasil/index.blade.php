<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('sumber_hasil.management_title') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('sumber_hasil.management_subtitle') }}</p>
            </div>
            @can('create', \App\Models\SumberHasil::class)
                <a href="{{ route('admin.sumber-hasil.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    {{ __('sumber_hasil.add') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ __('sumber_hasil.stats.total_sources') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-emerald-700">{{ __('sumber_hasil.stats.active') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ $stats['active'] }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ __('sumber_hasil.stats.inactive') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['inactive'] }}</p>
                </div>
            </div>

            <form method="GET" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_180px_auto_auto]">
                    <x-text-input name="q" type="text" class="block w-full" :placeholder="__('sumber_hasil.filters.search_placeholder')"
                        :value="$search" />
                    <select name="jenis"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('sumber_hasil.filters.all_types') }}</option>
                        @foreach ($jenisOptions as $option)
                            <option value="{{ $option }}" @selected($jenis === $option)>{{ $option }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all" @selected($status === 'all')>{{ __('sumber_hasil.filters.all_status') }}
                        </option>
                        <option value="active" @selected($status === 'active')>{{ __('sumber_hasil.filters.active') }}
                        </option>
                        <option value="inactive" @selected($status === 'inactive')>{{ __('sumber_hasil.filters.inactive') }}
                        </option>
                    </select>
                    <x-primary-button>{{ __('sumber_hasil.filters.filter') }}</x-primary-button>
                    <a href="{{ route('admin.sumber-hasil.index') }}"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('sumber_hasil.filters.reset') }}
                    </a>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('sumber_hasil.table.code') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('sumber_hasil.table.source_name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('sumber_hasil.table.type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('sumber_hasil.table.masjid') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('sumber_hasil.table.status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('sumber_hasil.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($sumberHasil as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->kod }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->nama_sumber }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->jenis }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->masjid->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $item->aktif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $item->aktif ? __('sumber_hasil.table.active') : __('sumber_hasil.table.inactive') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-3">
                                    @can('update', $item)
                                        <a href="{{ route('admin.sumber-hasil.edit', $item) }}"
                                            class="text-indigo-600 hover:text-indigo-900">{{ __('sumber_hasil.table.edit') }}</a>
                                    @endcan
                                    @can('toggleStatus', $item)
                                        <form method="POST" action="{{ route('admin.sumber-hasil.status', $item) }}"
                                            class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-amber-600 hover:text-amber-900">
                                                {{ $item->aktif ? __('sumber_hasil.table.deactivate') : __('sumber_hasil.table.activate') }}
                                            </button>
                                        </form>
                                    @endcan
                                    @can('delete', $item)
                                        <form method="POST" action="{{ route('admin.sumber-hasil.destroy', $item) }}"
                                            class="inline"
                                            onsubmit="return confirm('{{ __('sumber_hasil.confirm_delete') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900">{{ __('sumber_hasil.table.delete') }}</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                    {{ __('sumber_hasil.table.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $sumberHasil->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
