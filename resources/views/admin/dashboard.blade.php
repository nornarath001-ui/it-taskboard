@extends('layouts.app')
@section('title', 'Admin overview')

@section('content')
    @php
        $maxOpen = max(1, $workload->max('open_count') ?? 1);
        $maxCat  = max(1, $byCategory->max() ?? 1);
    @endphp

    <header class="mb-6">
        <h1 class="font-display text-3xl font-bold">IT department overview</h1>
        <p class="mt-1 text-ink-500">
            {{ $roleCounts['staff'] ?? 0 }} staff · {{ $roleCounts['manager'] ?? 0 }} managers · {{ $roleCounts['admin'] ?? 0 }} admins
        </p>
    </header>

    <x-stat-strip :items="[
        ['People', $stats['users'], false],
        ['Open tasks', $stats['open'], false],
        ['Overdue', $stats['overdue'], true],
        ['Completion rate', $stats['rate'].'%', false],
    ]" />

    <div class="mt-10 grid gap-8 lg:grid-cols-2">
        <section>
            <h2 class="mb-3 font-display text-xl font-bold">Workload</h2>
            <div class="divide-y divide-ink-100 rounded-lg border border-ink-200 bg-white">
                @forelse ($workload as $person)
                    <div class="px-4 py-3">
                        <div class="flex items-baseline justify-between text-sm">
                            <span class="font-medium">{{ $person->name }}</span>
                            <span class="text-ink-500">{{ $person->open_count }} open · {{ $person->done_count }} done</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-ink-100">
                            <div class="h-2 rounded-full bg-signal" style="width: {{ round($person->open_count / $maxOpen * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-ink-500">No staff yet. Add people on the Users page.</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="mb-3 font-display text-xl font-bold">Tasks by category</h2>
            <div class="divide-y divide-ink-100 rounded-lg border border-ink-200 bg-white">
                @forelse ($byCategory as $cat => $count)
                    <div class="px-4 py-3">
                        <div class="flex items-baseline justify-between text-sm">
                            <span class="font-medium">{{ \App\Models\Task::CATEGORIES[$cat] ?? $cat }}</span>
                            <span class="text-ink-500">{{ $count }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-ink-100">
                            <div class="h-2 rounded-full bg-ink-700" style="width: {{ round($count / $maxCat * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-ink-500">No tasks yet. Create one from the Task board.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="mt-10">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-display text-xl font-bold">Latest tasks</h2>
            <a href="{{ route('manager.dashboard') }}" class="text-sm font-medium text-signal-dark underline">Open task board</a>
        </div>
        @include('partials.task-table', ['tasks' => $recent, 'manage' => false])
    </section>
@endsection
