@props([
    'action',
    'method' => 'POST',
    'tabungKhas' => null,
    'masjidOptions' => collect(),
])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">
        Tabung khas aktif boleh digunakan semasa merekod transaksi hasil dan belanja. Nyahaktifkan tabung jika anda mahu hentikan penggunaan untuk transaksi baharu tanpa mengganggu rekod lama.
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        @if (auth()->user()->hasRole('Admin'))
            <div>
                <x-input-label for="id_masjid" value="Masjid" />
                <select id="id_masjid" name="id_masjid"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Pilih masjid</option>
                    @foreach ($masjidOptions as $option)
                        <option value="{{ $option->id }}" @selected(old('id_masjid', $tabungKhas?->id_masjid) == $option->id)>
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
            <x-input-label for="nama_tabung" value="Nama Tabung" />
            <x-text-input id="nama_tabung" name="nama_tabung" type="text" class="mt-1 block w-full"
                :value="old('nama_tabung', $tabungKhas?->nama_tabung)" required maxlength="150"
                placeholder="Contoh: Tabung Pembangunan" />
            <x-input-error class="mt-2" :messages="$errors->get('nama_tabung')" />
        </div>
    </div>

    <label class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
        <input type="checkbox" name="aktif" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
            @checked(old('aktif', $tabungKhas?->aktif ?? true))>
        <span class="text-sm text-slate-700">Aktifkan tabung ini untuk transaksi baharu</span>
    </label>

    @if ($tabungKhas)
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Transaksi Hasil</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $tabungKhas->hasil_count ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Transaksi Belanja</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $tabungKhas->belanja_count ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-amber-700">Jumlah Penggunaan</p>
                <p class="mt-2 text-2xl font-semibold text-amber-900">{{ ($tabungKhas->hasil_count ?? 0) + ($tabungKhas->belanja_count ?? 0) }}</p>
            </div>
        </div>
    @endif

    <div class="flex items-center gap-3">
        <x-primary-button>Simpan</x-primary-button>
        <a href="{{ route('admin.tabung-khas.index') }}"
            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Kembali
        </a>
    </div>
</form>
