<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('belanja.management_title') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('belanja.management_subtitle') }}</p>
            </div>
            @can('create', \App\Models\Belanja::class)
                <a href="{{ route('admin.belanja.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    {{ __('belanja.add') }}
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
                    <p class="text-sm font-medium text-slate-500">{{ __('belanja.stats.active') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-amber-700">{{ __('belanja.stats.draft') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-900">{{ $stats['draft'] }}</p>
                </div>
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-emerald-700">{{ __('belanja.stats.submitted') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ $stats['submitted'] }}</p>
                </div>
                <div class="rounded-3xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-sky-700">{{ __('belanja.stats.linked_voucher') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-sky-900">{{ $stats['linked_baucar'] }}</p>
                </div>
            </div>

            <form method="GET" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-[220px_220px_auto_auto]">
                    <select name="status"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all" @selected($status === 'all')>{{ __('belanja.filters.all_status') }}
                        </option>
                        <option value="draft" @selected($status === 'draft')>{{ __('belanja.filters.draft') }}</option>
                        <option value="submitted" @selected($status === 'submitted')>{{ __('belanja.filters.submitted') }}
                        </option>
                    </select>
                    <select name="baucar_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="0">{{ __('belanja.filters.all_voucher') }}</option>
                        @foreach ($baucarOptions as $option)
                            <option value="{{ $option->id }}" @selected($baucarId === $option->id)>{{ $option->no_baucar }}
                            </option>
                        @endforeach
                    </select>
                    <x-primary-button>{{ __('belanja.filters.filter') }}</x-primary-button>
                    <a href="{{ route('admin.belanja.index') }}"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('belanja.filters.reset') }}
                    </a>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('belanja.table.date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('belanja.table.amount') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('belanja.table.category') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('belanja.table.account') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('belanja.table.voucher') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('belanja.table.status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('belanja.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($belanja as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ optional($item->tarikh)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">RM
                                    {{ number_format((float) $item->amaun, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $item->kategoriBelanja->nama_kategori ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->akaun->nama_akaun ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->baucar->no_baucar ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $item->status === 'DRAF' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $item->status === 'DRAF' ? __('belanja.table.draft') : __('belanja.table.submitted') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-3">
                                    @can('update', $item)
                                        <a href="{{ route('admin.belanja.edit', $item) }}"
                                            class="text-indigo-600 hover:text-indigo-900">{{ __('belanja.table.edit') }}</a>
                                    @endcan
                                    @can('delete', $item)
                                        <form method="POST" action="{{ route('admin.belanja.destroy', $item) }}"
                                            class="inline" data-confirm="{{ __('belanja.confirm_delete') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900">{{ __('belanja.table.delete') }}</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                    {{ __('belanja.table.empty') }}</td>
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
