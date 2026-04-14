<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kemaskini Program Masjid</h2>
                <p class="mt-1 text-sm text-gray-500">Semak penggunaan program dalam transaksi hasil dan belanja sebelum
                    mengubah status atau memadam rekod.</p>
            </div>
            <span
                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $programMasjid->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                {{ $programMasjid->aktif ? 'Aktif' : 'Tidak Aktif' }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->has('program_masjid'))
                <div class="rounded-md bg-rose-50 p-3 text-sm text-rose-800">{{ $errors->first('program_masjid') }}</div>
            @endif

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                @include('admin.program-masjid._form', [
                    'action' => route('admin.program-masjid.update', $programMasjid),
                    'method' => 'PUT',
                    'programMasjid' => $programMasjid,
                    'masjidOptions' => $masjidOptions,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
