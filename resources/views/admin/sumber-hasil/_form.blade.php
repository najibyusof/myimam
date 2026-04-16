@php
    $sumberHasilModel = $sumberHasil ?? null;
@endphp

<div class="grid gap-4 md:grid-cols-2">
    @if (auth()->user()->hasRole('Superadmin'))
        <div class="md:col-span-2">
            <x-input-label for="id_masjid" :value="__('sumber_hasil.form.masjid')" />
            <select id="id_masjid" name="id_masjid"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
                <option value="">{{ __('sumber_hasil.form.select_masjid') }}</option>
                @foreach ($masjidOptions as $masjid)
                    <option value="{{ $masjid->id }}" @selected((string) old('id_masjid', $sumberHasilModel->id_masjid ?? '') === (string) $masjid->id)>
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
        <x-input-label for="kod" :value="__('sumber_hasil.form.code')" />
        <x-text-input id="kod" name="kod" type="text" class="mt-1 block w-full" :value="old('kod', $sumberHasilModel->kod ?? '')"
            required />
        <x-input-error :messages="$errors->get('kod')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="jenis" :value="__('sumber_hasil.form.type')" />
        <x-text-input id="jenis" name="jenis" type="text" class="mt-1 block w-full" :value="old('jenis', $sumberHasilModel->jenis ?? '')"
            :placeholder="__('sumber_hasil.form.type_placeholder')" required />
        <x-input-error :messages="$errors->get('jenis')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="nama_sumber" :value="__('sumber_hasil.form.source_name')" />
        <x-text-input id="nama_sumber" name="nama_sumber" type="text" class="mt-1 block w-full" :value="old('nama_sumber', $sumberHasilModel->nama_sumber ?? '')"
            :placeholder="__('sumber_hasil.form.source_name_placeholder')" required />
        <x-input-error :messages="$errors->get('nama_sumber')" class="mt-2" />
    </div>

    @if (!($sumberHasilModel->is_baseline ?? false) || auth()->user()->hasRole('Superadmin'))
        <div>
            <x-input-label for="aktif" :value="__('sumber_hasil.form.status')" />
            <select id="aktif" name="aktif"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="1" @selected((bool) old('aktif', $sumberHasilModel->aktif ?? true) === true)>{{ __('sumber_hasil.form.active') }}</option>
                <option value="0" @selected((bool) old('aktif', $sumberHasilModel->aktif ?? true) === false)>{{ __('sumber_hasil.form.inactive') }}</option>
            </select>
            <x-input-error :messages="$errors->get('aktif')" class="mt-2" />
        </div>
    @else
        {{-- Baseline record: non-superadmin cannot change status; lock it to active --}}
        <input type="hidden" name="aktif" value="1">
    @endif
</div>
