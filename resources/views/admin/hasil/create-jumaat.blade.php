<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('hasil.add_jumaat_title') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('hasil.add_jumaat_subtitle') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-amber-200 bg-white p-6 shadow-sm">
                @include('admin.hasil._form', [
                    'action' => route('admin.hasil.jumaat.store'),
                    'method' => 'POST',
                    'hasilRecord' => null,
                    'formMode' => 'jumaat',
                    'masjidOptions' => $masjidOptions,
                    'akaunOptions' => $akaunOptions,
                    'sumberHasilOptions' => $sumberHasilOptions,
                    'tabungKhasOptions' => $tabungKhasOptions,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
