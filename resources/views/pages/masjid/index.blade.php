<!-- Masjid Management Page -->
<x-app-layout>
    <div class="space-y-6">
        <!-- Breadcrumb -->
        @include('components.breadcrumb', [
            'items' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Masjid Management', 'url' => '#'],
            ],
        ])

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Masjid Management</h1>
                <p class="mt-1 text-gray-600">Manage mosque data and information</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <button @click="modals['addMasjid'] = true"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Masjid
                </button>
            </div>
        </div>

        <!-- Alerts -->
        @include('components.alert', [
            'type' => 'info',
            'title' => 'Maintenance Notice',
            'closeable' => true,
        ])
        System maintenance scheduled for tonight 10 PM - 12 AM. Some services may be unavailable.
        @endinclude

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-stat-card title="Total Masjid" value="42" subtitle="Registered" :trend="['direction' => 'up', 'value' => 8]" color="blue"
                icon="<svg class='w-8 h-8 text-blue-600' fill='currentColor' viewBox='0 0 24 24'><path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'/></svg>" />

            <x-stat-card title="Active Programs" value="156" subtitle="This month" :trend="['direction' => 'up', 'value' => 12]" color="green"
                icon="<svg class='w-8 h-8 text-green-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>" />

            <x-stat-card title="Participants" value="2,340" subtitle="Registered members" :trend="['direction' => 'up', 'value' => 6]" color="purple"
                icon="<svg class='w-8 h-8 text-purple-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM4 20h16a2 2 0 002-2v-2a3 3 0 00-5.856-1.487M13 7a4 4 0 11-8 0 4 4 0 018 0z'/></svg>" />

            <x-stat-card title="Completion Rate" value="87%" subtitle="Program completion" :trend="['direction' => 'up', 'value' => 3]"
                color="indigo"
                icon="<svg class='w-8 h-8 text-indigo-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'/></svg>" />
        </div>

        <!-- Masjid List Table -->
        @php
            $masjids = [
                [
                    'id' => 1,
                    'name' => 'Masjid Negara',
                    'location' => 'Kuala Lumpur',
                    'capacity' => '10,000',
                    'status' => 'active',
                    'programs' => '18',
                    'members' => '2,450',
                ],
                [
                    'id' => 2,
                    'name' => 'Masjid Al-Azhar',
                    'location' => 'Petaling Jaya',
                    'capacity' => '5,000',
                    'status' => 'active',
                    'programs' => '12',
                    'members' => '1,230',
                ],
                [
                    'id' => 3,
                    'name' => 'Masjid Jamek',
                    'location' => 'Kuala Lumpur',
                    'capacity' => '3,000',
                    'status' => 'active',
                    'programs' => '8',
                    'members' => '890',
                ],
                [
                    'id' => 4,
                    'name' => 'Masjid Putra',
                    'location' => 'Putrajaya',
                    'capacity' => '8,000',
                    'status' => 'pending',
                    'programs' => '5',
                    'members' => '450',
                ],
                [
                    'id' => 5,
                    'name' => 'Masjid Sentosa',
                    'location' => 'Seberang Perai',
                    'capacity' => '2,000',
                    'status' => 'inactive',
                    'programs' => '0',
                    'members' => '0',
                ],
            ];
        @endphp

        @include('components.data-table', [
            'columns' => [
                ['key' => 'name', 'label' => 'Masjid Name', 'type' => 'link'],
                ['key' => 'location', 'label' => 'Location'],
                ['key' => 'capacity', 'label' => 'Capacity'],
                ['key' => 'members', 'label' => 'Members'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
            ],
            'rows' => $masjids,
            'actions' => [
                ['type' => 'link', 'label' => 'View', 'url' => fn($row) => '#'],
                ['type' => 'link', 'label' => 'Edit', 'url' => fn($row) => '#'],
                ['type' => 'button', 'label' => 'Delete'],
            ],
        ])
    </div>

    <!-- Add Masjid Modal -->
    @push('modals')
        @include('components.modal-form', [
            'id' => 'addMasjid',
            'title' => 'Add New Masjid',
            'size' => 'xl',
        ])
        <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Masjid Name -->
                @include('components.form-input', [
                    'label' => 'Masjid Name',
                    'name' => 'name',
                    'placeholder' => 'Enter masjid name',
                    'required' => true,
                ])

                <!-- Location -->
                @include('components.form-input', [
                    'label' => 'Location',
                    'name' => 'location',
                    'placeholder' => 'City/State',
                    'required' => true,
                ])

                <!-- Contact Person -->
                @include('components.form-input', [
                    'label' => 'Contact Person',
                    'name' => 'contact_person',
                    'placeholder' => 'Full name',
                    'required' => true,
                ])

                <!-- Phone Number -->
                @include('components.form-input', [
                    'label' => 'Phone Number',
                    'name' => 'phone',
                    'type' => 'tel',
                    'placeholder' => '+60...',
                    'required' => true,
                ])

                <!-- Prayer Capacity -->
                @include('components.form-input', [
                    'label' => 'Prayer Capacity',
                    'name' => 'capacity',
                    'type' => 'number',
                    'placeholder' => '0',
                    'required' => true,
                ])

                <!-- Email -->
                @include('components.form-input', [
                    'label' => 'Email Address',
                    'name' => 'email',
                    'type' => 'email',
                    'placeholder' => 'contact@masjid.com',
                ])
            </div>

            <!-- Description -->
            @include('components.form-input', [
                'label' => 'Description',
                'name' => 'description',
                'type' => 'textarea',
                'placeholder' => 'Provide additional details about the masjid...',
            ])

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
                        'label' => 'Pending',
                        'name' => 'status',
                        'value' => 'pending',
                    ])
                </div>
            </div>

            <!-- Checkbox -->
            @include('components.checkbox', [
                'label' => 'Enable Prayer Notifications',
                'name' => 'enable_notifications',
                'value' => '1',
            ])
        </form>

        @slot('footer')
            <button @click="modals['addMasjid'] = false" type="button"
                class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Create Masjid
            </button>
        @endslot
        @endinclude
    @endpush
</x-app-layout>
