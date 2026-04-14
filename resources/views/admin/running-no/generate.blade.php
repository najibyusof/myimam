<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                {{ __('Jana Nombor Rujukan') }}
            </h2>
            <a href="{{ route('admin.running-no.index') }}"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                &larr; Senarai Kaunter
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Result card --}}
            @isset($nomborRujukan)
                <div class="bg-green-50 border border-green-300 rounded-xl p-6 text-center shadow-sm">
                    <p class="text-xs font-semibold text-green-600 uppercase tracking-wider mb-2">Nombor Rujukan Dijana</p>
                    <p class="text-3xl font-bold font-mono text-green-700 tracking-widest">{{ $nomborRujukan }}</p>
                    <p class="mt-2 text-xs text-green-600">Kaunter telah dikemaskini secara automatik.</p>
                </div>
            @endisset

            {{-- Generate form --}}
            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-5">Parameter Nombor Rujukan</h3>

                <form method="POST" action="{{ route('admin.running-no.generate.post') }}" class="space-y-5">
                    @csrf

                    @if ($isAdmin)
                        <div>
                            <label for="id_masjid" class="block text-sm font-medium text-gray-700 mb-1">Masjid</label>
                            <select id="id_masjid" name="id_masjid"
                                class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error('id_masjid') border-red-400 @enderror">
                                <option value="">-- Pilih Masjid --</option>
                                @foreach ($masjidOptions as $m)
                                    <option value="{{ $m->id }}" @selected(isset($lastIdMasjid) && $lastIdMasjid == $m->id)>
                                        {{ $m->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_masjid')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label for="prefix" class="block text-sm font-medium text-gray-700 mb-1">Prefix /
                            Jenis</label>
                        <input list="prefix-list" id="prefix" name="prefix"
                            value="{{ old('prefix', $lastPrefix ?? '') }}" placeholder="e.g. RMT, INV, BLL, PKT"
                            class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono uppercase @error('prefix') border-red-400 @enderror">
                        <datalist id="prefix-list">
                            <option value="RMT">
                            <option value="INV">
                            <option value="BLL">
                            <option value="PKT">
                            <option value="RCP">
                        </datalist>
                        @error('prefix')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Huruf besar/kecil diterima — akan ditukar ke huruf besar.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                            <input type="number" id="tahun" name="tahun"
                                value="{{ old('tahun', $defaultTahun) }}" min="2000" max="2100"
                                class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error('tahun') border-red-400 @enderror">
                            @error('tahun')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                            <select id="bulan" name="bulan"
                                class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error('bulan') border-red-400 @enderror">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" @selected(old('bulan', $defaultBulan) == $m)>
                                        {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('bulan')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
                            Jana Nombor Rujukan
                        </button>
                    </div>
                </form>

                <div class="mt-4 rounded-lg bg-indigo-50 border border-indigo-100 px-4 py-3 text-xs text-indigo-700">
                    <strong>Format:</strong> PREFIX-TAHUNBULAN-URUTAN &nbsp;|&nbsp;
                    Contoh: <span class="font-mono font-semibold">RMT-2604-001</span>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
