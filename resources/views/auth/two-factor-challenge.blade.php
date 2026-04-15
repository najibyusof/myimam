<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.two_factor_title') }} - {{ config('app.name', 'Masjid Finance') }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <x-auth-card title="{{ __('auth.system_title') }}" subtitle="{{ __('auth.system_subtitle') }}"
        left-title="{{ __('auth.left_title_two_factor_authentication') }}">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('auth.two_factor_challenge_heading') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('auth.two_factor_challenge_subheading') }}</p>
        </div>

        <form method="POST" action="{{ route('2fa.challenge.verify') }}" x-data="{ submitting: false, useRecovery: false }"
            @submit="submitting = true" class="space-y-4">
            @csrf

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-1">
                <div class="grid grid-cols-2 gap-1">
                    <button type="button" @click="useRecovery = false"
                        :class="useRecovery ? 'bg-transparent text-gray-600' : 'bg-white text-indigo-700 shadow-sm'"
                        class="rounded-lg px-3 py-2 text-sm font-semibold transition">
                        {{ __('auth.authenticator_code') }}
                    </button>
                    <button type="button" @click="useRecovery = true"
                        :class="useRecovery ? 'bg-white text-indigo-700 shadow-sm' : 'bg-transparent text-gray-600'"
                        class="rounded-lg px-3 py-2 text-sm font-semibold transition">
                        {{ __('auth.recovery_code') }}
                    </button>
                </div>
            </div>

            <div>
                <label for="code" class="mb-1 block text-sm font-medium text-gray-700"
                    x-text="useRecovery ? @js(__('auth.recovery_code')) : @js(__('auth.authentication_code'))"></label>
                <input id="code" name="code" type="text" required autofocus autocomplete="one-time-code"
                    :placeholder="useRecovery ? @js(__('auth.placeholder_recovery_code')) : @js(__('auth.placeholder_authentication_code'))"
                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                @error('code')
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <x-button type="primary" class="w-full" button-type="submit" x-bind:disabled="submitting">
                <span x-show="!submitting">{{ __('auth.verify') }}</span>
                <span x-show="submitting" x-cloak>{{ __('messages.verifying') }}</span>
            </x-button>
        </form>
    </x-auth-card>
</body>

</html>
