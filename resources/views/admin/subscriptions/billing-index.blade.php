<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Superadmin Billing Control</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-xl bg-white border border-gray-200 shadow p-5">
                <h3 class="text-lg font-semibold text-gray-900">Semua Langganan</h3>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-gray-500">
                                <th class="py-2 pr-4">Tenant</th>
                                <th class="py-2 pr-4">Pelan</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Mula</th>
                                <th class="py-2 pr-4">Tamat</th>
                                <th class="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subscriptions as $subscription)
                                <tr class="border-b align-top">
                                    <td class="py-3 pr-4">
                                        <div class="font-semibold text-gray-900">{{ $subscription->tenant?->nama }}
                                        </div>
                                        <div class="text-xs text-gray-500">ID: {{ $subscription->tenant_id }}</div>
                                    </td>
                                    <td class="py-3 pr-4">{{ $subscription->plan?->name ?? '-' }}</td>
                                    <td class="py-3 pr-4 uppercase">{{ $subscription->status }}</td>
                                    <td class="py-3 pr-4">
                                        {{ optional($subscription->start_date)?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="py-3 pr-4">
                                        {{ optional($subscription->end_date)?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="py-3">
                                        <div class="flex flex-col gap-2">
                                            <form method="POST"
                                                action="{{ route('superadmin.subscriptions.toggle-tenant', $subscription->tenant_id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="suspended">
                                                <button
                                                    class="rounded bg-red-600 px-3 py-1 text-xs font-semibold text-white">Disable
                                                    Tenant</button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('superadmin.subscriptions.toggle-tenant', $subscription->tenant_id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="active">
                                                <button
                                                    class="rounded bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">Enable
                                                    Tenant</button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('superadmin.subscriptions.override', $subscription->tenant_id) }}">
                                                @csrf
                                                <input type="hidden" name="plan_id"
                                                    value="{{ $subscription->plan_id }}">
                                                <input type="hidden" name="status" value="active">
                                                <button
                                                    class="rounded bg-slate-700 px-3 py-1 text-xs font-semibold text-white">Override
                                                    Active</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $subscriptions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
