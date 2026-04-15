<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenantMasjid?->nama ? $tenantMasjid->nama . ' — ' : '' }}MyImam · Sistem Pengurusan Kewangan Masjid
    </title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 font-sans text-gray-900 antialiased">

    {{-- ── NAVBAR ──────────────────────────────────────────────────────── --}}
    <nav class="sticky top-0 z-50 border-b border-indigo-900/20 bg-indigo-950/95 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">

            {{-- Brand --}}
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 shadow-sm">
                    <svg viewBox="0 0 64 64" class="h-6 w-6" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 34 Q18 22 32 22 Q46 22 46 34 Z" fill="white" />
                        <rect x="18" y="34" width="28" height="12" rx="1" fill="white" />
                        <path d="M29 46 L29 40 Q32 37 35 40 L35 46 Z" fill="#4338ca" />
                        <rect x="13" y="28" width="4" height="18" rx="1" fill="white" />
                        <path d="M13 28 Q15 23 17 28 Z" fill="white" />
                        <circle cx="15" cy="23" r="1.5" fill="#f59e0b" />
                        <rect x="47" y="28" width="4" height="18" rx="1" fill="white" />
                        <path d="M47 28 Q49 23 51 28 Z" fill="white" />
                        <circle cx="49" cy="23" r="1.5" fill="#f59e0b" />
                        <circle cx="32" cy="19" r="2" fill="#f59e0b" />
                    </svg>
                </div>
                <div>
                    <p class="text-base font-bold leading-none text-white">MyImam</p>
                    <p class="text-[10px] leading-none text-indigo-300">
                        {{ $tenantMasjid?->nama ?? 'Sistem Kewangan Masjid' }}
                    </p>
                </div>
            </div>

            {{-- Nav Actions --}}
            <div class="flex items-center gap-2">
                <a href="#features"
                    class="hidden rounded-lg px-4 py-2 text-sm font-medium text-indigo-200 transition hover:text-white sm:block">
                    Ciri-Ciri
                </a>
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        Papan Pemuka
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        Log Masuk
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ── HERO ────────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden bg-indigo-950">

        {{-- Decorative blobs --}}
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-20 top-0 h-80 w-80 rounded-full bg-indigo-700/30 blur-3xl"></div>
            <div class="absolute -right-10 bottom-0 h-96 w-96 rounded-full bg-amber-500/15 blur-3xl"></div>
            <div class="absolute left-1/2 top-1/3 h-64 w-64 -translate-x-1/2 rounded-full bg-sky-700/20 blur-3xl"></div>
        </div>

        {{-- Subtle geometric pattern --}}
        <div class="pointer-events-none absolute inset-0 opacity-[0.04]"
            style="background-image:url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='1' fill-rule='evenodd'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E\")">
        </div>

        <div class="relative mx-auto max-w-7xl px-4 pb-28 pt-16 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">

                {{-- Hero Text --}}
                <div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-indigo-400/30 bg-indigo-800/50 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-indigo-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        Sistem Pengurusan Kewangan Masjid
                    </div>

                    <h1
                        class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                        {{ $landing['hero_title'] }}
                    </h1>

                    @if (!empty($landing['hero_subtitle']))
                        <p class="mt-5 max-w-xl text-base leading-relaxed text-indigo-200 sm:text-lg">
                            {{ $landing['hero_subtitle'] }}
                        </p>
                    @endif

                    <div class="mt-10 flex flex-wrap gap-4">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-6 py-3 text-sm font-bold text-slate-900 shadow-lg shadow-amber-500/25 transition hover:bg-amber-300">
                            {{ $landing['hero_cta_text'] ?: 'Log Masuk Sekarang' }}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                        <a href="#features"
                            class="inline-flex items-center rounded-xl border border-indigo-400/40 px-6 py-3 text-sm font-semibold text-indigo-100 transition hover:border-indigo-300 hover:bg-white/5">
                            Lihat Ciri-Ciri
                        </a>
                    </div>

                    {{-- Trust badges --}}
                    <div class="mt-10 flex flex-wrap items-center gap-6 text-xs text-indigo-300">
                        @foreach (['Data Selamat & Terenkripsi', 'Multi-Tenant SaaS', 'Laporan Automatik'] as $badge)
                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M16.403 12.652a3 3 0 0 0 0-5.304 3 3 0 0 0-3.75-3.751 3 3 0 0 0-5.305 0 3 3 0 0 0-3.751 3.75 3 3 0 0 0 0 5.305 3 3 0 0 0 3.75 3.751 3 3 0 0 0 5.305 0 3 3 0 0 0 3.751-3.75Zm-2.546-4.46a.75.75 0 0 0-1.214-.883l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" />
                                </svg>
                                {{ $badge }}
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Hero Visual --}}
                <div class="relative">
                    <div
                        class="relative overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-indigo-950/50 backdrop-blur-sm">

                        @if (!empty($landing['hero_image']))
                            <div class="mb-5 overflow-hidden rounded-2xl border border-white/10">
                                <img src="{{ $landing['hero_image'] }}" alt="{{ $tenantMasjid?->nama ?? 'MyImam' }}"
                                    class="h-48 w-full object-cover" />
                            </div>
                        @else
                            {{-- Mosque illustration --}}
                            <div
                                class="mb-5 flex h-48 items-center justify-center overflow-hidden rounded-2xl bg-indigo-900/60">
                                <svg viewBox="0 0 200 130" class="w-full max-w-xs" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect width="200" height="130" fill="#1e1b4b" rx="8" />
                                    {{-- Stars --}}
                                    <circle cx="20" cy="15" r="1" fill="#fbbf24" opacity="0.7" />
                                    <circle cx="50" cy="8" r="1.2" fill="#fbbf24" opacity="0.9" />
                                    <circle cx="80" cy="18" r="0.8" fill="white" opacity="0.6" />
                                    <circle cx="130" cy="10" r="1" fill="white" opacity="0.7" />
                                    <circle cx="160" cy="20" r="1.2" fill="#fbbf24" opacity="0.8" />
                                    <circle cx="185" cy="12" r="0.8" fill="white" opacity="0.5" />
                                    {{-- Crescent moon --}}
                                    <circle cx="170" cy="25" r="10" fill="#fbbf24" opacity="0.9" />
                                    <circle cx="175" cy="22" r="8" fill="#1e1b4b" />
                                    {{-- Ground --}}
                                    <rect x="0" y="105" width="200" height="25" fill="#312e81"
                                        rx="2" />
                                    {{-- Left minaret --}}
                                    <rect x="30" y="50" width="12" height="55" fill="#a5b4fc"
                                        rx="2" />
                                    <path d="M30 50 Q36 35 42 50 Z" fill="#c7d2fe" />
                                    <circle cx="36" cy="33" r="3" fill="#fbbf24" />
                                    {{-- Right minaret --}}
                                    <rect x="158" y="50" width="12" height="55" fill="#a5b4fc"
                                        rx="2" />
                                    <path d="M158 50 Q164 35 170 50 Z" fill="#c7d2fe" />
                                    <circle cx="164" cy="33" r="3" fill="#fbbf24" />
                                    {{-- Dome --}}
                                    <path d="M70 75 Q70 45 100 45 Q130 45 130 75 Z" fill="#c7d2fe" />
                                    <path d="M80 75 Q80 55 100 55 Q120 55 120 75 Z" fill="#e0e7ff" />
                                    {{-- Dome finial --}}
                                    <circle cx="100" cy="43" r="5" fill="#fbbf24" />
                                    <circle cx="103" cy="41" r="4" fill="#1e1b4b" />
                                    {{-- Prayer hall --}}
                                    <rect x="65" y="75" width="70" height="30" fill="#e0e7ff"
                                        rx="2" />
                                    {{-- Door --}}
                                    <path d="M91 105 L91 90 Q100 83 109 90 L109 105 Z" fill="#4338ca" />
                                    {{-- Windows --}}
                                    <rect x="72" y="80" width="10" height="12" fill="#a5b4fc"
                                        rx="2" />
                                    <rect x="118" y="80" width="10" height="12" fill="#a5b4fc"
                                        rx="2" />
                                </svg>
                            </div>
                        @endif

                        {{-- Feature pills --}}
                        <div class="grid grid-cols-2 gap-3">
                            @foreach (collect($landing['features'])->take(4) as $i => $feature)
                                <div class="rounded-xl bg-indigo-600/20 p-3 ring-1 ring-indigo-400/20">
                                    <div
                                        class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-500/30">
                                        <span class="text-xs font-bold text-amber-300">{{ $i + 1 }}</span>
                                    </div>
                                    <p class="text-xs font-semibold leading-tight text-indigo-100">{{ $feature }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Floating badge --}}
                    <div
                        class="absolute -right-3 -top-3 rounded-2xl border border-amber-400/30 bg-amber-400/10 px-3 py-2 text-xs font-semibold text-amber-300 shadow-lg backdrop-blur-sm">
                        ✦ Dipercayai Masjid
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── STATS BAND ──────────────────────────────────────────────────── --}}
    <div class="border-b border-indigo-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 text-center lg:grid-cols-4">
                @foreach ([['100+', 'Masjid Berdaftar'], ['50K+', 'Transaksi Diproses'], ['10K+', 'Laporan Dijana'], ['99.9%', 'Ketersediaan']] as [$value, $label])
                    <div>
                        <p class="text-3xl font-extrabold text-indigo-700">{{ $value }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── FEATURES ────────────────────────────────────────────────────── --}}
    <section id="features" class="bg-slate-50 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-14 text-center">
                <span
                    class="inline-block rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-indigo-700">
                    Ciri-Ciri
                </span>
                <h2 class="mt-4 text-3xl font-bold text-slate-900 sm:text-4xl">Ciri-Ciri Utama</h2>
                <p class="mx-auto mt-3 max-w-2xl text-slate-500">
                    Semua yang diperlukan untuk mengurus kewangan masjid secara cekap, telus, dan teratur.
                </p>
            </div>

            @php
                $featureIcons = [
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>',
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>',
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605"/>',
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>',
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>',
                ];
            @endphp

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($landing['features'] as $idx => $feature)
                    <article
                        class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition hover:border-indigo-200 hover:shadow-md">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100 transition group-hover:bg-indigo-600 group-hover:text-white group-hover:ring-indigo-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.6">
                                {!! $featureIcons[$idx % count($featureIcons)] !!}
                            </svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900">{{ $feature }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">
                            Diuruskan secara sistematik melalui platform MyImam yang selamat dan mudah digunakan.
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── CTA BAND ────────────────────────────────────────────────────── --}}
    <section class="bg-indigo-700 py-16">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-white sm:text-3xl">Mula Gunakan MyImam Hari Ini</h2>
            <p class="mt-3 text-indigo-200">Urus kewangan masjid anda dengan lebih cekap, telus dan tersusun.</p>
            <a href="{{ route('login') }}"
                class="mt-8 inline-flex items-center gap-2 rounded-xl bg-amber-400 px-8 py-3.5 text-sm font-bold text-slate-900 shadow-lg shadow-indigo-950/30 transition hover:bg-amber-300">
                {{ $landing['hero_cta_text'] ?: 'Log Masuk Sekarang' }}
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>
    </section>

    {{-- ── FOOTER ──────────────────────────────────────────────────────── --}}
    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col items-start gap-6 sm:flex-row sm:items-center sm:justify-between">

                {{-- Brand --}}
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600">
                        <svg viewBox="0 0 64 64" class="h-5 w-5" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 34 Q18 22 32 22 Q46 22 46 34 Z" fill="white" />
                            <rect x="18" y="34" width="28" height="12" rx="1" fill="white" />
                            <path d="M29 46 L29 40 Q32 37 35 40 L35 46 Z" fill="#4338ca" />
                            <rect x="13" y="28" width="4" height="18" rx="1" fill="white" />
                            <circle cx="15" cy="23" r="1.5" fill="#f59e0b" />
                            <rect x="47" y="28" width="4" height="18" rx="1" fill="white" />
                            <circle cx="49" cy="23" r="1.5" fill="#f59e0b" />
                            <circle cx="32" cy="19" r="2" fill="#f59e0b" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">MyImam</p>
                        <p class="text-xs text-slate-500">{{ $landing['footer_text'] }}</p>
                    </div>
                </div>

                {{-- Footer links --}}
                <div class="flex items-center gap-5 text-sm text-slate-500">
                    <a href="#features" class="transition hover:text-indigo-600">Ciri-Ciri</a>
                    <a href="{{ route('login') }}" class="transition hover:text-indigo-600">Log Masuk</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="transition hover:text-indigo-600">Papan Pemuka</a>
                    @endauth
                </div>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-6 text-center text-xs text-slate-400">
                © {{ date('Y') }} MyImam — Sistem Pengurusan Kewangan Masjid. Hak Cipta Terpelihara.
            </div>
        </div>
    </footer>

</body>

</html>
