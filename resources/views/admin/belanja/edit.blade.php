<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('belanja.edit_title') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('belanja.edit_subtitle') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                @include('admin.belanja._form', [
                    'action' => route('admin.belanja.update', $belanjaRecord),
                    'method' => 'PUT',
                    'belanjaRecord' => $belanjaRecord,
                    'masjidOptions' => $masjidOptions,
                    'akaunOptions' => $akaunOptions,
                    'kategoriOptions' => $kategoriOptions,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
