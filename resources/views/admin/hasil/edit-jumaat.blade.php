<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('hasil.edit_jumaat_title') }}</h2>
            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                {{ __('hasil.badge.jumaat') }}
            </span>
        </div>
        <div>
            <p class="mt-1 text-sm text-gray-500">{{ __('hasil.edit_jumaat_subtitle') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-4 sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="overflow-hidden rounded-3xl border border-amber-200 bg-white p-6 shadow-sm">
                @include('admin.hasil._form', [
                    'action' => route('admin.hasil.jumaat.update', $hasilRecord),
                    'method' => 'PUT',
                    'hasilRecord' => $hasilRecord,
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
