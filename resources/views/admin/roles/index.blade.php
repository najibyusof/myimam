<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Role & Permission Management</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Hierarchical role management.
                    {{ $isSuperAdmin ? 'Viewing all roles across all tenants.' : 'Viewing roles scoped to your masjid.' }}
                </p>
            </div>
            @can('create', \App\Models\Role::class)
                <a href="{{ route('admin.roles.create') }}"
                    class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Role
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Legend (SuperAdmin only) --}}
            @if ($isSuperAdmin)
                <div
                    class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs">
                    <span class="font-semibold text-slate-600">Hierarchy levels:</span>
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 font-medium text-red-700">Level
                        1 — System (protected)</span>
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-2.5 py-0.5 font-medium text-indigo-700">Level
                        2 — Admin</span>
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 font-medium text-emerald-700">Level
                        3 — User</span>
                </div>
            @endif

            {{-- Search --}}
            <form method="GET" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_auto_auto]">
                    <x-text-input name="q" type="text" class="block w-full" placeholder="Search role name"
                        :value="$search" />
                    <x-primary-button>Search</x-primary-button>
                    <a href="{{ route('admin.roles.index') }}"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Role</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Level</th>
                            @if ($isSuperAdmin)
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Scope</th>
                            @endif
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Permissions</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Updated</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($roles as $role)
                            @php
                                $canUpdate = auth()->user()->can('update', $role);
                                $canDelete = auth()->user()->can('delete', $role);
                            @endphp
                            <tr class="{{ $role->isSystemLevel() ? 'bg-red-50/30' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900">{{ $role->name }}</span>
                                        @if ($role->isGlobal())
                                            <span
                                                class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">Global</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-semibold {{ \App\Models\Role::levelBadgeClass((int) $role->level) }}">
                                        {{ \App\Models\Role::levelLabel((int) $role->level) }}
                                    </span>
                                </td>
                                @if ($isSuperAdmin)
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ $role->masjid?->nama ?? '— Global —' }}
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $role->permissions_count }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $role->updated_at?->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @if ($canUpdate)
                                            <a href="{{ route('admin.roles.edit', $role) }}"
                                                class="rounded-md bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                Edit / Permissions
                                            </a>
                                        @else
                                            <span
                                                class="cursor-not-allowed rounded-md bg-gray-50 px-3 py-1 text-xs font-medium text-gray-400"
                                                title="You cannot edit this role">
                                                View only
                                            </span>
                                        @endif

                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                                onsubmit="return confirm('Delete role \'{{ addslashes($role->name) }}\'? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="rounded-md bg-red-50 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-100">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 6 : 5 }}"
                                    class="px-4 py-8 text-center text-sm text-gray-500">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $roles->links() }}
        </div>
    </div>
</x-app-layout>
