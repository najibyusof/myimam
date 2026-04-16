<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">User Management</h2>
                <p class="mt-1 text-sm text-gray-500">Admin-only user CRUD, role assignment, status control, and password
                    resets.</p>
            </div>
            @can('create', \App\Models\User::class)
                <a href="{{ route('admin.users.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Create User
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Total Users</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-emerald-700">Active</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ $stats['active'] }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Inactive</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['inactive'] }}</p>
                </div>
            </div>

            <form method="GET" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_180px_auto_auto]">
                    <x-text-input name="q" type="text" class="block w-full"
                        placeholder="Search by name or email" :value="$search" />
                    <select name="role"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All roles</option>
                        @foreach ($roles as $roleOption)
                            <option value="{{ $roleOption }}" @selected($role === $roleOption)>{{ $roleOption }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All status</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                    <x-primary-button>Search</x-primary-button>
                    <a href="{{ route('admin.users.index') }}"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Role</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Masjid</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Joined</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $user->masjid->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $user->aktif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $user->aktif ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $user->created_at?->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    @can('update', $user)
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    @endcan
                                    @can('toggleStatus', $user)
                                        <form method="POST" action="{{ route('admin.users.status', $user) }}"
                                            class="inline-block ml-3">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="text-amber-600 hover:text-amber-900">{{ $user->aktif ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                    @endcan
                                    @can('delete', $user)
                                        @if (auth()->id() !== $user->id)
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                class="inline-block ml-3" data-confirm="Delete this user?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
