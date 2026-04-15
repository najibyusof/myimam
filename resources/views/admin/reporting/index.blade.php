<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">{{ __('laporan.title') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow rounded-xl p-5">
                <form method="GET" action="{{ route('admin.reporting.index') }}"
                    class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    @if ($isAdmin)
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">{{ __('laporan.filters.masjid') }}</label>
                            <select name="masjid_id"
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">{{ __('laporan.filters.all') }}</option>
                                @foreach ($masjidOptions as $m)
                                    <option value="{{ $m->id }}" @selected(($filters['masjid_id'] ?? null) === $m->id)>{{ $m->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 mb-1">{{ __('laporan.filters.account') }}</label>
                        <select name="akaun_id"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">{{ __('laporan.filters.all') }}</option>
                            @foreach ($akaunOptions as $a)
                                <option value="{{ $a->id }}" @selected(($filters['akaun_id'] ?? null) === $a->id)>{{ $a->nama_akaun }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 mb-1">{{ __('laporan.filters.date_from') }}</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 mb-1">{{ __('laporan.filters.date_to') }}</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 rounded-lg transition">{{ __('laporan.filters.filter') }}</button>
                        <a href="{{ route('admin.reporting.index') }}"
                            class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 rounded-lg transition">{{ __('laporan.filters.reset') }}</a>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-xl shadow p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">{{ __('laporan.cards.income') }}</p>
                    <p class="text-2xl font-bold text-emerald-700">RM
                        {{ number_format($incomeExpense['income_total'], 2) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">{{ __('laporan.cards.expense') }}</p>
                    <p class="text-2xl font-bold text-rose-700">RM
                        {{ number_format($incomeExpense['expense_total'], 2) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">{{ __('laporan.cards.net') }}</p>
                    <p
                        class="text-2xl font-bold {{ $incomeExpense['net_balance'] >= 0 ? 'text-indigo-700' : 'text-amber-700' }}">
                        RM {{ number_format($incomeExpense['net_balance'], 2) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">
                        {{ __('laporan.cards.approved_voucher') }}</p>
                    <p class="text-2xl font-bold text-sky-700">RM
                        {{ number_format($incomeExpense['voucher_total'], 2) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">{{ __('laporan.cards.transfer') }}</p>
                    <p class="text-2xl font-bold text-purple-700">RM
                        {{ number_format($incomeExpense['transfer_total'], 2) }}</p>
                </div>
            </div>

            <div class="bg-white shadow rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">{{ __('laporan.account_summary') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.account') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.income') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.expense') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.voucher') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.in') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.out') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.balance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($accountRows as $row)
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-gray-800">{{ $row['akaun_nama'] }}</span>
                                        <span class="block text-xs text-gray-500">{{ $row['akaun_jenis'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-emerald-700">RM
                                        {{ number_format($row['income_total'], 2) }}</td>
                                    <td class="px-4 py-3 text-rose-700">RM
                                        {{ number_format($row['expense_total'], 2) }}</td>
                                    <td class="px-4 py-3 text-sky-700">RM {{ number_format($row['voucher_total'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-indigo-700">RM
                                        {{ number_format($row['transfer_in'], 2) }}</td>
                                    <td class="px-4 py-3 text-amber-700">RM
                                        {{ number_format($row['transfer_out'], 2) }}</td>
                                    <td
                                        class="px-4 py-3 font-semibold {{ $row['balance'] >= 0 ? 'text-gray-800' : 'text-amber-700' }}">
                                        RM {{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                        {{ __('laporan.table.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">{{ __('laporan.monthly_report') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.month') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.income') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.expense') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.net') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.voucher') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('laporan.table.transfer') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($monthlyRows as $row)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $row['month'] }}</td>
                                    <td class="px-4 py-3 text-emerald-700">RM
                                        {{ number_format($row['income_total'], 2) }}</td>
                                    <td class="px-4 py-3 text-rose-700">RM
                                        {{ number_format($row['expense_total'], 2) }}</td>
                                    <td
                                        class="px-4 py-3 {{ $row['net'] >= 0 ? 'text-indigo-700' : 'text-amber-700' }}">
                                        RM {{ number_format($row['net'], 2) }}</td>
                                    <td class="px-4 py-3 text-sky-700">RM {{ number_format($row['voucher_total'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-purple-700">RM
                                        {{ number_format($row['transfer_total'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                        {{ __('laporan.table.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
