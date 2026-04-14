<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Set New Password - {{ config('app.name', 'Masjid Finance') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <x-auth-card
        title="Sistem Pengurusan Kewangan Masjid"
        subtitle="Transparent, efficient, and modern financial management"
        left-title="Secure Password Reset"
    >
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Set New Password</h2>
            <p class="mt-1 text-sm text-gray-500">Masukkan kata laluan baru untuk akaun anda.</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <x-input
                label="Email"
                name="email"
                type="email"
                :value="old('email', $request->email)"
                required
                autofocus
                autocomplete="username"
            />

            <x-input
                label="Password"
                name="password"
                type="password"
                required
                autocomplete="new-password"
            />

            <x-input
                label="Confirm Password"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
            />

            <x-button type="primary" class="w-full" button-type="submit" x-bind:disabled="submitting" x-text="submitting ? 'Resetting password...' : 'Reset Password'"></x-button>
        </form>
    </x-auth-card>
</body>
</html>
