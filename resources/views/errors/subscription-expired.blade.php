<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Langganan Tamat — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900">

    <div class="min-h-screen flex flex-col items-center justify-center px-4">

        <div class="w-full max-w-lg bg-white rounded-2xl shadow-lg overflow-hidden">

            {{-- Orange banner --}}
            <div class="bg-orange-500 px-8 py-6 flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Langganan Tamat</h1>
                    <p class="text-orange-100 text-sm mt-1">Akses sistem telah disekat</p>
                </div>
            </div>

            <div class="px-8 py-7">
                <p class="text-gray-700 leading-relaxed">
                    Langganan masjid anda telah <strong>tamat tempoh</strong>. Akses ke sistem kewangan telah disekat
                    sehingga langganan diperbaharui.
                </p>

                @if (session('expiry'))
                    <div class="mt-5 p-3 bg-orange-50 rounded-lg border border-orange-200 text-sm text-orange-700">
                        Tarikh tamat: <strong>{{ session('expiry') }}</strong>
                    </div>
                @endif

                <div class="mt-5 p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <p class="text-sm text-blue-700 font-medium">Cara memperbaharui langganan:</p>
                    <ul class="text-sm text-blue-600 mt-2 space-y-1 list-disc list-inside">
                        <li>Hubungi pentadbir sistem</li>
                        <li>Semak e-mel untuk notifikasi langganan</li>
                        <li>Lawati panel SuperAdmin untuk maklumat lanjut</li>
                    </ul>
                </div>

                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white hover:bg-orange-600 transition">
                            Log Keluar
                        </button>
                    </form>
                </div>
            </div>

            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 text-center text-xs text-gray-400">
                {{ config('app.name') }} &mdash; Sistem Kewangan Masjid
            </div>
        </div>

    </div>

</body>

</html>
