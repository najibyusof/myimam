<!-- Data Table Component with Search & Filter -->
@props([
    'title' => '',
    'columns' => [],
    'rows' => [],
    'searchable' => true,
    'filterable' => false,
    'actions' => null,
])

<div class="bg-white rounded-lg shadow overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 sm:flex sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        </div>
        @if ($searchable)
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="search" placeholder="Search..."
                        @input="$dispatch('table-search', $event.target.value)"
                        class="pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 w-64">
                </div>
            </div>
        @endif
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    @foreach ($columns as $column)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                    @if ($actions)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($rows as $row)
                    <tr class="hover:bg-gray-50 transition">
                        @foreach ($columns as $column)
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($column['type'] === 'badge')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800': '{{ $row[$column['key']] }}'
                                            === 'active',
                                            'bg-yellow-100 text-yellow-800': '{{ $row[$column['key']] }}'
                                            === 'pending',
                                            'bg-red-100 text-red-800': '{{ $row[$column['key']] }}'
                                            === 'inactive',
                                            'bg-gray-100 text-gray-800': true
                                        }">
                                        {{ $row[$column['key']] }}
                                    </span>
                                @elseif($column['type'] === 'avatar')
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-sm font-semibold text-indigo-600">
                                            {{ strtoupper(substr($row[$column['key']], 0, 1)) }}
                                        </div>
                                        <span class="ml-2 text-sm text-gray-900">{{ $row[$column['key']] }}</span>
                                    </div>
                                @elseif($column['type'] === 'link')
                                    <a href="{{ $row[$column['link'] ?? 'edit_url'] }}"
                                        class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        {{ $row[$column['key']] }}
                                    </a>
                                @elseif($column['type'] === 'date')
                                    <span
                                        class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($row[$column['key']])->format('d/m/Y H:i') }}</span>
                                @else
                                    <span class="text-sm text-gray-900">{{ $row[$column['key']] }}</span>
                                @endif
                            </td>
                        @endforeach
                        @if ($actions)
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                @foreach ($actions as $action)
                                    @if ($action['type'] === 'link')
                                        <a href="{{ $action['url']($row) }}"
                                            class="text-indigo-600 hover:text-indigo-900 transition">
                                            {{ $action['label'] }}
                                        </a>
                                    @elseif($action['type'] === 'button')
                                        <button {{ $action['attributes'] ?? '' }}
                                            class="text-red-600 hover:text-red-900 transition">
                                            {{ $action['label'] }}
                                        </button>
                                    @endif
                                @endforeach
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + ($actions ? 1 : 0) }}" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-gray-500 text-sm">No data found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
