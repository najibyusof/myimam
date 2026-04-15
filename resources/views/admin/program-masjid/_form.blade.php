@props(['action', 'method' => 'POST', 'programMasjid' => null, 'masjidOptions' => collect()])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">{{ __('program_masjid.info_box') }}
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        @if (auth()->user()->hasRole('Admin'))
            <div>
                <x-input-label for="id_masjid" :value="__('program_masjid.form.masjid')" />
                <select id="id_masjid" name="id_masjid"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('program_masjid.form.select_masjid') }}</option>
                    @foreach ($masjidOptions as $option)
                        <option value="{{ $option->id }}" @selected(old('id_masjid', $programMasjid?->id_masjid) == $option->id)>
                            {{ $option->nama }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('id_masjid')" />
            </div>
        @else
            <input type="hidden" name="id_masjid" value="{{ old('id_masjid', auth()->user()->id_masjid) }}">
        @endif

        <div>
            <x-input-label for="nama_program" :value="__('program_masjid.form.program_name')" />
            <x-text-input id="nama_program" name="nama_program" type="text" class="mt-1 block w-full"
                :value="old('nama_program', $programMasjid?->nama_program)" required maxlength="150" :placeholder="__('program_masjid.form.program_name_placeholder')" />
            <x-input-error class="mt-2" :messages="$errors->get('nama_program')" />
        </div>
    </div>

    <label class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
        <input type="checkbox" name="aktif" value="1"
            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
            @checked(old('aktif', $programMasjid?->aktif ?? true))>
        <span class="text-sm text-slate-700">{{ __('program_masjid.form.active_toggle') }}</span>
    </label>

    @if ($programMasjid)
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    {{ __('program_masjid.form.hasil_transactions') }}</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $programMasjid->hasil_count ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    {{ __('program_masjid.form.belanja_transactions') }}</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $programMasjid->belanja_count ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-amber-700">{{ __('program_masjid.form.total_usage') }}
                </p>
                <p class="mt-2 text-2xl font-semibold text-amber-900">
                    {{ ($programMasjid->hasil_count ?? 0) + ($programMasjid->belanja_count ?? 0) }}</p>
            </div>
        </div>
    @endif

    <div class="flex items-center gap-3">
        <x-primary-button>{{ __('program_masjid.form.save') }}</x-primary-button>
        <a href="{{ route('admin.program-masjid.index') }}"
            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            {{ __('program_masjid.form.back') }}
        </a>
    </div>
</form>
