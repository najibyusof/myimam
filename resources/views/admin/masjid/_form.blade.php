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
            <label for="code" class="block text-sm font-medium text-gray-700">Kod Tenant</label>
            <input type="text" name="code" id="code" value="{{ old('code', $masjid?->code) }}"
                placeholder="contoh: masjid-al-hidayah"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('code') border-red-500 @enderror" />
            @error('code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status Tenant</label>
            <select name="status" id="status"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('status') border-red-500 @enderror">
                @php($status = old('status', $masjid?->status ?? 'pending'))
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="suspended" @selected($status === 'suspended')>Suspended</option>
                <option value="pending" @selected($status === 'pending')>Pending</option>
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="subscription_status" class="block text-sm font-medium text-gray-700">Status Langganan</label>
            <select name="subscription_status" id="subscription_status"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('subscription_status') border-red-500 @enderror">
                @php($subStatus = old('subscription_status', $masjid?->subscription_status ?? 'none'))
                <option value="active" @selected($subStatus === 'active')>Active</option>
                <option value="trial" @selected($subStatus === 'trial')>Trial</option>
                <option value="expired" @selected($subStatus === 'expired')>Expired</option>
                <option value="none" @selected($subStatus === 'none')>None</option>
            </select>
            @error('subscription_status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="subscription_expiry" class="block text-sm font-medium text-gray-700">Tarikh Tamat
                Langganan</label>
            <input type="datetime-local" name="subscription_expiry" id="subscription_expiry"
                value="{{ old('subscription_expiry', $masjid?->subscription_expiry?->format('Y-m-d\TH:i')) }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('subscription_expiry') border-red-500 @enderror" />
            @error('subscription_expiry')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="pt-4 border-t">
        <h4 class="font-medium text-gray-800 mb-3">Tetapkan Admin Masjid</h4>

        <div class="mb-4">
            <label for="admin_user_id" class="block text-sm font-medium text-gray-700">Pilih Pengguna Sedia Ada</label>
            <select name="admin_user_id" id="admin_user_id"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('admin_user_id') border-red-500 @enderror">
                <option value="">-- Tiada --</option>
                @foreach ($adminCandidates as $user)
                    <option value="{{ $user->id }}" @selected((string) old('admin_user_id') === (string) $user->id)>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
            @error('admin_user_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-xs text-gray-500 mb-3">Atau cipta admin baru di bawah.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="admin_name" class="block text-sm font-medium text-gray-700">Nama Admin</label>
                <input type="text" name="admin_name" id="admin_name" value="{{ old('admin_name') }}"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('admin_name') border-red-500 @enderror" />
                @error('admin_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="admin_email" class="block text-sm font-medium text-gray-700">Email Admin</label>
                <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('admin_email') border-red-500 @enderror" />
                @error('admin_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ showAdminPassword: false }">
                <label for="admin_password" class="block text-sm font-medium text-gray-700">Kata Laluan Admin</label>
                <div class="relative mt-1">
                    <input :type="showAdminPassword ? 'text' : 'password'" name="admin_password" id="admin_password"
                        class="block w-full px-3 py-2 pr-16 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('admin_password') border-red-500 @enderror" />
                    <button type="button" @click="showAdminPassword = !showAdminPassword"
                        class="absolute inset-y-0 right-1 my-1 rounded-md px-2 text-xs font-semibold text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                        x-text="showAdminPassword ? @js(__('form.hide')) : @js(__('form.show'))"></button>
                </div>
                @error('admin_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>
