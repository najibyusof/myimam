<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('belanja.add_title') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('belanja.add_subtitle') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                @include('admin.belanja._form', [
                    'action' => route('admin.belanja.store'),
                    'method' => 'POST',
                    'belanjaRecord' => null,
                    'masjidOptions' => $masjidOptions,
                    'akaunOptions' => $akaunOptions,
                    'kategoriOptions' => $kategoriOptions,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
