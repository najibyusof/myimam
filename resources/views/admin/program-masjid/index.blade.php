<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('program_masjid.management_title') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('program_masjid.management_subtitle') }}</p>
            </div>
            @can('create', \App\Models\ProgramMasjid::class)
                <a href="{{ route('admin.program-masjid.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    {{ __('program_masjid.add') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->has('program_masjid'))
                <div class="rounded-md bg-rose-50 p-3 text-sm text-rose-800">{{ $errors->first('program_masjid') }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ __('program_masjid.stats.total_programs') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-emerald-700">{{ __('program_masjid.stats.active') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ $stats['active'] }}</p>
                </div>
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-amber-700">{{ __('program_masjid.stats.linked_transactions') }}
                    </p>
                    <p class="mt-2 text-3xl font-semibold text-amber-900">{{ $stats['linked'] }}</p>
                </div>
            </div>

            <form method="GET" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto_auto]">
                    <x-text-input name="q" type="text" class="block w-full" :placeholder="__('program_masjid.filters.search_placeholder')"
                        :value="$search" />
                    <select name="status"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all" @selected($status === 'all')>
                            {{ __('program_masjid.filters.all_status') }}</option>
                        <option value="active" @selected($status === 'active')>{{ __('program_masjid.filters.active') }}
                        </option>
                        <option value="inactive" @selected($status === 'inactive')>
                            {{ __('program_masjid.filters.inactive') }}</option>
                        <option value="linked" @selected($status === 'linked')>{{ __('program_masjid.filters.linked') }}
                        </option>
                    </select>
                    <x-primary-button>{{ __('program_masjid.filters.filter') }}</x-primary-button>
                    <a href="{{ route('admin.program-masjid.index') }}"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('program_masjid.filters.reset') }}
                    </a>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('program_masjid.table.program_name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('program_masjid.table.masjid') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('program_masjid.table.hasil') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('program_masjid.table.belanja') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('program_masjid.table.status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('program_masjid.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($programMasjid as $item)
                            @php($usageCount = $item->hasil_count + $item->belanja_count)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <div>{{ $item->nama_program }}</div>
                                    @if ($usageCount > 0)
                                        <p class="mt-1 text-xs text-amber-700">
                                            {{ __('program_masjid.table.linked_in_transactions', ['count' => $usageCount]) }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->masjid->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->hasil_count }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->belanja_count }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $item->aktif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $item->aktif ? __('program_masjid.table.active') : __('program_masjid.table.inactive') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-3">
                                    @can('update', $item)
                                        <a href="{{ route('admin.program-masjid.edit', $item) }}"
                                            class="text-indigo-600 hover:text-indigo-900">{{ __('program_masjid.table.edit') }}</a>
                                    @endcan
                                    @can('toggleStatus', $item)
                                        <form method="POST" action="{{ route('admin.program-masjid.status', $item) }}"
                                            class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-amber-600 hover:text-amber-900">
                                                {{ $item->aktif ? __('program_masjid.table.deactivate') : __('program_masjid.table.activate') }}
                                            </button>
                                        </form>
                                    @endcan
                                    @can('delete', $item)
                                        <form method="POST" action="{{ route('admin.program-masjid.destroy', $item) }}"
                                            class="inline" data-confirm="{{ __('program_masjid.confirm_delete') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900">{{ __('program_masjid.table.delete') }}</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                    {{ __('program_masjid.table.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $programMasjid->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
