<div class="space-y-4">
    <div>
        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Masjid</label>
        <input type="text" name="nama" id="nama" value="{{ old('nama', $masjid?->nama) }}"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('nama') border-red-500 @enderror"
            required />
        @error('nama')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
        <textarea name="alamat" id="alamat" rows="3"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('alamat') border-red-500 @enderror">{{ old('alamat', $masjid?->alamat) }}</textarea>
        @error('alamat')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="daerah" class="block text-sm font-medium text-gray-700">Daerah</label>
            <input type="text" name="daerah" id="daerah" value="{{ old('daerah', $masjid?->daerah) }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('daerah') border-red-500 @enderror" />
            @error('daerah')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="negeri" class="block text-sm font-medium text-gray-700">Negeri</label>
            <input type="text" name="negeri" id="negeri" value="{{ old('negeri', $masjid?->negeri) }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('negeri') border-red-500 @enderror" />
            @error('negeri')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="no_pendaftaran" class="block text-sm font-medium text-gray-700">No. Pendaftaran</label>
            <input type="text" name="no_pendaftaran" id="no_pendaftaran"
                value="{{ old('no_pendaftaran', $masjid?->no_pendaftaran) }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('no_pendaftaran') border-red-500 @enderror" />
            @error('no_pendaftaran')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tarikh_daftar" class="block text-sm font-medium text-gray-700">Tarikh Pendaftaran</label>
            <input type="date" name="tarikh_daftar" id="tarikh_daftar"
                value="{{ old('tarikh_daftar', $masjid?->tarikh_daftar?->format('Y-m-d')) }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('tarikh_daftar') border-red-500 @enderror" />
            @error('tarikh_daftar')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
