@php
    $akaunModel = $akaun ?? null;
    $selectedJenis = old('jenis', $akaunModel->jenis ?? 'tunai');
@endphp

<div class="grid gap-4 md:grid-cols-2">
    @if (auth()->user()->hasRole('Admin'))
        <div class="md:col-span-2">
            <x-input-label for="id_masjid" :value="__('akaun.form.masjid')" />
            <select id="id_masjid" name="id_masjid"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
                <option value="">{{ __('akaun.form.select_masjid') }}</option>
                @foreach ($masjidOptions as $masjid)
                    <option value="{{ $masjid->id }}" @selected((string) old('id_masjid', $akaunModel->id_masjid ?? '') === (string) $masjid->id)>
                        {{ $masjid->nama }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('id_masjid')" class="mt-2" />
        </div>
    @else
        <input type="hidden" name="id_masjid" value="{{ old('id_masjid', auth()->user()->id_masjid) }}">
    @endif

    <div class="md:col-span-2">
        <x-input-label for="nama_akaun" :value="__('akaun.form.account_name')" />
        <x-text-input id="nama_akaun" name="nama_akaun" type="text" class="mt-1 block w-full" :value="old('nama_akaun', $akaunModel->nama_akaun ?? '')"
            required />
        <x-input-error :messages="$errors->get('nama_akaun')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="jenis" :value="__('akaun.form.account_type')" />
        <select id="jenis" name="jenis"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required>
            <option value="tunai" @selected($selectedJenis === 'tunai')>{{ __('akaun.form.cash') }} (cash)</option>
            <option value="bank" @selected($selectedJenis === 'bank')>{{ __('akaun.form.bank') }}</option>
        </select>
        <x-input-error :messages="$errors->get('jenis')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status_aktif" :value="__('akaun.form.status')" />
        <select id="status_aktif" name="status_aktif"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="1" @selected((bool) old('status_aktif', $akaunModel->status_aktif ?? true) === true)>{{ __('akaun.form.active') }}</option>
            <option value="0" @selected((bool) old('status_aktif', $akaunModel->status_aktif ?? true) === false)>{{ __('akaun.form.inactive') }}</option>
        </select>
        <x-input-error :messages="$errors->get('status_aktif')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="no_akaun" :value="__('akaun.form.bank_account_number')" />
        <x-text-input id="no_akaun" name="no_akaun" type="text" class="mt-1 block w-full" :value="old('no_akaun', $akaunModel->no_akaun ?? '')" />
        <p class="mt-1 text-xs text-gray-500">{{ __('akaun.form.bank_required_hint') }}</p>
        <x-input-error :messages="$errors->get('no_akaun')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="nama_bank" :value="__('akaun.form.bank_name')" />
        <x-text-input id="nama_bank" name="nama_bank" type="text" class="mt-1 block w-full" :value="old('nama_bank', $akaunModel->nama_bank ?? '')" />
        <p class="mt-1 text-xs text-gray-500">{{ __('akaun.form.bank_required_hint') }}</p>
        <x-input-error :messages="$errors->get('nama_bank')" class="mt-2" />
    </div>
</div>
