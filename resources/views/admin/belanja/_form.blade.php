@props([
    'action',
    'method' => 'POST',
    'belanjaRecord' => null,
    'masjidOptions' => collect(),
    'akaunOptions' => collect(),
    'kategoriOptions' => collect(),
    'baucarOptions' => collect(),
])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        @if (auth()->user()->hasRole('Admin'))
            <div>
                <x-input-label for="id_masjid" value="Masjid" />
                <select id="id_masjid" name="id_masjid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Pilih masjid</option>
                    @foreach ($masjidOptions as $option)
                        <option value="{{ $option->id }}" @selected(old('id_masjid', $belanjaRecord?->id_masjid) == $option->id)>{{ $option->nama }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('id_masjid')" />
            </div>
        @else
            <input type="hidden" name="id_masjid" value="{{ old('id_masjid', auth()->user()->id_masjid) }}">
        @endif

        <div>
            <x-input-label for="tarikh" value="Tarikh" />
            <x-text-input id="tarikh" name="tarikh" type="date" class="mt-1 block w-full" :value="old('tarikh', optional($belanjaRecord?->tarikh)->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('tarikh')" />
        </div>

        <div>
            <x-input-label for="amaun" value="Amaun" />
            <x-text-input id="amaun" name="amaun" type="number" min="0.01" step="0.01" class="mt-1 block w-full" :value="old('amaun', $belanjaRecord?->amaun)" required placeholder="0.00" />
            <x-input-error class="mt-2" :messages="$errors->get('amaun')" />
        </div>

        <div>
            <x-input-label for="id_akaun" value="Akaun" />
            <select id="id_akaun" name="id_akaun" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Pilih akaun</option>
                @foreach ($akaunOptions as $option)
                    <option value="{{ $option->id }}" @selected(old('id_akaun', $belanjaRecord?->id_akaun) == $option->id)>{{ $option->nama_akaun }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('id_akaun')" />
        </div>

        <div>
            <x-input-label for="id_kategori_belanja" value="Kategori Belanja" />
            <select id="id_kategori_belanja" name="id_kategori_belanja" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Pilih kategori</option>
                @foreach ($kategoriOptions as $option)
                    <option value="{{ $option->id }}" @selected(old('id_kategori_belanja', $belanjaRecord?->id_kategori_belanja) == $option->id)>{{ $option->nama_kategori }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('id_kategori_belanja')" />
        </div>

        <div>
            <x-input-label for="id_baucar" value="Baucar (Opsyenal)" />
            <select id="id_baucar" name="id_baucar" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tiada baucar</option>
                @foreach ($baucarOptions as $option)
                    <option value="{{ $option->id }}" @selected(old('id_baucar', $belanjaRecord?->id_baucar) == $option->id)>{{ $option->no_baucar }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('id_baucar')" />
        </div>

        <div>
            <x-input-label for="penerima" value="Penerima (Opsyenal)" />
            <x-text-input id="penerima" name="penerima" type="text" class="mt-1 block w-full" :value="old('penerima', $belanjaRecord?->penerima)" maxlength="190" />
            <x-input-error class="mt-2" :messages="$errors->get('penerima')" />
        </div>
    </div>

    <label class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
        <input type="checkbox" name="is_submitted" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_submitted', $belanjaRecord?->status === 'LULUS'))>
        <span class="text-sm text-slate-700">Tandakan sebagai submitted</span>
    </label>

    <div>
        <x-input-label for="catatan" value="Catatan (Opsyenal)" />
        <textarea id="catatan" name="catatan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('catatan', $belanjaRecord?->catatan) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('catatan')" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>Simpan</x-primary-button>
        <a href="{{ route('admin.belanja.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Kembali
        </a>
    </div>
</form>
