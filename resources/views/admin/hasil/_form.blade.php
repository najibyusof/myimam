@props([
    'action',
    'method' => 'POST',
    'hasilRecord' => null,
    'formMode' => 'regular',
    'masjidOptions' => collect(),
    'akaunOptions' => collect(),
    'sumberHasilOptions' => collect(),
    'tabungKhasOptions' => collect(),
])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($hasilRecord?->no_resit)
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                {{ __('hasil.receipt.receipt_no') }}</p>
            <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ $hasilRecord->no_resit }}</h3>
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        @if (auth()->user()->hasRole('Superadmin'))
            <div>
                <x-input-label for="id_masjid" :value="__('hasil.form.masjid')" />
                <select id="id_masjid" name="id_masjid"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('hasil.form.select_masjid') }}</option>
                    @foreach ($masjidOptions as $option)
                        <option value="{{ $option->id }}" @selected(old('id_masjid', $hasilRecord?->id_masjid) == $option->id)>{{ $option->nama }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('id_masjid')" />
            </div>
        @else
            <input type="hidden" name="id_masjid" value="{{ old('id_masjid', auth()->user()->id_masjid) }}">
        @endif

        <div>
            <x-input-label for="tarikh" :value="__('hasil.form.date')" />
            <x-text-input id="tarikh" name="tarikh" type="date" class="mt-1 block w-full" :value="old('tarikh', optional($hasilRecord?->tarikh)->format('Y-m-d') ?? now()->format('Y-m-d'))"
                required />
            <x-input-error class="mt-2" :messages="$errors->get('tarikh')" />
        </div>

        <div>
            <x-input-label for="amaun" :value="__('hasil.form.amount')" />
            <x-text-input id="amaun" name="amaun" type="number" min="0.01" step="0.01"
                class="mt-1 block w-full" :value="old('amaun', $hasilRecord?->jumlah)" required placeholder="0.00" />
            <x-input-error class="mt-2" :messages="$errors->get('amaun')" />
        </div>

        <div>
            <x-input-label for="id_akaun" :value="__('hasil.form.account')" />
            <select id="id_akaun" name="id_akaun"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
                <option value="">{{ __('hasil.form.select_account') }}</option>
                @foreach ($akaunOptions as $option)
                    <option value="{{ $option->id }}" @selected(old('id_akaun', $hasilRecord?->id_akaun) == $option->id)>
                        {{ $option->nama_akaun }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('id_akaun')" />
        </div>

        @if ($formMode !== 'jumaat')
            <div>
                <x-input-label for="id_sumber_hasil" :value="__('hasil.form.source')" />
                <select id="id_sumber_hasil" name="id_sumber_hasil"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required>
                    <option value="">{{ __('hasil.form.select_source') }}</option>
                    @foreach ($sumberHasilOptions as $option)
                        <option value="{{ $option->id }}" @selected(old('id_sumber_hasil', $hasilRecord?->id_sumber_hasil) == $option->id)>
                            {{ $option->nama_sumber }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('id_sumber_hasil')" />
            </div>

            <div>
                <x-input-label for="id_tabung_khas" :value="__('hasil.form.fund_optional')" />
                <select id="id_tabung_khas" name="id_tabung_khas"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('hasil.form.no_fund') }}</option>
                    @foreach ($tabungKhasOptions as $option)
                        <option value="{{ $option->id }}" @selected(old('id_tabung_khas', $hasilRecord?->id_tabung_khas) == $option->id)>
                            {{ $option->nama_tabung }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('id_tabung_khas')" />
            </div>
        @endif
    </div>

    @if ($formMode === 'jumaat')
        <input type="hidden" name="is_jumaat" value="1">
    @else
        <input type="hidden" name="is_jumaat" value="0">
    @endif

    <div>
        <x-input-label for="catatan" :value="__('hasil.form.notes_optional')" />
        <textarea id="catatan" name="catatan" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('catatan', $hasilRecord?->catatan) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('catatan')" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ __('hasil.form.save') }}</x-primary-button>
        <a href="{{ route('admin.hasil.index') }}"
            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            {{ __('hasil.form.back') }}
        </a>
    </div>
</form>
