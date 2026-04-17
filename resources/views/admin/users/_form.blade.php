@php
    $selectedRole = old('role', isset($user) ? $user->roles->pluck('name')->first() ?? null : null);
    $selectedMasjid = old('id_masjid', $user->id_masjid ?? '');
    $actor = auth()->user();
    $canSelectMasjid = $actor?->peranan === 'superadmin';
    $actorMasjidName = $masjidOptions->first()?->nama ?? '-';
@endphp

<div class="grid grid-cols-1 gap-6">
    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        User accounts are restricted to administrators for management. Profile edits for the signed-in user remain
        available from the profile screen.
    </div>

    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name ?? '')" required />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email ?? '')"
            required />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ showPassword: false, showPasswordConfirmation: false }">
        <div>
            <x-input-label for="password" :value="isset($user) ? __('Password (leave blank to keep)') : __('Password')" />
            <div class="relative mt-1">
                <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                    class="block w-full rounded-md border-gray-300 pr-16 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    autocomplete="new-password" {{ isset($user) ? '' : 'required' }} />
                <button type="button" @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-1 my-1 rounded-md px-2 text-xs font-semibold text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                    x-text="showPassword ? @js(__('form.hide')) : @js(__('form.show'))"></button>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div class="relative mt-1">
                <input id="password_confirmation" name="password_confirmation"
                    :type="showPasswordConfirmation ? 'text' : 'password'"
                    class="block w-full rounded-md border-gray-300 pr-16 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    autocomplete="new-password" {{ isset($user) ? '' : 'required' }} />
                <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                    class="absolute inset-y-0 right-1 my-1 rounded-md px-2 text-xs font-semibold text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                    x-text="showPasswordConfirmation ? @js(__('form.hide')) : @js(__('form.show'))"></button>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <x-input-label for="role" :value="__('Role')" />
            <select id="role" name="role"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
                <option value="">Select Role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected($selectedRole === $role)>{{ $role }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('role')" />
        </div>

        @if ($canSelectMasjid)
            <div>
                <x-input-label for="id_masjid" :value="__('Masjid (optional)')" />
                <select id="id_masjid" name="id_masjid"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">No Masjid</option>
                    @foreach ($masjidOptions as $masjid)
                        <option value="{{ $masjid->id }}" @selected((string) $selectedMasjid === (string) $masjid->id)>
                            {{ $masjid->nama }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('id_masjid')" />
            </div>
        @else
            <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                <p class="font-medium text-slate-700">Masjid</p>
                <p class="mt-1">Pengguna baru akan disimpan di masjid anda secara automatik: {{ $actorMasjidName }}
                </p>
                <input type="hidden" name="id_masjid" value="{{ $actor?->id_masjid }}">
            </div>
        @endif
    </div>

    <div class="flex items-center gap-2">
        <input id="aktif" name="aktif" type="checkbox" value="1"
            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
            @checked(old('aktif', $user->aktif ?? true))>
        <x-input-label for="aktif" :value="__('Active')" />
    </div>

    @if (isset($user))
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Leave the password fields blank to keep the current password. Use the reset action on the edit page to send
            a password reset link.
        </div>
    @endif
</div>
