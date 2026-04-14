<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Role</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Update role details and manage permission matrix.
                    @if (!$role->isGlobal())
                        <span
                            class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                            Tenant-scoped role
                        </span>
                    @else
                        <span
                            class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                            Global role
                        </span>
                    @endif
                </p>
            </div>
            @can('delete', $role)
                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                    onsubmit="return confirm('Permanently delete role \'{{ addslashes($role->name) }}\'? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-md border border-red-300 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m4-3h2a1 1 0 011 1v1H9V5a1 1 0 011-1h2z" />
                        </svg>
                        Delete Role
                    </button>
                </form>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($role->isSystemLevel())
                <div class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                    <strong>Protected role:</strong> This is a system-level role. Some restrictions may apply.
                </div>
            @endif

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">{{ $role->name }}</h3>
                    <p class="mt-1 text-sm text-slate-600">Guard: {{ $role->guard_name }}</p>
                </div>

                <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="px-6 py-6">
                        @include('admin.roles._form', [
                            'role' => $role,
                            'selectedPermissions' => $assignedPermissions,
                            'isSuperAdmin' => $isSuperAdmin,
                        ])
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4">
                        <a href="{{ route('admin.roles.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Back
                        </a>
                        <x-primary-button>Save Changes</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
