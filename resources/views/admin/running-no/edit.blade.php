<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                {{ __('Kemaskini Kaunter') }}
            </h2>
            <a href="{{ route('admin.running-no.index') }}"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                &larr; Senarai Kaunter
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Current counter info --}}
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-indigo-500 uppercase tracking-wider font-semibold mb-0.5">Prefix</p>
                    <p class="font-mono font-bold text-indigo-800 text-lg">{{ $record->prefix }}</p>
                </div>
                <div>
                    <p class="text-xs text-indigo-500 uppercase tracking-wider font-semibold mb-0.5">Tempoh</p>
                    <p class="font-semibold text-indigo-800">
                        {{ $record->tahun }} / {{ str_pad($record->bulan, 2, '0', STR_PAD_LEFT) }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-indigo-500 uppercase tracking-wider font-semibold mb-0.5">Masjid</p>
                    <p class="text-indigo-800">{{ $record->masjid?->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-indigo-500 uppercase tracking-wider font-semibold mb-0.5">Kaunter Semasa</p>
                    <p class="font-bold text-indigo-800">{{ $record->last_no }}</p>
                </div>
            </div>

            <div class="bg-white shadow rounded-xl p-6">
                <p class="text-sm text-gray-600 mb-5">
                    Nombor seterusnya yang akan dijana ialah
                    <span class="font-mono font-semibold text-indigo-700">
                        {{ sprintf(
                            '%s-%s%s-%03d',
                            $record->prefix,
                            substr((string) $record->tahun, -2),
                            str_pad($record->bulan, 2, '0', STR_PAD_LEFT),
                            $record->last_no + 1,
                        ) }}
                    </span>.
                    Kemaskini medan di bawah untuk membetulkan kaunter.
                </p>

                <form method="POST"
                    action="{{ route('admin.running-no.update', [$record->id_masjid, $record->prefix, $record->tahun, $record->bulan]) }}"
                    class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="last_no" class="block text-sm font-medium text-gray-700 mb-1">
                            Kaunter Terakhir (last_no)
                        </label>
                        <input type="number" id="last_no" name="last_no"
                            value="{{ old('last_no', $record->last_no) }}" min="0"
                            class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error('last_no') border-red-400 @enderror">
                        @error('last_no')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Tetapkan ke 0 untuk mula semula dari 001 pada penjanaan seterusnya.
                        </p>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.running-no.index') }}"
                            class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-lg transition text-sm">
                            Batal
                        </a>
                    </div>
                </form>
            </div>

            <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-800">
                <strong>Amaran:</strong> Mengubah kaunter boleh menyebabkan nombor rujukan bertindih.
                Hanya kemaskini sekiranya terdapat ralat data yang perlu dibetulkan.
            </div>

        </div>
    </div>
</x-app-layout>
