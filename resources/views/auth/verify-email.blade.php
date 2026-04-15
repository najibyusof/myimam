<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.verify_email_heading') }} - {{ config('app.name', 'Masjid Finance') }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <x-auth-card title="{{ __('auth.system_title') }}" subtitle="{{ __('auth.system_subtitle') }}"
        left-title="{{ __('auth.left_title_email_verification') }}">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('auth.verify_email_heading') }}</h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ __('auth.verify_email_subheading') }}
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div
                class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ __('messages.resend_verification_success') }}
            </div>
        @endif

        <div class="space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-button type="primary" class="w-full"
                    button-type="submit">{{ __('auth.resend_verification_email') }}</x-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button type="ghost" class="w-full" button-type="submit">{{ __('form.logout') }}</x-button>
            </form>
        </div>
    </x-auth-card>
</body>

</html>
