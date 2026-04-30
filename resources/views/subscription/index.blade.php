<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Langganan</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('payment_message'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                    {{ session('payment_message') }}
                </div>
            @endif

            @if ($currentSubscription)
                <div class="rounded-lg bg-white shadow border border-gray-100 p-5">
                    <p class="text-sm text-gray-500">Langganan Semasa</p>
                    <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ $currentSubscription->plan?->name ?? 'Pelan' }}</h3>
                            <p class="text-sm text-gray-600">
                                Status:
                                <span class="font-semibold uppercase">{{ $currentSubscription->status }}</span>
                                @if ($currentSubscription->is_trial)
                                    • Trial 7 Hari
                                @endif
                                @if ($currentSubscription->end_date)
                                    • Tamat: {{ $currentSubscription->end_date->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($plans as $plan)
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                        <p class="mt-2 text-3xl font-black text-emerald-700">RM {{ number_format($plan->price, 2) }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ $plan->duration_days }} hari</p>

                        <ul class="mt-4 space-y-2 text-sm text-gray-600">
                            @foreach ($plan->features ?? [] as $featureKey => $featureValue)
                                <li>• {{ is_string($featureKey) ? $featureKey : 'Feature' }}:
                                    {{ is_bool($featureValue) ? ($featureValue ? 'Ya' : 'Tidak') : $featureValue }}</li>
                            @endforeach
                        </ul>

                        <form method="POST" action="{{ route('subscription.subscribe', $plan->id) }}" class="mt-6">
                            @csrf
                            <label
                                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">Gateway</label>
                            <select name="gateway"
                                class="mb-3 w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="toyyibpay">ToyyibPay</option>
                                <option value="billplz">Billplz</option>
                            </select>

                            <label class="mb-3 flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="auto_renew" value="1" checked
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                Auto renew langganan
                            </label>

                            <button type="submit"
                                class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                                Langgan Sekarang
                            </button>
                        </form>

                        <form method="POST" action="{{ route('subscription.trial', $plan->id) }}" class="mt-3">
                            @csrf
                            <button type="submit"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition">
                                Mulakan Trial 7 Hari
                            </button>
                        </form>
                    </div>
                @empty
                    <div
                        class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-amber-800 md:col-span-2 xl:col-span-3">
                        Tiada pelan langganan tersedia.
                    </div>
                @endforelse
            </div>

            <div class="rounded-lg bg-white shadow border border-gray-100 p-5">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-gray-900">Status Pembayaran Terkini</h3>
                    <a href="{{ route('subscription.payments') }}" class="text-sm font-semibold text-indigo-600 hover:underline">
                        Lihat Semua Pembayaran
                    </a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-4">ID</th>
                                <th class="py-2 pr-4">Jumlah</th>
                                <th class="py-2 pr-4">Gateway</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2">Rujukan</th>
                                <th class="py-2">Invois</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPayments as $payment)
                                <tr class="border-b">
                                    <td class="py-2 pr-4">#{{ $payment->id }}</td>
                                    <td class="py-2 pr-4">RM {{ number_format($payment->amount, 2) }}</td>
                                    <td class="py-2 pr-4 uppercase">{{ $payment->gateway }}</td>
                                    <td class="py-2 pr-4">
                                        @if ($payment->status === 'paid')
                                            Berjaya ✅
                                        @elseif($payment->status === 'failed')
                                            Gagal ❌
                                        @else
                                            Menunggu ⏳
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $payment->reference_id ?? '-' }}</td>
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
                                    <td colspan="6" class="py-3 text-gray-500">Belum ada rekod pembayaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
