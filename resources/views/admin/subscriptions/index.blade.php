<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengurusan Langganan') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Pelan Langganan</h3>
                        <a href="{{ route('admin.subscriptions.plans.create') }}"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Tambah Pelan
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Pelan
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Harga
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Tempoh
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Pengguna
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Tindakan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($plans as $plan)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ $plan->name }}
                                            <div class="text-xs text-gray-500">{{ $plan->slug }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">RM
                                            {{ number_format((float) $plan->price, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $plan->duration_months }} bulan
                                            ({{ $plan->billing_cycle }})</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $plan->tenant_subscriptions_count }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($plan->is_active)
                                                <span
                                                    class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Aktif</span>
                                            @else
                                                <span
                                                    class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700">Tidak
                                                    aktif</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <a href="{{ route('admin.subscriptions.plans.edit', $plan) }}"
                                                class="text-indigo-600 hover:text-indigo-900">
                                                Ubah
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-sm text-gray-500 text-center">Tiada
                                            pelan langganan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Langganan Tenant</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Masjid
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Pelan
                                        Aktif</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Tamat
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Tindakan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($masjids as $masjid)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $masjid->nama }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $masjid->activeSubscription?->plan?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ strtoupper($masjid->subscription_status ?? 'none') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $masjid->subscription_expiry?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <a href="{{ route('admin.subscriptions.assign', $masjid) }}"
                                                class="text-indigo-600 hover:text-indigo-900">
                                                Tetapkan Langganan
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-sm text-gray-500 text-center">Tiada
                                            tenant dijumpai.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $masjids->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
