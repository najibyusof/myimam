<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    @php
        $notificationUser = Auth::user();
        $notificationUnreadCount = $notificationUser?->unreadNotificationsCount() ?? 0;
        $notificationDropdownItems = $notificationUser
            ? $notificationUser->appNotifications()->latest('created_at')->limit(5)->get()
            : collect();
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @role('Admin')
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Users') }}
                        </x-nav-link>
                    @endrole

                    @can('roles.assign')
                        <x-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')">
                            {{ __('Roles & Permissions') }}
                        </x-nav-link>
                    @endcan

                    @can('masjid.view')
                        <x-nav-link :href="route('admin.masjid.index')" :active="request()->routeIs('admin.masjid.*')">
                            {{ __('Masjid') }}
                        </x-nav-link>
                    @endcan

                    @can('akaun.view')
                        <x-nav-link :href="route('admin.akaun.index')" :active="request()->routeIs('admin.akaun.*')">
                            {{ __('Akaun') }}
                        </x-nav-link>
                    @endcan

                    @can('hasil.view')
                        <x-nav-link :href="route('admin.hasil.index')" :active="request()->routeIs('admin.hasil.*')">
                            {{ __('Hasil') }}
                        </x-nav-link>
                    @endcan

                    @can('belanja.view')
                        <x-nav-link :href="route('admin.belanja.index')" :active="request()->routeIs('admin.belanja.*')">
                            {{ __('Belanja') }}
                        </x-nav-link>
                    @endcan

                    @can('sumber_hasil.view')
                        <x-nav-link :href="route('admin.sumber-hasil.index')" :active="request()->routeIs('admin.sumber-hasil.*')">
                            {{ __('Sumber Hasil') }}
                        </x-nav-link>
                    @endcan

                    @can('kategori_belanja.view')
                        <x-nav-link :href="route('admin.kategori-belanja.index')" :active="request()->routeIs('admin.kategori-belanja.*')">
                            {{ __('Kategori Belanja') }}
                        </x-nav-link>
                    @endcan

                    @can('tabung_khas.view')
                        <x-nav-link :href="route('admin.tabung-khas.index')" :active="request()->routeIs('admin.tabung-khas.*')">
                            {{ __('Tabung Khas') }}
                        </x-nav-link>
                    @endcan

                    @can('program_masjid.view')
                        <x-nav-link :href="route('admin.program-masjid.index')" :active="request()->routeIs('admin.program-masjid.*')">
                            {{ __('Program Masjid') }}
                        </x-nav-link>
                    @endcan

                    @can('pindahan_akaun.view')
                        <x-nav-link :href="route('admin.pindahan-akaun.index')" :active="request()->routeIs('admin.pindahan-akaun.*')">
                            {{ __('Pindahan Akaun') }}
                        </x-nav-link>
                    @endcan

                    @can('running_no.view')
                        <x-nav-link :href="route('admin.running-no.index')" :active="request()->routeIs('admin.running-no.*')">
                            {{ __('Nombor Rujukan') }}
                        </x-nav-link>
                    @endcan

                    @can('audit.view')
                        <x-nav-link :href="route('admin.log-aktiviti.index')" :active="request()->routeIs('admin.log-aktiviti.*')">
                            {{ __('Log Aktiviti') }}
                        </x-nav-link>
                    @endcan

                    @can('reports.view')
                        <x-nav-link :href="route('admin.reporting.index')" :active="request()->routeIs('admin.reporting.*')">
                            {{ __('Reporting') }}
                        </x-nav-link>
                    @endcan

                    <x-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                        {{ __('Notifications') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <x-dropdown align="right" width="w-80" contentClasses="py-0 bg-white">
                    <x-slot name="trigger">
                        <button
                            class="relative inline-flex items-center rounded-full border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if ($notificationUnreadCount > 0)
                                <span
                                    class="absolute -top-1 -right-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[11px] font-semibold text-white">
                                    {{ min($notificationUnreadCount, 99) }}
                                </span>
                            @endif
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-slate-200 px-4 py-3">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-900">Notifications</p>
                                <span class="text-xs text-slate-500">{{ $notificationUnreadCount }} unread</span>
                            </div>
                        </div>

                        <div class="max-h-80 overflow-y-auto">
                            @forelse ($notificationDropdownItems as $notificationItem)
                                @php
                                    $notificationData = is_array($notificationItem->data)
                                        ? $notificationItem->data
                                        : [];
                                    $notificationTitle =
                                        (string) ($notificationData['title'] ??
                                            ($notificationData['subject'] ?? 'System Notification'));
                                    $notificationMessage =
                                        (string) ($notificationData['message'] ??
                                            ($notificationData['body'] ?? 'No additional details.'));
                                @endphp
                                <div
                                    class="px-4 py-3 {{ $notificationItem->read_at ? 'bg-white' : 'bg-sky-50' }} border-b border-slate-100">
                                    <p class="text-sm font-medium text-slate-800">{{ $notificationTitle }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $notificationMessage }}</p>
                                    <p class="mt-1 text-[11px] text-slate-400">
                                        {{ $notificationItem->created_at?->diffForHumans() }}</p>
                                </div>
                            @empty
                                <p class="px-4 py-5 text-sm text-slate-500">No notifications yet.</p>
                            @endforelse
                        </div>

                        <div class="flex items-center justify-between gap-2 border-t border-slate-200 px-4 py-3">
                            <form method="POST" action="{{ route('notifications.read-all') }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="text-xs font-medium text-slate-600 hover:text-slate-800">Mark all as
                                    read</button>
                            </form>
                            <a href="{{ route('notifications.index') }}"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">
                                View all
                            </a>
                        </div>
                    </x-slot>
                </x-dropdown>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('two-factor.edit')">
                            {{ __('Two-Factor Authentication') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @role('Admin')
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Users') }}
                </x-responsive-nav-link>
            @endrole

            @can('roles.assign')
                <x-responsive-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')">
                    {{ __('Roles & Permissions') }}
                </x-responsive-nav-link>
            @endcan

            @can('masjid.view')
                <x-responsive-nav-link :href="route('admin.masjid.index')" :active="request()->routeIs('admin.masjid.*')">
                    {{ __('Masjid') }}
                </x-responsive-nav-link>
            @endcan

            @can('akaun.view')
                <x-responsive-nav-link :href="route('admin.akaun.index')" :active="request()->routeIs('admin.akaun.*')">
                    {{ __('Akaun') }}
                </x-responsive-nav-link>
            @endcan

            @can('hasil.view')
                <x-responsive-nav-link :href="route('admin.hasil.index')" :active="request()->routeIs('admin.hasil.*')">
                    {{ __('Hasil') }}
                </x-responsive-nav-link>
            @endcan

            @can('belanja.view')
                <x-responsive-nav-link :href="route('admin.belanja.index')" :active="request()->routeIs('admin.belanja.*')">
                    {{ __('Belanja') }}
                </x-responsive-nav-link>
            @endcan

            @can('sumber_hasil.view')
                <x-responsive-nav-link :href="route('admin.sumber-hasil.index')" :active="request()->routeIs('admin.sumber-hasil.*')">
                    {{ __('Sumber Hasil') }}
                </x-responsive-nav-link>
            @endcan

            @can('kategori_belanja.view')
                <x-responsive-nav-link :href="route('admin.kategori-belanja.index')" :active="request()->routeIs('admin.kategori-belanja.*')">
                    {{ __('Kategori Belanja') }}
                </x-responsive-nav-link>
            @endcan

            @can('tabung_khas.view')
                <x-responsive-nav-link :href="route('admin.tabung-khas.index')" :active="request()->routeIs('admin.tabung-khas.*')">
                    {{ __('Tabung Khas') }}
                </x-responsive-nav-link>
            @endcan

            @can('program_masjid.view')
                <x-responsive-nav-link :href="route('admin.program-masjid.index')" :active="request()->routeIs('admin.program-masjid.*')">
                    {{ __('Program Masjid') }}
                </x-responsive-nav-link>
            @endcan

            <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                {{ __('Notifications') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('two-factor.edit')">
                    {{ __('Two-Factor Authentication') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
