<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Role</h2>
            <p class="mt-1 text-sm text-gray-500">Define a role and assign permissions dynamically.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Role Configuration</h3>
                    <p class="mt-1 text-sm text-slate-600">Permissions are grouped by module prefix for quick assignment.</p>
                </div>

                <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-6">
                    @csrf

                    <div class="px-6 py-6">
                        @include('admin.roles._form')
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4">
                        <a href="{{ route('admin.roles.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <x-primary-button>Create Role</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
