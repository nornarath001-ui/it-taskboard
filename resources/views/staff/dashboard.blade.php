@extends('layouts.app')
@section('title', 'My tasks')

@section('content')
    <header class="mb-6">
        <h1 class="font-display text-3xl font-bold">Hi {{ strtok(auth()->user()->name, ' ') }}, here is your day</h1>
        <p class="mt-1 text-ink-500">Tasks due today and anything overdue are listed first.</p>
    </header>

    <x-stat-strip :items="[
        ['To do today', $stats['today'], false],
        ['In progress', $stats['in_progress'], false],
        ['Overdue', $stats['overdue'], true],
        ['Finished today', $stats['done_today'], false],
    ]" />

    <div class="mb-4 mt-8 flex flex-wrap items-center justify-between gap-3">
        <h2 class="font-display text-xl font-bold">Tasks</h2>
        <nav class="flex gap-1 rounded-lg bg-ink-100 p-1 text-sm font-medium" aria-label="Filter tasks">
            @foreach (['today' => 'Today', 'open' => 'All open', 'done' => 'Done'] as $key => $label)
                <a href="{{ route('staff.dashboard', ['filter' => $key]) }}"
                   @class(['rounded-md px-3 py-1.5', 'bg-white shadow-sm' => $filter === $key, 'text-ink-500' => $filter !== $key])>{{ $label }}</a>
            @endforeach
        </nav>
    </div>

    @include('partials.task-table', ['tasks' => $tasks, 'manage' => false])
@endsection
