<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('program_masjid.add_title') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('program_masjid.add_subtitle') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                @include('admin.program-masjid._form', [
                    'action' => route('admin.program-masjid.store'),
                    'method' => 'POST',
                    'programMasjid' => null,
                    'masjidOptions' => $masjidOptions,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
