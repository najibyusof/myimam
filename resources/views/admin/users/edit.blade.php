<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit User</h2>
                <p class="mt-1 text-sm text-gray-500">Manage role assignment, status, password reset, and account
                    details.</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                My profile
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->has('email'))
                <div class="rounded-md bg-red-50 p-3 text-sm text-red-800">{{ $errors->first('email') }}</div>
            @endif

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">{{ $user->name }}</h3>
                            <p class="text-sm text-slate-600">{{ $user->email }}</p>
                        </div>
                        <span
                            class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $user->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                            {{ $user->aktif ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="px-6 py-6">
                        @include('admin.users._form', ['user' => $user])
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4">
                        <a href="{{ route('admin.users.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Back
                        </a>
                        <x-primary-button>Save Changes</x-primary-button>
                    </div>
                </form>
            </div>

            @if (auth()->id() !== $user->id)
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-slate-900">Account Status</h3>
                        <p class="mt-1 text-sm text-slate-600">Toggle access without changing user details.</p>
                        <form method="POST" action="{{ route('admin.users.status', $user) }}" class="mt-4">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="inline-flex items-center rounded-md {{ $user->aktif ? 'bg-slate-900 hover:bg-slate-800' : 'bg-emerald-600 hover:bg-emerald-500' }} px-4 py-2 text-sm font-semibold text-white transition">
                                {{ $user->aktif ? 'Deactivate User' : 'Activate User' }}
                            </button>
                        </form>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-slate-900">Reset Password</h3>
                        <p class="mt-1 text-sm text-slate-600">Send a password reset link to the user's email address.
                        </p>
                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="mt-4">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                Send Reset Link
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
