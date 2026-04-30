<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Status Pembayaran</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white border border-gray-200 shadow p-6 space-y-3">
                <p class="text-sm text-gray-500">Payment #{{ $payment->id }}</p>

                @if ($payment->status === 'paid')
                    <p class="text-2xl font-bold text-emerald-700">Berjaya ✅</p>
                @elseif ($payment->status === 'failed')
                    <p class="text-2xl font-bold text-red-700">Gagal ❌</p>
                @else
                    <p class="text-2xl font-bold text-amber-700">Menunggu ⏳</p>
                @endif

                <div class="text-sm text-gray-700 space-y-1">
                    <p>Pelan: {{ $payment->subscription?->plan?->name ?? '-' }}</p>
                    <p>Jumlah: RM {{ number_format($payment->amount, 2) }}</p>
                    <p>Gateway: {{ strtoupper($payment->gateway) }}</p>
                    <p>Rujukan: {{ $payment->reference_id ?? '-' }}</p>
                </div>

                <a href="{{ route('subscription.index') }}"
                    class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                    Kembali ke Langganan
                </a>

                @if ($payment->status === 'paid')
                    <a href="{{ route('subscription.invoice.download', $payment) }}"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 ml-2">
                        Muat Turun Invois PDF
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
