@props(['header' => null])

<div x-data="{ mobileSidebarOpen: false }" class="flex h-screen bg-gray-100">
    <aside class="hidden w-72 shrink-0 md:block">
        <x-sidebar />
    </aside>

    <div x-show="mobileSidebarOpen" x-cloak class="fixed inset-0 z-40 md:hidden">
        <div @click="mobileSidebarOpen = false" class="absolute inset-0 bg-gray-900/50"></div>
        <aside class="absolute inset-y-0 left-0 z-50 w-72">
            <x-sidebar />
        </aside>
    </div>

    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
        <x-navbar />

        @if ($header)
            <div class="border-b border-gray-200 bg-white px-4 py-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        @endif

        <main class="min-h-0 flex-1 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>
</div>
