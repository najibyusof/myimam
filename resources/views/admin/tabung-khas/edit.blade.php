<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kemaskini Tabung Khas</h2>
                <p class="mt-1 text-sm text-gray-500">Pantau penggunaan tabung dalam transaksi hasil dan belanja sebelum membuat perubahan atau pemadaman.</p>
            </div>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $tabungKhas->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                {{ $tabungKhas->aktif ? 'Aktif' : 'Tidak Aktif' }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->has('tabung_khas'))
                <div class="rounded-md bg-rose-50 p-3 text-sm text-rose-800">{{ $errors->first('tabung_khas') }}</div>
            @endif

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                @include('admin.tabung-khas._form', [
                    'action' => route('admin.tabung-khas.update', $tabungKhas),
                    'method' => 'PUT',
                    'tabungKhas' => $tabungKhas,
                    'masjidOptions' => $masjidOptions,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
