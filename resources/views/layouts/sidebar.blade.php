<!-- Sidebar Navigation -->
<aside @click="sidebarOpen = true"
    class="w-64 bg-gradient-to-b from-indigo-900 via-indigo-800 to-indigo-900 text-white shadow-xl hidden md:flex flex-col">
    <!-- Logo -->
    <div class="px-6 py-6 border-b border-indigo-700">
        <div class="flex items-center space-x-3">
            <div
                class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-blue-500 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2h2v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold">Imam</h1>
                <p class="text-xs text-indigo-300">Finance Manager</p>
            </div>
        </div>
    </div>

    <!-- Navigation Items -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <a href="{{ route('dashboard') }}"
            class="nav-link group flex items-center px-4 py-3 rounded-lg transition-all duration-200 hover:bg-indigo-700 {{ request()->routeIs('dashboard') ? 'bg-indigo-700 bg-opacity-60' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m0 0l2-3m-2 3v10a1 1 0 01-1 1H6a1 1 0 01-1-1V9m0 0V5a1 1 0 011-1h12a1 1 0 011 1v4" />
            </svg>
            <span class="font-medium">Dashboard</span>
        </a>

        @role('Admin')
            <a href="{{ route('admin.users.index') }}"
                class="nav-link group flex items-center px-4 py-3 rounded-lg transition-all duration-200 hover:bg-indigo-700 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-700 bg-opacity-60' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span class="font-medium">Users</span>
            </a>
        @endrole

        @can('roles.assign')
            <a href="{{ route('admin.roles.index') }}"
                class="nav-link group flex items-center px-4 py-3 rounded-lg transition-all duration-200 hover:bg-indigo-700 {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-700 bg-opacity-60' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="font-medium">Roles & Permissions</span>
            </a>
        @endcan

        @can('masjid.view')
            <a href="{{ route('admin.masjid.index') }}"
                class="nav-link group flex items-center px-4 py-3 rounded-lg transition-all duration-200 hover:bg-indigo-700 {{ request()->routeIs('admin.masjid.*') ? 'bg-indigo-700 bg-opacity-60' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="font-medium">Masjid</span>
            </a>
        @endcan

        @can('akaun.view')
            <a href="{{ route('admin.akaun.index') }}"
                class="nav-link group flex items-center px-4 py-3 rounded-lg transition-all duration-200 hover:bg-indigo-700 {{ request()->routeIs('admin.akaun.*') ? 'bg-indigo-700 bg-opacity-60' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">Akaun</span>
            </a>
        @endcan

        @can('hasil.view')
            <x-sidebar-link :href="route('admin.hasil.index')" :active="request()->routeIs('admin.hasil.*')" icon="heroicon-o-arrow-trending-up">
                {{ __('Hasil') }}
            </x-sidebar-link>
        @endcan

        @can('belanja.view')
            <x-sidebar-link :href="route('admin.belanja.index')" :active="request()->routeIs('admin.belanja.*')" icon="heroicon-o-arrow-trending-down">
                {{ __('Belanja') }}
            </x-sidebar-link>
        @endcan

        @can('sumber_hasil.view')
            <a href="{{ route('admin.sumber-hasil.index') }}"
                class="nav-link group flex items-center px-4 py-3 rounded-lg transition-all duration-200 hover:bg-indigo-700 {{ request()->routeIs('admin.sumber-hasil.*') ? 'bg-indigo-700 bg-opacity-60' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.105 0-2 .672-2 1.5S10.895 11 12 11s2 .672 2 1.5S13.105 14 12 14m0-6V7m0 7v1m8-3a8 8 0 11-16 0 8 8 0 0116 0z" />
                </svg>
                <span class="font-medium">Sumber Hasil</span>
            </a>
        @endcan

        @can('kategori_belanja.view')
            <x-sidebar-link :href="route('admin.kategori-belanja.index')" :active="request()->routeIs('admin.kategori-belanja.*')" icon="heroicon-o-tag">
                {{ __('Kategori Belanja') }}
            </x-sidebar-link>
        @endcan

        @can('tabung_khas.view')
            <x-sidebar-link :href="route('admin.tabung-khas.index')" :active="request()->routeIs('admin.tabung-khas.*')" icon="heroicon-o-archive-box">
                {{ __('Tabung Khas') }}
            </x-sidebar-link>
        @endcan

        @can('program_masjid.view')
            <x-sidebar-link :href="route('admin.program-masjid.index')" :active="request()->routeIs('admin.program-masjid.*')" icon="heroicon-o-calendar-days">
                {{ __('Program Masjid') }}
            </x-sidebar-link>
        @endcan

        @can('pindahan_akaun.view')
            <x-sidebar-link :href="route('admin.pindahan-akaun.index')" :active="request()->routeIs('admin.pindahan-akaun.*')" icon="heroicon-o-arrows-right-left">
                {{ __('Pindahan Akaun') }}
            </x-sidebar-link>
        @endcan

        @can('running_no.view')
            <x-sidebar-link :href="route('admin.running-no.index')" :active="request()->routeIs('admin.running-no.*')" icon="heroicon-o-hashtag">
                {{ __('Nombor Rujukan') }}
            </x-sidebar-link>
        @endcan

        @can('audit.view')
            <x-sidebar-link :href="route('admin.log-aktiviti.index')" :active="request()->routeIs('admin.log-aktiviti.*')" icon="heroicon-o-clipboard-document-list">
                {{ __('Log Aktiviti') }}
            </x-sidebar-link>
        @endcan

        @can('reports.view')
            <x-sidebar-link :href="route('admin.reporting.index')" :active="request()->routeIs('admin.reporting.*')" icon="heroicon-o-chart-bar-square">
                {{ __('Reporting') }}
            </x-sidebar-link>
        @endcan

        <div class="px-4 py-3 text-xs font-semibold text-indigo-300 uppercase tracking-wider mt-6">
            Resources
        </div>

        <a href="#"
            class="nav-link flex items-center px-4 py-3 rounded-lg transition-all duration-200 hover:bg-indigo-700 text-indigo-200">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">Help & Support</span>
        </a>

        <a href="#"
            class="nav-link flex items-center px-4 py-3 rounded-lg transition-all duration-200 hover:bg-indigo-700 text-indigo-200">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="font-medium">Settings</span>
        </a>
    </nav>

    <!-- User Profile Footer -->
    <div class="px-6 py-4 border-t border-indigo-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div
                    class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-300 to-blue-400 flex items-center justify-center">
                    <span
                        class="text-sm font-bold text-indigo-900">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                </div>
                <div class="text-sm">
                    <p class="font-medium">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-indigo-300">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <button @click="$dispatch('open-profile')" class="p-1.5 hover:bg-indigo-700 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </div>
</aside>

<!-- Mobile Menu Button & Overlay -->
<div class="md:hidden fixed inset-0 z-20 pointer-events-none" :class="{ 'pointer-events-auto': mobileMenuOpen }">
    <div @click="mobileMenuOpen = false" :class="{ 'bg-black bg-opacity-50': mobileMenuOpen }"
        class="absolute inset-0 transition-opacity duration-300"></div>
</div>
