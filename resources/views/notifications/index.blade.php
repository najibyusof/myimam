<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-400">Notification Center</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">In-App Notifications</h2>
                <p class="mt-1 text-sm text-slate-500">Track updates, review categories, and manage read status.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">
                    {{ $unreadCount }} unread
                </span>
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Mark all as read
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[linear-gradient(180deg,_#f8fafc_0%,_#eef2ff_100%)]">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <form method="GET" class="grid gap-3 md:grid-cols-[200px_minmax(0,1fr)_auto]">
                    <select name="status"
                        class="rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all" @selected($status === 'all')>All statuses</option>
                        <option value="unread" @selected($status === 'unread')>Unread</option>
                        <option value="read" @selected($status === 'read')>Read</option>
                    </select>

                    <select name="category"
                        class="rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all" @selected($category === 'all')>All categories</option>
                        @foreach ($categories as $categoryItem)
                            <option value="{{ $categoryItem['key'] }}" @selected($category === $categoryItem['key'])>
                                {{ $categoryItem['label'] }}
                            </option>
                        @endforeach
                    </select>

                    <div class="flex gap-2">
                        <x-primary-button>Apply</x-primary-button>
                        <a href="{{ route('notifications.index') }}"
                            class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Reset
                        </a>
                    </div>
                </form>
            </section>

            <section class="space-y-3">
                @forelse ($notifications as $notification)
                    <article
                        class="rounded-3xl border {{ $notification['is_read'] ? 'border-slate-200 bg-white' : 'border-sky-200 bg-sky-50/50' }} p-5 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        {{ $notification['category_label'] }}
                                    </span>
                                    <span class="text-xs text-slate-500">{{ $notification['created_at_human'] }}</span>
                                    @if (!$notification['is_read'])
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            New
                                        </span>
                                    @endif
                                </div>

                                <h3 class="mt-2 text-base font-semibold text-slate-900">{{ $notification['title'] }}</h3>
                                <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notification['message'] }}</p>
                            </div>

                            <div class="flex gap-2">
                                @if ($notification['is_read'])
                                    <form method="POST"
                                        action="{{ route('notifications.unread', $notification['id']) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            Mark unread
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('notifications.read', $notification['id']) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="inline-flex items-center rounded-md border border-sky-300 bg-white px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-50">
                                            Mark read
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                        No notifications found for this filter.
                    </div>
                @endforelse
            </section>

            <div>
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
