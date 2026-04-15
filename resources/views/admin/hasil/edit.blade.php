<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('hasil.edit_title') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('hasil.edit_subtitle') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                @include('admin.hasil._form', [
                    'action' => route('admin.hasil.update', $hasilRecord),
                    'method' => 'PUT',
                    'hasilRecord' => $hasilRecord,
                    'masjidOptions' => $masjidOptions,
                    'akaunOptions' => $akaunOptions,
                    'sumberHasilOptions' => $sumberHasilOptions,
                    'tabungKhasOptions' => $tabungKhasOptions,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
