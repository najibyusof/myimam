@php
    $financialRoutesAvailable = [
        'admin.akaun.index' => Route::has('admin.akaun.index'),
        'admin.hasil.index' => Route::has('admin.hasil.index'),
        'admin.belanja.index' => Route::has('admin.belanja.index'),
        'admin.tabung-khas.index' => Route::has('admin.tabung-khas.index'),
        'admin.program-masjid.index' => Route::has('admin.program-masjid.index'),
        'admin.reporting.index' => Route::has('admin.reporting.index'),
    ];
@endphp

<div class="flex h-full flex-col bg-gray-900 text-gray-100">
    <div class="flex h-16 items-center border-b border-gray-800 px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-800 text-indigo-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 101.414 1.414L4 10.414V17a1 1 0 001 1h3a1 1 0 001-1v-3h2v3a1 1 0 001 1h3a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-white">Imam</p>
                <p class="text-xs text-gray-400">Internal Panel</p>
            </div>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        <div class="space-y-1">
            <p class="px-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500">General</p>

            <a href="{{ route('dashboard') }}"
               class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('dashboard') ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-hover:text-gray-200" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 1.707a1 1 0 00-1.414 0l-7 7A1 1 0 003 10.414V17a1 1 0 001 1h12a1 1 0 001-1v-6.586a1 1 0 00-.293-.707l-7-7z" />
                </svg>
                <span>Dashboard</span>
            </a>

            @if (Route::has('notifications.index'))
                <a href="{{ route('notifications.index') }}"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('notifications.*') ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-hover:text-gray-200" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 2a6 6 0 00-6 6v3.586L2.293 13.293A1 1 0 003 15h14a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM8 16a2 2 0 104 0H8z" />
                    </svg>
                    <span>Notifications</span>
                </a>
            @endif
        </div>

        <div class="space-y-1">
            <p class="px-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500">Finance</p>

            @can('akaun.view')
                @if ($financialRoutesAvailable['admin.akaun.index'])
                    <a href="{{ route('admin.akaun.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('admin.akaun.*') ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a1 1 0 000 2h12a1 1 0 100-2H4zm-1 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm1 3a1 1 0 100 2h12a1 1 0 100-2H4z" clip-rule="evenodd" /></svg>
                        <span>Akaun</span>
                    </a>
                @endif
            @endcan

            @can('hasil.view')
                @if ($financialRoutesAvailable['admin.hasil.index'])
                    <a href="{{ route('admin.hasil.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('admin.hasil.*') ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3a1 1 0 011-1h1a1 1 0 011 1v13h2V8a1 1 0 011-1h1a1 1 0 011 1v8h2V5a1 1 0 011-1h1a1 1 0 011 1v11h1a1 1 0 110 2H3a1 1 0 110-2h1V3z" /></svg>
                        <span>Hasil</span>
                    </a>
                @endif
            @endcan

            @can('belanja.view')
                @if ($financialRoutesAvailable['admin.belanja.index'])
                    <a href="{{ route('admin.belanja.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('admin.belanja.*') ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3a1 1 0 102 0V7zm0 6a1 1 0 10-2 0 1 1 0 002 0z" clip-rule="evenodd" /></svg>
                        <span>Belanja</span>
                    </a>
                @endif
            @endcan

            @can('finance.view')
                @if (Route::has('admin.baucar-bayaran.index'))
                    <a href="{{ route('admin.baucar-bayaran.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('admin.baucar-bayaran.*') ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v8a2 2 0 002 2h4l2 2 2-2h4a2 2 0 002-2V5a2 2 0 00-2-2H4z" /></svg>
                        <span>Baucar Bayaran</span>
                    </a>
                @endif
            @endcan

            @can('tabung_khas.view')
                @if ($financialRoutesAvailable['admin.tabung-khas.index'])
                    <a href="{{ route('admin.tabung-khas.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('admin.tabung-khas.*') ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v1H2V6z" /><path fill-rule="evenodd" d="M2 9h16v5a2 2 0 01-2 2H4a2 2 0 01-2-2V9zm3 2a1 1 0 100 2h6a1 1 0 100-2H5z" clip-rule="evenodd" /></svg>
                        <span>Tabung Khas</span>
                    </a>
                @endif
            @endcan

            @can('program_masjid.view')
                @if ($financialRoutesAvailable['admin.program-masjid.index'])
                    <a href="{{ route('admin.program-masjid.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('admin.program-masjid.*') ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zM18 9H2v7a2 2 0 002 2h12a2 2 0 002-2V9z" clip-rule="evenodd" /></svg>
                        <span>Program Masjid</span>
                    </a>
                @endif
            @endcan

            @can('reports.view')
                @if ($financialRoutesAvailable['admin.reporting.index'])
                    <a href="{{ route('admin.reporting.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('admin.reporting.*') ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h3a1 1 0 011 1v5H3a1 1 0 01-1-1v-4zM8 6a1 1 0 011-1h3a1 1 0 011 1v10H9a1 1 0 01-1-1V6zM14 3a1 1 0 011-1h3a1 1 0 011 1v13a1 1 0 01-1 1h-4V3z" /></svg>
                        <span>Reports</span>
                    </a>
                @endif
            @endcan
        </div>

        <div class="space-y-1">
            <p class="px-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500">Administration</p>

            @can('settings.manage')
                <a href="{{ route('profile.edit') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('profile.*') ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17a1 1 0 00-1.98 0l-.2 1.197a1 1 0 01-.95.816l-1.204.07a1 1 0 00-.56 1.76l.91.79a1 1 0 01.32 1.042l-.3 1.17a1 1 0 001.45 1.11l1.05-.56a1 1 0 01.94 0l1.05.56a1 1 0 001.45-1.11l-.3-1.17a1 1 0 01.32-1.042l.91-.79a1 1 0 00-.56-1.76l-1.204-.07a1 1 0 01-.95-.816l-.2-1.197zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" /></svg>
                    <span>Settings</span>
                </a>
            @endcan

            @role('Admin')
                @if (Route::has('admin.users.index'))
                    <a href="{{ route('admin.users.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('admin.users.*') ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M13 7a3 3 0 11-6 0 3 3 0 016 0zM5 14a4 4 0 018 0v1H5v-1z" /><path d="M15 8a2 2 0 100-4 2 2 0 000 4zM15 14a3 3 0 013 3v1h-4v-1a5 5 0 00-1-3h2z" /></svg>
                        <span>User Management</span>
                    </a>
                @endif
            @endrole

            @can('roles.assign')
                @if (Route::has('admin.roles.index'))
                    <a href="{{ route('admin.roles.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('admin.roles.*') ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zM9 8a1 1 0 012 0v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1H8a1 1 0 110-2h1V8z" clip-rule="evenodd" /></svg>
                        <span>Role Management</span>
                    </a>
                @endif
            @endcan
        </div>
    </nav>
</div>
