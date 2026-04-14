<!-- Users Management Page with Premium UI -->
<x-app-layout>
    <div class="space-y-6">
        <!-- Breadcrumb Navigation -->
        @include('components.breadcrumb', [
            'items' => [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Users', 'url' => '#']],
        ])

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
                <p class="mt-1 text-gray-600">Manage system users and permissions</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <button @click="modals['createUser'] = true"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add User
                </button>
            </div>
        </div>

        <!-- Filters & Search (Optional) -->
        <div class="bg-white rounded-lg shadow p-4 flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="search" placeholder="Search users by name or email..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="sm:w-48">
                <select
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Roles</option>
                    <option value="admin">Administrator</option>
                    <option value="user">User</option>
                    <option value="viewer">Viewer</option>
                </select>
            </div>
        </div>

        <!-- Users Table -->
        @php
            $users = [
                [
                    'id' => 1,
                    'name' => 'Ahmad Hassan',
                    'email' => 'ahmad@finance.gov.my',
                    'role' => 'Administrator',
                    'status' => 'active',
                    'joined' => '2024-01-15 10:30:00',
                ],
                [
                    'id' => 2,
                    'name' => 'Fatimah Binti Ali',
                    'email' => 'fatimah@finance.gov.my',
                    'role' => 'Finance Officer',
                    'status' => 'active',
                    'joined' => '2024-02-20 14:15:00',
                ],
                [
                    'id' => 3,
                    'name' => 'Muhammad Ismail',
                    'email' => 'ismail@finance.gov.my',
                    'role' => 'Analyst',
                    'status' => 'pending',
                    'joined' => '2024-03-10 09:45:00',
                ],
                [
                    'id' => 4,
                    'name' => 'Siti Nurhaliza',
                    'email' => 'siti@finance.gov.my',
                    'role' => 'User',
                    'status' => 'inactive',
                    'joined' => '2023-12-05 11:20:00',
                ],
                [
                    'id' => 5,
                    'name' => 'Hassan Mohamed',
                    'email' => 'hassan@finance.gov.my',
                    'role' => 'Supervisor',
                    'status' => 'active',
                    'joined' => '2024-01-20 16:30:00',
                ],
            ];
        @endphp

        @include('components.data-table', [
            'columns' => [
                ['key' => 'name', 'label' => 'User', 'type' => 'avatar'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'role', 'label' => 'Role'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ['key' => 'joined', 'label' => 'Joined', 'type' => 'date'],
            ],
            'rows' => $users,
            'actions' => [
                ['type' => 'link', 'label' => 'Edit', 'url' => fn($row) => '#'],
                ['type' => 'link', 'label' => 'View', 'url' => fn($row) => '#'],
                ['type' => 'button', 'label' => 'Delete'],
            ],
        ])

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-stat-card title="Total Users" value="124" subtitle="Active and inactive" color="blue"
                icon="<svg class='w-8 h-8 text-blue-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM4 20h16a2 2 0 002-2v-2a3 3 0 00-5.856-1.487M13 7a4 4 0 11-8 0 4 4 0 018 0z'/></svg>" />

            <x-stat-card title="Active Users" value="98" subtitle="79% of total" :trend="['direction' => 'up', 'value' => 5]" color="green"
                icon="<svg class='w-8 h-8 text-green-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>" />

            <x-stat-card title="Pending Access" value="8" subtitle="Awaiting approval" color="yellow"
                icon="<svg class='w-8 h-8 text-yellow-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>" />
        </div>
    </div>

    <!-- Create User Modal -->
    @push('modals')
        @include('components.modal-form', [
            'id' => 'createUser',
            'title' => 'Create New User',
            'size' => 'lg',
        ])
        <form @submit.prevent="submitForm" class="space-y-4">
            <!-- Name -->
            @include('components.form-input', [
                'label' => 'Full Name',
                'name' => 'name',
                'placeholder' => 'Enter full name',
                'required' => true,
            ])

            <!-- Email -->
            @include('components.form-input', [
                'label' => 'Email Address',
                'name' => 'email',
                'type' => 'email',
                'placeholder' => 'user@finance.gov.my',
                'required' => true,
            ])

            <!-- Role Selection -->
            @include('components.form-input', [
                'label' => 'Role',
                'name' => 'role',
                'type' => 'select',
                'required' => true,
            ])
            <option value="">-- Select Role --</option>
            <option value="admin">Administrator</option>
            <option value="officer">Finance Officer</option>
            <option value="analyst">Analyst</option>
            <option value="user">User</option>
            <option value="viewer">Viewer</option>
            @endinclude

            <!-- Status -->
            <div>
                <p class="block text-sm font-medium text-gray-700 mb-2">Status</p>
                @include('components.radio', [
                    'label' => 'Active',
                    'name' => 'status',
                    'value' => 'active',
                    'checked' => true,
                ])
                <div class="mt-2">
                    @include('components.radio', [
                        'label' => 'Inactive',
                        'name' => 'status',
                        'value' => 'inactive',
                    ])
                </div>
            </div>

            <!-- Permissions -->
            <div>
                <p class="block text-sm font-medium text-gray-700 mb-3">Permissions</p>
                <div class="space-y-2">
                    @include('components.checkbox', [
                        'label' => 'View Dashboard',
                        'name' => 'permission[]',
                        'value' => 'dashboard.view',
                    ])

                    @include('components.checkbox', [
                        'label' => 'Manage Users',
                        'name' => 'permission[]',
                        'value' => 'users.manage',
                    ])

                    @include('components.checkbox', [
                        'label' => 'View Reports',
                        'name' => 'permission[]',
                        'value' => 'reports.view',
                    ])

                    @include('components.checkbox', [
                        'label' => 'Edit Transactions',
                        'name' => 'permission[]',
                        'value' => 'transactions.edit',
                    ])
                </div>
            </div>
        </form>

        @slot('footer')
            <button @click="modals['createUser'] = false" type="button"
                class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Create User
            </button>
        @endslot
        @endinclude
    @endpush
</x-app-layout>
