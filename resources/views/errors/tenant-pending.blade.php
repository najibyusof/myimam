<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akaun Belum Aktif — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900">

    <div class="min-h-screen flex flex-col items-center justify-center px-4">

        <div class="w-full max-w-lg bg-white rounded-2xl shadow-lg overflow-hidden">

            {{-- Amber banner --}}
            <div class="bg-amber-500 px-8 py-6 flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Akaun Belum Aktif</h1>
                    <p class="text-amber-100 text-sm mt-1">Menunggu pengesahan pentadbir</p>
                </div>
            </div>

            <div class="px-8 py-7">
                <p class="text-gray-700 leading-relaxed">
                    Akaun masjid anda masih dalam status <strong>menunggu</strong>. Pentadbir sistem perlu mengaktifkan
                    akaun anda sebelum anda boleh menggunakan sistem ini.
                </p>
                <p class="text-gray-500 text-sm mt-4">
                    Jika anda telah mendaftar, sila hubungi pentadbir anda untuk proses pengaktifan.
                </p>

                <div class="mt-8">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white hover:bg-amber-600 transition">
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
