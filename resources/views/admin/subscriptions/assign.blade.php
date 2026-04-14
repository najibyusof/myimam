<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tetapkan Langganan Tenant</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold">{{ $masjid->nama }}</h3>
                    <p class="text-sm text-gray-600 mt-1">Status semasa:
                        {{ strtoupper($masjid->subscription_status ?? 'none') }}</p>
                    <p class="text-sm text-gray-600">Tamat semasa:
                        {{ $masjid->subscription_expiry?->format('d/m/Y') ?? '-' }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.subscriptions.assign.store', $masjid) }}" method="POST"
                        class="space-y-4">
                        @csrf

                        <div>
                            <label for="plan_id" class="block text-sm font-medium text-gray-700">Pelan</label>
                            <select id="plan_id" name="plan_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('plan_id') border-red-500 @enderror">
                                <option value="">-- Pilih Pelan --</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected((string) old('plan_id') === (string) $plan->id)>
                                        {{ $plan->name }} - RM {{ number_format((float) $plan->price, 2) }} /
                                        {{ $plan->billing_cycle }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Tarikh
                                    Mula</label>
                                <input type="date" id="start_date" name="start_date"
                                    value="{{ old('start_date', now()->format('Y-m-d')) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('start_date') border-red-500 @enderror">
                                @error('start_date')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Tarikh Tamat
                                    (optional)</label>
                                <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('end_date') border-red-500 @enderror">
                                @error('end_date')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                @php($status = old('status', 'active'))
                                <select id="status" name="status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-500 @enderror">
                                    <option value="active" @selected($status === 'active')>Active</option>
                                    <option value="grace" @selected($status === 'grace')>Grace</option>
                                    <option value="expired" @selected($status === 'expired')>Expired</option>
                                    <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                                </select>
                                @error('status')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="grace_days" class="block text-sm font-medium text-gray-700">Grace
                                    Days</label>
                                <input type="number" id="grace_days" name="grace_days"
                                    value="{{ old('grace_days', 7) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('grace_days') border-red-500 @enderror">
                                @error('grace_days')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="amount_paid" class="block text-sm font-medium text-gray-700">Bayaran
                                    (RM)</label>
                                <input type="number" step="0.01" id="amount_paid" name="amount_paid"
                                    value="{{ old('amount_paid', 0) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('amount_paid') border-red-500 @enderror">
                                @error('amount_paid')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="payment_reference" class="block text-sm font-medium text-gray-700">Rujukan
                                Bayaran</label>
                            <input type="text" id="payment_reference" name="payment_reference"
                                value="{{ old('payment_reference') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('payment_reference') border-red-500 @enderror">
                            @error('payment_reference')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                            <textarea id="notes" name="notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-4 border-t flex justify-between">
                            <a href="{{ route('admin.subscriptions.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Kembali</a>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Simpan
                                Langganan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
