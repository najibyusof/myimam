@php
    $kategoriBelanjaModel = $kategoriBelanja ?? null;
@endphp

<div class="grid gap-4 md:grid-cols-2">
    @if (auth()->user()->hasRole('Admin'))
        <div class="md:col-span-2">
            <x-input-label for="id_masjid" :value="__('kategori_belanja.form.masjid')" />
            <select id="id_masjid" name="id_masjid"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
                <option value="">{{ __('kategori_belanja.form.select_masjid') }}</option>
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
        <x-input-label for="kod" :value="__('kategori_belanja.form.code')" />
        <x-text-input id="kod" name="kod" type="text" class="mt-1 block w-full" :value="old('kod', $kategoriBelanjaModel->kod ?? '')"
            :placeholder="__('kategori_belanja.form.code_placeholder')" required />
        <x-input-error :messages="$errors->get('kod')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="aktif" :value="__('kategori_belanja.form.status')" />
        <select id="aktif" name="aktif"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="1" @selected((bool) old('aktif', $kategoriBelanjaModel->aktif ?? true) === true)>{{ __('kategori_belanja.form.active') }}</option>
            <option value="0" @selected((bool) old('aktif', $kategoriBelanjaModel->aktif ?? true) === false)>{{ __('kategori_belanja.form.inactive') }}</option>
        </select>
        <x-input-error :messages="$errors->get('aktif')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="nama_kategori" :value="__('kategori_belanja.form.expense_category_name')" />
        <x-text-input id="nama_kategori" name="nama_kategori" type="text" class="mt-1 block w-full" :value="old('nama_kategori', $kategoriBelanjaModel->nama_kategori ?? '')"
            :placeholder="__('kategori_belanja.form.name_placeholder')" required />
        <x-input-error :messages="$errors->get('nama_kategori')" class="mt-2" />
    </div>
</div>
