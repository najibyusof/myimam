<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.login') }} - {{ config('app.name', 'Masjid Finance') }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <x-auth-card title="{{ __('auth.system_title') }}" subtitle="{{ __('auth.system_subtitle') }}"
        left-title="{{ __('auth.left_title_demo') }}">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('auth.login_heading') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('auth.login_subheading') }}</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <div class="mb-6 rounded-xl border border-sky-200 bg-sky-50 p-4" x-data="{ open: false }">
            <div class="flex items-center justify-between gap-3">
                <button type="button" @click="open = !open"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-sky-800">
                    <span>{{ __('auth.demo_accounts') }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                        :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <button type="button"
                    class="rounded-md border border-sky-300 px-2.5 py-1 text-xs font-semibold text-sky-700 transition hover:bg-sky-100"
                    @click="navigator.clipboard.writeText(@js(__('messages.demo_copy_payload')))">
                    {{ __('form.copy_all') }}
                </button>
            </div>

            <div x-show="open" x-cloak class="mt-3 space-y-2 text-xs text-sky-900">
                <p><span class="font-semibold">{{ __('auth.system_admin') }}:</span> superadmin@imam.com /
                    {{ __('auth.password') }}</p>
                <p><span class="font-semibold">{{ __('auth.masjid_al_falah_admin') }}:</span> admin@alfalah.com /
                    {{ __('auth.password') }}</p>
                <p><span class="font-semibold">{{ __('auth.masjid_al_falah_bendahari') }}:</span> bendahari@alfalah.com
                    /
                    {{ __('auth.password') }}</p>
                <p><span class="font-semibold">{{ __('auth.masjid_al_falah_ajk') }}:</span> ajk.kewangan@alfalah.com /
                    {{ __('auth.password') }}</p>
                <p><span class="font-semibold">{{ __('auth.masjid_al_falah_auditor') }}:</span> auditor@alfalah.com /
                    {{ __('auth.password') }}
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}" x-data="{ submitting: false, showPassword: false }" @submit="submitting = true"
            class="space-y-4">
            @csrf

            <x-input label="{{ __('auth.email') }}" name="email" type="email" :value="old('email')"
                placeholder="{{ __('form.placeholder_email') }}" required autofocus autocomplete="username" />

            <div>
                <label for="password"
                    class="mb-1 block text-sm font-medium text-gray-700">{{ __('auth.password') }}</label>
                <div class="relative">
                    <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                        autocomplete="current-password"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-20 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-2 my-1 rounded-md px-2 text-xs font-semibold text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                        x-text="showPassword ? @js(__('form.hide')) : @js(__('form.show'))"></button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <span>{{ __('auth.remember_me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                        {{ __('auth.forgot_password') }}
                    </a>
                @endif
            </div>

            <x-button type="primary" size="md" class="w-full" button-type="submit" x-bind:disabled="submitting">
                <span x-show="!submitting">{{ __('auth.log_in') }}</span>
                <span x-show="submitting" x-cloak>{{ __('messages.signing_in') }}</span>
            </x-button>

            <a href="{{ url('/') }}"
                class="block text-center text-sm font-medium text-gray-500 hover:text-gray-700">
                {{ __('form.back_to_landing') }}
            </a>
        </form>
    </x-auth-card>
</body>

</html>
