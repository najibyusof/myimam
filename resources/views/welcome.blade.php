<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Pengurusan Kewangan Masjid</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 font-sans text-gray-900 antialiased">
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-900 to-slate-800">
        <div class="absolute inset-0 opacity-20">
            <div class="absolute -left-10 top-10 h-40 w-40 rounded-full bg-cyan-400 blur-3xl"></div>
            <div class="absolute right-0 top-32 h-56 w-56 rounded-full bg-indigo-300 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 pb-24 pt-8 sm:px-6 lg:px-8">
            <header class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-indigo-200">Demo Platform</p>
                    <h1 class="mt-2 text-xl font-bold text-white">Masjid Finance System</h1>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="rounded-lg border border-white/30 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="rounded-lg border border-white/30 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">
                            Login
                        </a>
                    @endauth
                </div>
            </header>

            <section class="mt-16 grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-200">Sistem Pengurusan Kewangan
                        Masjid</p>
                    <h2 class="mt-4 text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                        Transparent, efficient, and modern financial management
                    </h2>
                    <p class="mt-5 max-w-xl text-base text-indigo-100 sm:text-lg">
                        Urus akaun, hasil, belanja, baucar, dan laporan kewangan masjid dengan aliran kerja yang telus,
                        berstruktur, dan mudah diaudit.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center rounded-xl bg-white px-5 py-3 text-sm font-semibold text-indigo-900 shadow-lg shadow-indigo-900/30 transition hover:bg-indigo-50">
                            Login
                        </a>
                        <a href="#features"
                            class="inline-flex items-center rounded-xl border border-indigo-200/60 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                            View Features
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/20 bg-white/10 p-6 backdrop-blur">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="rounded-xl bg-white/10 p-4 text-white">
                            <p class="text-xs uppercase tracking-wider text-indigo-200">Module</p>
                            <p class="mt-1 font-semibold">Hasil and Belanja</p>
                        </div>
                        <div class="rounded-xl bg-white/10 p-4 text-white">
                            <p class="text-xs uppercase tracking-wider text-indigo-200">Workflow</p>
                            <p class="mt-1 font-semibold">Baucar Approval</p>
                        </div>
                        <div class="rounded-xl bg-white/10 p-4 text-white">
                            <p class="text-xs uppercase tracking-wider text-indigo-200">Monitoring</p>
                            <p class="mt-1 font-semibold">Audit Trail</p>
                        </div>
                        <div class="rounded-xl bg-white/10 p-4 text-white">
                            <p class="text-xs uppercase tracking-wider text-indigo-200">Insight</p>
                            <p class="mt-1 font-semibold">Financial Reports</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <section id="features" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mb-8 text-center">
            <h3 class="text-3xl font-bold text-gray-900">Features</h3>
            <p class="mt-2 text-gray-500">Direka untuk operasi kewangan masjid yang kemas dan profesional.</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <article class="rounded-xl bg-white p-6 shadow">
                <h4 class="font-semibold text-gray-900">Akaun Management</h4>
                <p class="mt-2 text-sm text-gray-600">Pengurusan akaun tunai dan bank untuk setiap masjid dengan
                    struktur yang jelas.</p>
            </article>
            <article class="rounded-xl bg-white p-6 shadow">
                <h4 class="font-semibold text-gray-900">Hasil and Belanja Tracking</h4>
                <p class="mt-2 text-sm text-gray-600">Jejak transaksi masuk dan keluar secara tersusun dengan rujukan
                    yang lengkap.</p>
            </article>
            <article class="rounded-xl bg-white p-6 shadow">
                <h4 class="font-semibold text-gray-900">Baucar Approval</h4>
                <p class="mt-2 text-sm text-gray-600">Proses kelulusan pembayaran yang lebih teratur dan boleh diaudit.
                </p>
            </article>
            <article class="rounded-xl bg-white p-6 shadow">
                <h4 class="font-semibold text-gray-900">Financial Reports</h4>
                <p class="mt-2 text-sm text-gray-600">Laporan kewangan mudah ditapis mengikut tarikh, akaun, dan masjid.
                </p>
            </article>
            <article class="rounded-xl bg-white p-6 shadow">
                <h4 class="font-semibold text-gray-900">Audit Trail</h4>
                <p class="mt-2 text-sm text-gray-600">Setiap tindakan penting direkodkan untuk ketelusan dan pematuhan.
                </p>
            </article>
            <article class="rounded-xl bg-white p-6 shadow">
                <h4 class="font-semibold text-gray-900">Role-based Access</h4>
                <p class="mt-2 text-sm text-gray-600">Kawalan akses berdasarkan peranan untuk keselamatan operasi
                    harian.</p>
            </article>
        </div>
    </section>

    <footer class="border-t border-gray-200 bg-gray-50">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-8 text-sm text-gray-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <p>Masjid Finance System Demo</p>
            <div class="flex items-center gap-4">
                <a href="#features" class="hover:text-gray-700">Features</a>
                <a href="{{ route('login') }}" class="hover:text-gray-700">Login</a>
                <a href="#" class="hover:text-gray-700">Contact</a>
            </div>
        </div>
    </footer>
</body>

</html>
