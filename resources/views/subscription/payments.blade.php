<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sejarah Pembayaran</h2>
            <a href="{{ route('subscription.index') }}"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                Kembali ke Langganan
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-lg bg-white shadow border border-gray-100 p-5">
                <form method="GET" action="{{ route('subscription.payments') }}"
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Semua</option>
                            <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Menunggu</option>
                            <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Berjaya</option>
                            <option value="failed" @selected(($filters['status'] ?? '') === 'failed')>Gagal</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Gateway</label>
                        <select name="gateway" class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Semua</option>
                            <option value="toyyibpay" @selected(($filters['gateway'] ?? '') === 'toyyibpay')>ToyyibPay</option>
                            <option value="billplz" @selected(($filters['gateway'] ?? '') === 'billplz')>Billplz</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Dari Tarikh</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Hingga Tarikh</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Tapis
                        </button>
                        <a href="{{ route('subscription.payments') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="rounded-lg bg-white shadow border border-gray-100 p-5">
                <h3 class="text-lg font-semibold text-gray-900">Rekod Pembayaran Tenant</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-4">ID</th>
                                <th class="py-2 pr-4">Tarikh</th>
                                <th class="py-2 pr-4">Pelan</th>
                                <th class="py-2 pr-4">Jumlah</th>
                                <th class="py-2 pr-4">Gateway</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Rujukan</th>
                                <th class="py-2 pr-4">Status Halaman</th>
                                <th class="py-2">Invois</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr class="border-b">
                                    <td class="py-2 pr-4">#{{ $payment->id }}</td>
                                    <td class="py-2 pr-4">{{ $payment->created_at?->format('d M Y H:i') }}</td>
                                    <td class="py-2 pr-4">{{ $payment->subscription?->plan?->name ?? '-' }}</td>
                                    <td class="py-2 pr-4">RM {{ number_format((float) $payment->amount, 2) }}</td>
                                    <td class="py-2 pr-4 uppercase">{{ $payment->gateway }}</td>
                                    <td class="py-2 pr-4">
                                        @if ($payment->status === 'paid')
                                            <span class="text-emerald-700 font-semibold">Berjaya</span>
                                        @elseif($payment->status === 'failed')
                                            <span class="text-red-700 font-semibold">Gagal</span>
                                        @else
                                            <span class="text-amber-700 font-semibold">Menunggu</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-4">{{ $payment->reference_id ?? '-' }}</td>
                                    <td class="py-2 pr-4">
                                        <a href="{{ route('subscription.status', $payment) }}" class="text-indigo-600 hover:underline">
                                            Lihat
                                        </a>
                                    </td>
                                    <td class="py-2">
                                        @if ($payment->status === 'paid')
                                            <a href="{{ route('subscription.invoice.download', $payment) }}"
                                                class="text-emerald-700 hover:underline">Muat Turun</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-3 text-gray-500">Belum ada rekod pembayaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>