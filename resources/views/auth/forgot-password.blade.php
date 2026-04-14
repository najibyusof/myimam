<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - {{ config('app.name', 'Masjid Finance') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <x-auth-card
        title="Sistem Pengurusan Kewangan Masjid"
        subtitle="Transparent, efficient, and modern financial management"
        left-title="Password Assistance"
    >
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Reset Password</h2>
            <p class="mt-1 text-sm text-gray-500">Masukkan emel anda untuk menerima pautan set semula kata laluan.</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-4">
            @csrf

            <x-input
                label="Email"
                name="email"
                type="email"
                :value="old('email')"
                placeholder="nama@masjid.com"
                required
                autofocus
                autocomplete="username"
            />

            <x-button type="primary" class="w-full" button-type="submit" x-bind:disabled="submitting" x-text="submitting ? 'Sending link...' : 'Email Password Reset Link'"></x-button>

            <a href="{{ route('login') }}" class="block text-center text-sm font-medium text-indigo-600 hover:text-indigo-700">
                Back to login
            </a>
        </form>
    </x-auth-card>
</body>
</html>
