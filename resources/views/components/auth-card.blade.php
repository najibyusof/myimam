@props([
    'title' => 'Sistem Pengurusan Kewangan Masjid',
    'subtitle' => 'Platform kewangan masjid yang telus dan moden.',
    'leftTitle' => 'Masjid Finance',
])

<div class="min-h-screen bg-gray-100">
    <div class="mx-auto grid min-h-screen w-full max-w-7xl lg:grid-cols-2">
        <aside class="hidden lg:flex lg:flex-col lg:justify-between bg-gradient-to-br from-slate-900 via-indigo-900 to-slate-800 px-12 py-10 text-white">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-indigo-200">{{ $leftTitle }}</p>
                <h1 class="mt-3 text-4xl font-extrabold leading-tight">{{ $title }}</h1>
                <p class="mt-4 max-w-md text-indigo-100">{{ $subtitle }}</p>
            </div>

            <div class="space-y-3">
                <div class="rounded-xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-sm font-semibold">Transparent Operations</p>
                    <p class="mt-1 text-xs text-indigo-100">Audit-ready records for income, expenses and approvals.</p>
                </div>
                <div class="rounded-xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-sm font-semibold">Role-based Access</p>
                    <p class="mt-1 text-xs text-indigo-100">Secure module access for Admin, Bendahari, AJK and Auditor.</p>
                </div>
            </div>
        </aside>

        <section class="flex items-center justify-center px-4 py-10 sm:px-6 lg:px-10">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg sm:p-8">
                {{ $slot }}
            </div>
        </section>
    </div>
</div>
