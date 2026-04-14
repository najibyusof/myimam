<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akaun Digantung — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900">

    <div class="min-h-screen flex flex-col items-center justify-center px-4">

        {{-- Card --}}
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-lg overflow-hidden">

            {{-- Red banner --}}
            <div class="bg-red-600 px-8 py-6 flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Akaun Digantung</h1>
                    <p class="text-red-100 text-sm mt-1">Akses tidak dibenarkan</p>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-8 py-7">
                <p class="text-gray-700 leading-relaxed">
                    Akaun masjid anda telah <strong>digantung</strong>. Anda tidak dapat mengakses sistem pada masa ini.
                </p>
                <p class="text-gray-700 mt-3 leading-relaxed">
                    Sila hubungi pentadbir sistem untuk mendapatkan bantuan atau maklumat lanjut.
                </p>

                @if (session('masjid_name'))
                    <div class="mt-5 p-3 bg-red-50 rounded-lg border border-red-200 text-sm text-red-700">
                        Masjid: <strong>{{ session('masjid_name') }}</strong>
                    </div>
                @endif

                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Log Keluar
                        </button>
                    </form>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 text-center text-xs text-gray-400">
                {{ config('app.name') }} &mdash; Sistem Kewangan Masjid
            </div>
        </div>

    </div>

</body>

</html>
