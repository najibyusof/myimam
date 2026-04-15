<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.register') }} - {{ config('app.name', 'Masjid Finance') }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <x-auth-card title="{{ __('auth.system_title') }}" subtitle="{{ __('auth.system_subtitle') }}"
        left-title="{{ __('auth.left_title_create_access') }}">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('auth.register_heading') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('auth.register_subheading') }}</p>
        </div>

        <form method="POST" action="{{ route('register') }}" x-data="{ submitting: false }" @submit="submitting = true"
            class="space-y-4">
            @csrf

            <x-input label="{{ __('form.name') }}" name="name" type="text" :value="old('name')"
                placeholder="{{ __('form.placeholder_full_name') }}" required autofocus autocomplete="name" />

            <x-input label="{{ __('auth.email') }}" name="email" type="email" :value="old('email')"
                placeholder="{{ __('form.placeholder_email') }}" required autocomplete="username" />

            <x-input label="{{ __('auth.password') }}" name="password" type="password" required
                autocomplete="new-password" />

            <x-input label="{{ __('form.password_confirmation') }}" name="password_confirmation" type="password"
                required autocomplete="new-password" />

            <x-button type="primary" class="w-full" button-type="submit" x-bind:disabled="submitting">
                <span x-show="!submitting">{{ __('auth.register') }}</span>
                <span x-show="submitting" x-cloak>{{ __('messages.creating_account') }}</span>
            </x-button>

            <a href="{{ route('login') }}"
                class="block text-center text-sm font-medium text-indigo-600 hover:text-indigo-700">
                {{ __('auth.already_registered') }}
            </a>
        </form>
    </x-auth-card>
</body>

</html>
