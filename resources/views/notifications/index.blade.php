@extends('layouts.app')
@section('title', 'Alerts')

@section('content')
    @php
        $dot = ['info' => 'bg-sky-500', 'success' => 'bg-emerald-500', 'warning' => 'bg-amber-500', 'danger' => 'bg-flare'];
        $unreadTotal = auth()->user()->unreadNotifications()->count();
    @endphp

    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">Alerts</h1>
            <p class="mt-1 text-ink-500">New tasks, status changes and reminders for tasks due today or overdue.</p>
        </div>
        @if ($unreadTotal > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button class="rounded-md border border-ink-200 bg-white px-4 py-2 text-sm font-medium hover:bg-ink-50">Mark all as read ({{ $unreadTotal }})</button>
            </form>
        @endif
    </header>

    <ul class="divide-y divide-ink-100 overflow-hidden rounded-lg border border-ink-200 bg-white">
        @forelse ($notifications as $n)
            @php $unread = $n->read_at === null; @endphp
            <li>
                <a href="{{ route('notifications.open', $n->id) }}"
                   @class(['flex items-start gap-3 px-4 py-3 hover:bg-ink-50', 'bg-ink-50' => $unread])>
                    <span class="mt-1.5 size-2.5 shrink-0 rounded-full {{ $dot[$n->data['level'] ?? 'info'] ?? 'bg-sky-500' }}"></span>
                    <span class="min-w-0 flex-1">
                        <span @class(['block text-sm', 'font-semibold' => $unread, 'font-medium' => ! $unread])>{{ $n->data['title'] ?? 'Alert' }}</span>
                        <span class="block text-sm text-ink-500">{{ $n->data['message'] ?? '' }}</span>
                    </span>
                    <span class="shrink-0 text-xs text-ink-500">{{ $n->created_at->diffForHumans() }}</span>
                </a>
            </li>
        @empty
            <li class="px-4 py-10 text-center text-sm text-ink-500">No alerts yet. You will see them here when a task is assigned to you or is due.</li>
        @endforelse
    </ul>

    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection
