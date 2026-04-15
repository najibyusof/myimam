@props(['record' => null, 'akaunOptions' => collect()])

<div class="space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Tarikh --}}
        <div>
            <label for="tarikh" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('pindahan_akaun.form.date') }} <span class="text-rose-500">*</span>
            </label>
            <input type="date" id="tarikh" name="tarikh"
                value="{{ old('tarikh', $record?->tarikh?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('tarikh') border-rose-400 @enderror">
            @error('tarikh')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Amaun --}}
        <div>
            <label for="amaun" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('pindahan_akaun.form.amount_rm') }} <span class="text-rose-500">*</span>
            </label>
            <input type="number" id="amaun" name="amaun" step="0.01" min="0.01"
                value="{{ old('amaun', $record?->amaun) }}" placeholder="0.00"
                class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('amaun') border-rose-400 @enderror">
            @error('amaun')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Dari Akaun --}}
        <div>
            <label for="dari_akaun_id" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('pindahan_akaun.form.from_account') }} <span class="text-rose-500">*</span>
            </label>
            <select id="dari_akaun_id" name="dari_akaun_id"
                class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('dari_akaun_id') border-rose-400 @enderror">
                <option value="">{{ __('pindahan_akaun.form.select_source_account') }}</option>
                @foreach ($akaunOptions as $akaun)
                    <option value="{{ $akaun->id }}" @selected(old('dari_akaun_id', $record?->dari_akaun_id) == $akaun->id)>
                        {{ $akaun->nama_akaun }}
                    </option>
                @endforeach
            </select>
            @error('dari_akaun_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Ke Akaun --}}
        <div>
            <label for="ke_akaun_id" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('pindahan_akaun.form.to_account') }} <span class="text-rose-500">*</span>
            </label>
            <select id="ke_akaun_id" name="ke_akaun_id"
                class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('ke_akaun_id') border-rose-400 @enderror">
                <option value="">{{ __('pindahan_akaun.form.select_destination_account') }}</option>
                @foreach ($akaunOptions as $akaun)
                    <option value="{{ $akaun->id }}" @selected(old('ke_akaun_id', $record?->ke_akaun_id) == $akaun->id)>
                        {{ $akaun->nama_akaun }}
                    </option>
                @endforeach
            </select>
            @error('ke_akaun_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Catatan --}}
    <div>
        <label for="catatan"
            class="block text-sm font-medium text-gray-700 mb-1">{{ __('pindahan_akaun.form.notes') }}</label>
        <textarea id="catatan" name="catatan" rows="3"
            class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('catatan') border-rose-400 @enderror"
            placeholder="{{ __('pindahan_akaun.form.notes_placeholder') }}">{{ old('catatan', $record?->catatan) }}</textarea>
        @error('catatan')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>
