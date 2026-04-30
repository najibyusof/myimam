<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Pelan Langganan</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('sync_status'))
                <div class="mb-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    {{ session('sync_status') }}
                    @if (session('created_plan_name'))
                        <span class="font-medium">({{ session('created_plan_name') }})</span>
                    @endif
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.subscriptions.plans.update', $plan) }}" method="POST"
                        class="space-y-4">
                        @csrf
                        @method('PUT')

                        @include('admin.subscriptions.plan-form', ['plan' => $plan])

                        <div class="pt-4 border-t flex justify-between">
                            <a href="{{ route('admin.subscriptions.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Kembali</a>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Kemaskini
                                Pelan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
