<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'Masjid Finance') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <x-auth-card
        title="Sistem Pengurusan Kewangan Masjid"
        subtitle="Transparent, efficient, and modern financial management"
        left-title="Demo Authentication"
    >
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Login</h2>
            <p class="mt-1 text-sm text-gray-500">Sila log masuk untuk akses sistem kewangan masjid.</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <div class="mb-6 rounded-xl border border-sky-200 bg-sky-50 p-4" x-data>
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-sky-800">Demo Accounts</p>
                <button type="button"
                    class="rounded-md border border-sky-300 px-2.5 py-1 text-xs font-semibold text-sky-700 transition hover:bg-sky-100"
                    @click="navigator.clipboard.writeText('Admin: admin@masjid.com / password\nBendahari: bendahari@masjid.com / password\nAJK: ajk@masjid.com / password\nAuditor: auditor@masjid.com / password')">
                    Copy All
                </button>
            </div>
            <div class="mt-3 space-y-2 text-xs text-sky-900">
                <p><span class="font-semibold">Admin:</span> admin@masjid.com / password</p>
                <p><span class="font-semibold">Bendahari:</span> bendahari@masjid.com / password</p>
                <p><span class="font-semibold">AJK:</span> ajk@masjid.com / password</p>
                <p><span class="font-semibold">Auditor:</span> auditor@masjid.com / password</p>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}" x-data="{ submitting: false, showPassword: false }" @submit="submitting = true" class="space-y-4">
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

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Password</label>
                <div class="relative">
                    <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required autocomplete="current-password"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-20 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-2 my-1 rounded-md px-2 text-xs font-semibold text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                        x-text="showPassword ? 'Hide' : 'Show'"></button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <span>Remember me</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                        Forgot password?
                    </a>
                @endif
            </div>

            <x-button type="primary" size="md" class="w-full" button-type="submit" x-bind:disabled="submitting" x-text="submitting ? 'Signing in...' : 'Log in'"></x-button>
        </form>
    </x-auth-card>
</body>
</html>
