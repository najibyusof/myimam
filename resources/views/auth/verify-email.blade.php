<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email - {{ config('app.name', 'Masjid Finance') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <x-auth-card
        title="Sistem Pengurusan Kewangan Masjid"
        subtitle="Transparent, efficient, and modern financial management"
        left-title="Email Verification"
    >
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Verify Email</h2>
            <p class="mt-1 text-sm text-gray-500">
                Thanks for signing up. Please verify your email address by clicking the link we sent.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                A new verification link has been sent to your email address.
            </div>
        @endif

        <div class="space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-button type="primary" class="w-full" button-type="submit">Resend Verification Email</x-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button type="ghost" class="w-full" button-type="submit">Log Out</x-button>
            </form>
        </div>
    </x-auth-card>
</body>
</html>
