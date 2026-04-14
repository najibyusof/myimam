@php
    $kategoriBelanjaModel = $kategoriBelanja ?? null;
@endphp

<div class="grid gap-4 md:grid-cols-2">
    @if (auth()->user()->hasRole('Admin'))
        <div class="md:col-span-2">
            <x-input-label for="id_masjid" :value="__('Masjid')" />
            <select id="id_masjid" name="id_masjid"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
                <option value="">Pilih masjid</option>
                @foreach ($masjidOptions as $masjid)
                    <option value="{{ $masjid->id }}" @selected((string) old('id_masjid', $kategoriBelanjaModel->id_masjid ?? '') === (string) $masjid->id)>
                        {{ $masjid->nama }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('id_masjid')" class="mt-2" />
        </div>
    @else
        <input type="hidden" name="id_masjid" value="{{ old('id_masjid', auth()->user()->id_masjid) }}">
    @endif

    <div>
        <x-input-label for="kod" :value="__('Kod')" />
        <x-text-input id="kod" name="kod" type="text" class="mt-1 block w-full" :value="old('kod', $kategoriBelanjaModel->kod ?? '')"
            placeholder="Contoh: UTL" required />
        <x-input-error :messages="$errors->get('kod')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="aktif" :value="__('Status')" />
        <select id="aktif" name="aktif"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="1" @selected((bool) old('aktif', $kategoriBelanjaModel->aktif ?? true) === true)>Aktif</option>
            <option value="0" @selected((bool) old('aktif', $kategoriBelanjaModel->aktif ?? true) === false)>Tidak aktif</option>
        </select>
        <x-input-error :messages="$errors->get('aktif')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="nama_kategori" :value="__('Nama Kategori Belanja')" />
        <x-text-input id="nama_kategori" name="nama_kategori" type="text" class="mt-1 block w-full" :value="old('nama_kategori', $kategoriBelanjaModel->nama_kategori ?? '')"
            placeholder="Contoh: Utiliti" required />
        <x-input-error :messages="$errors->get('nama_kategori')" class="mt-2" />
    </div>
</div>
