@props([
    'action',
    'method' => 'POST',
    'belanjaRecord' => null,
    'masjidOptions' => collect(),
    'akaunOptions' => collect(),
    'kategoriOptions' => collect(),
    'baucarOptions' => collect(),
])

<form method="POST" action="{{ $action }}" class="space-y-6" enctype="multipart/form-data">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        @if (auth()->user()->hasRole('Superadmin'))
            <div>
                <x-input-label for="id_masjid" :value="__('belanja.form.masjid')" />
                <select id="id_masjid" name="id_masjid"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('belanja.form.select_masjid') }}</option>
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
            <x-input-label for="tarikh" :value="__('belanja.form.date')" />
            <x-text-input id="tarikh" name="tarikh" type="date" class="mt-1 block w-full" :value="old('tarikh', optional($belanjaRecord?->tarikh)->format('Y-m-d') ?? now()->format('Y-m-d'))"
                required />
            <x-input-error class="mt-2" :messages="$errors->get('tarikh')" />
        </div>

        <div>
            <x-input-label for="amaun" :value="__('belanja.form.amount')" />
            <x-text-input id="amaun" name="amaun" type="number" min="0.01" step="0.01"
                class="mt-1 block w-full" :value="old('amaun', $belanjaRecord?->amaun)" required placeholder="0.00" />
            <x-input-error class="mt-2" :messages="$errors->get('amaun')" />
        </div>

        <div>
            <x-input-label for="id_akaun" :value="__('belanja.form.account')" />
            <select id="id_akaun" name="id_akaun"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
                <option value="">{{ __('belanja.form.select_account') }}</option>
                @foreach ($akaunOptions as $option)
                    <option value="{{ $option->id }}" @selected(old('id_akaun', $belanjaRecord?->id_akaun) == $option->id)>{{ $option->nama_akaun }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('id_akaun')" />
        </div>

        <div>
            <x-input-label for="id_kategori_belanja" :value="__('belanja.form.category')" />
            <select id="id_kategori_belanja" name="id_kategori_belanja"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
                <option value="">{{ __('belanja.form.select_category') }}</option>
                @foreach ($kategoriOptions as $option)
                    <option value="{{ $option->id }}" @selected(old('id_kategori_belanja', $belanjaRecord?->id_kategori_belanja) == $option->id)>{{ $option->nama_kategori }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('id_kategori_belanja')" />
        </div>

        <div>
            <x-input-label for="id_baucar" :value="__('belanja.form.voucher_optional')" />
            <select id="id_baucar" name="id_baucar"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('belanja.form.no_voucher') }}</option>
                @foreach ($baucarOptions as $option)
                    <option value="{{ $option->id }}" @selected(old('id_baucar', $belanjaRecord?->id_baucar) == $option->id)>{{ $option->no_baucar }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('id_baucar')" />
        </div>

        <div>
            <x-input-label for="penerima" :value="__('belanja.form.recipient_optional')" />
            <x-text-input id="penerima" name="penerima" type="text" class="mt-1 block w-full" :value="old('penerima', $belanjaRecord?->penerima)"
                maxlength="190" />
            <x-input-error class="mt-2" :messages="$errors->get('penerima')" />
        </div>
    </div>

    <div>
        <x-input-label for="catatan" :value="__('belanja.form.notes_optional')" />
        <textarea id="catatan" name="catatan" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('catatan', $belanjaRecord?->catatan) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('catatan')" />
    </div>

    {{-- Attachment --}}
    <div>
        <x-input-label :value="__('belanja.form.attachment_optional')" />

        @if ($belanjaRecord?->bukti_fail)
            <div id="existing-attachment-container"
                class="mt-2 flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                </svg>
                <a href="{{ route('admin.belanja.viewAttachment', $belanjaRecord) }}" target="_blank"
                    class="flex-1 truncate text-indigo-600 hover:underline">
                    {{ basename($belanjaRecord->bukti_fail) }}
                </a>
                <button type="button"
                    data-async-delete-url="{{ route('admin.belanja.deleteAttachment', $belanjaRecord) }}"
                    data-confirm-title="Adakah anda pasti?"
                    data-confirm-text="{{ __('belanja.confirm_delete_attachment') }}" data-confirm-button="Ya, padam"
                    data-success-message="{{ __('belanja.success_delete_attachment') }}"
                    data-error-message="{{ __('belanja.error_delete_attachment') }}"
                    data-remove-selector="#existing-attachment-container|#existing-attachment-hint"
                    data-show-selector="#upload-buttons-container"
                    data-reset-file-inputs="#bukti_fail_input|#bukti_fail_camera_input"
                    data-reset-label-selector="#file-label"
                    data-reset-label-text="{{ __('belanja.form.choose_file') }}"
                    class="ml-auto flex items-center gap-1.5 rounded-md bg-red-50 px-3 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-100 hover:text-red-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 2.5h-2.25m-7.86 0H9.84m-8.86 3.21c.342-.052.68-.107 1.02-.166m0 0a48.11 48.11 0 017.7 0" />
                    </svg>
                    <span>{{ __('belanja.form.remove_attachment') }}</span>
                </button>
            </div>
            <p id="existing-attachment-hint" class="mt-1 text-xs text-slate-500">
                {{ __('belanja.form.replace_attachment_hint') }}</p>
        @endif

        <div id="upload-buttons-container" class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center"
            @if ($belanjaRecord?->bukti_fail) style="display: none;" @endif>
            {{-- File picker --}}
            <label class="flex-1 cursor-pointer">
                <div id="file-drop-zone"
                    class="flex items-center gap-3 rounded-lg border-2 border-dashed border-slate-300 bg-white px-4 py-3 text-sm text-slate-500 transition hover:border-indigo-400 hover:text-indigo-600">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    <span id="file-label"
                        data-default-text="{{ __('belanja.form.choose_file') }}">{{ __('belanja.form.choose_file') }}</span>
                </div>
                <input id="bukti_fail_input" type="file" name="bukti_fail" accept=".jpg,.jpeg,.png,.pdf"
                    class="sr-only" onchange="handleFileSelect(this)">
            </label>

            {{-- Camera button --}}
            <label class="cursor-pointer">
                <div
                    class="flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-600 transition hover:border-indigo-400 hover:text-indigo-600">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                    </svg>
                    <span>{{ __('belanja.form.take_photo') }}</span>
                </div>
                <input id="bukti_fail_camera_input" type="file" name="bukti_fail_camera" accept="image/*"
                    capture="environment" class="sr-only" onchange="handleFileSelect(this)">
            </label>
        </div>

        <p class="mt-1 text-xs text-slate-500">{{ __('belanja.form.attachment_hint') }}</p>
        <x-input-error class="mt-2" :messages="array_merge($errors->get('bukti_fail'), $errors->get('bukti_fail_camera'))" />
    </div>

    @if ($belanjaRecord)
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <span class="font-medium">{{ __('belanja.form.current_status') }}:</span>
            <span
                class="ml-2 inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $belanjaRecord->status === 'DRAF' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $belanjaRecord->status === 'DRAF' ? __('belanja.table.draft') : __('belanja.table.submitted') }}
            </span>
        </div>
    @endif

    <div class="flex items-center gap-3">
        <button type="submit" name="submit_action" value="submitted"
            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            {{ __('belanja.form.save') }}
        </button>
        <button type="submit" name="submit_action" value="draft"
            class="inline-flex items-center rounded-md border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 shadow-sm hover:bg-amber-100">
            {{ __('belanja.form.save_draft') }}
        </button>
        <a href="{{ route('admin.belanja.index') }}"
            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            {{ __('belanja.form.back') }}
        </a>
    </div>
</form>

<script>
    function handleFileSelect(input) {
        const label = document.getElementById('file-label');
        if (input.files && input.files.length > 0) {
            if (label) {
                label.textContent = input.files[0].name;
            }
        }
    }
</script>
