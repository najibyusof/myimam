<!-- Premium Dashboard Page -->
<x-app-layout>
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[['label' => 'Home', 'url' => route('admin.dashboard')]]" />

        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-1 text-gray-600">Welcome back, {{ Auth::user()->name }}!</p>
            </div>
            <button @click="modals['createUser'] = true"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition">
                + New Item
            </button>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-stat-card title="Total Users" value="2,543" subtitle="Active users" :trend="['direction' => 'up', 'value' => 12]" color="blue"
                icon="<svg class='w-8 h-8 text-blue-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM4 20h16a2 2 0 002-2v-2a3 3 0 00-5.856-1.487M13 7a4 4 0 11-8 0 4 4 0 018 0z'/></svg>" />

            <x-stat-card title="Revenue" value="RM 45,231" subtitle="This month" :trend="['direction' => 'up', 'value' => 8]" color="green"
                icon="<svg class='w-8 h-8 text-green-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>" />

            <x-stat-card title="Orders" value="1,240" subtitle="Total orders" :trend="['direction' => 'down', 'value' => 4]" color="purple"
                icon="<svg class='w-8 h-8 text-purple-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'/></svg>" />

            <x-stat-card title="Conversion" value="3.2%" subtitle="Rate" :trend="['direction' => 'up', 'value' => 2]" color="indigo"
                icon="<svg class='w-8 h-8 text-indigo-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'/></svg>" />
        </div>

        <!-- Summary Cards Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-summary-card title="Financial Summary" color="blue"
                icon="<svg class='w-5 h-5 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>"
                :items="[
                    ['label' => 'Total Revenue', 'value' => 'RM 125,450'],
                    ['label' => 'Total Expenses', 'value' => 'RM 89,230'],
                    ['label' => 'Net Profit', 'value' => 'RM 36,220'],
                    ['label' => 'Profit Margin', 'value' => '28.8%'],
                ]" :action="['label' => 'View Report', 'url' => '#']" />

            <x-summary-card title="System Status" color="green"
                icon="<svg class='w-5 h-5 text-white' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zm6-3a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z' clip-rule='evenodd'/></svg>"
                :items="[
                    ['label' => 'API Status', 'value' => '✓ Operational'],
                    ['label' => 'Database', 'value' => '✓ Healthy'],
                    ['label' => 'Cache Server', 'value' => '✓ Running'],
                    ['label' => 'Backups', 'value' => '✓ Latest 2hrs ago'],
                ]" />
        </div>

        <!-- Data Table -->
        @php
            $users = [
                [
                    'id' => 1,
                    'name' => 'Ahmad Bin Hasan',
                    'email' => 'ahmad@example.com',
                    'status' => 'active',
                    'joined' => '2024-01-15 10:30:00',
                ],
                [
                    'id' => 2,
                    'name' => 'Fatimah Binti Ali',
                    'email' => 'fatimah@example.com',
                    'status' => 'active',
                    'joined' => '2024-02-20 14:15:00',
                ],
                [
                    'id' => 3,
                    'name' => 'Muhammad Ismail',
                    'email' => 'ismail@example.com',
                    'status' => 'pending',
                    'joined' => '2024-03-10 09:45:00',
                ],
                [
                    'id' => 4,
                    'name' => 'Siti Nurhaliza',
                    'email' => 'siti@example.com',
                    'status' => 'inactive',
                    'joined' => '2024-01-05 11:20:00',
                ],
                [
                    'id' => 5,
                    'name' => 'Hassan Mohd',
                    'email' => 'hassan@example.com',
                    'status' => 'active',
                    'joined' => '2024-03-15 16:30:00',
                ],
            ];
        @endphp

        <x-data-table title="Recent Users" :columns="[
            ['key' => 'name', 'label' => 'Name', 'type' => 'avatar'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
            ['key' => 'joined', 'label' => 'Joined Date', 'type' => 'date'],
        ]" :rows="$users" :actions="[
            ['type' => 'link', 'label' => 'Edit', 'url' => fn($row) => route('admin.users.edit', $row['id'])],
            ['type' => 'button', 'label' => 'Delete'],
        ]" />
    </div>

    <script>
        // Initialize admin layout data
        document.addEventListener('Alpine.init', () => {
            Alpine.store('admin', {
                modals: {
                    createUser: false,
                }
            });
        });
    </script>
</x-app-layout>
