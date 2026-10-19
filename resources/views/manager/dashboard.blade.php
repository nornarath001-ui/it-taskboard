@extends('layouts.app')
@section('title', 'Task board')

@section('content')
    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">Task board</h1>
            <p class="mt-1 text-ink-500">Assign work, track progress and clear what is overdue.</p>
        </div>
        <button type="button" onclick="openNewTask()" class="rounded-md bg-signal px-4 py-2.5 text-sm font-medium text-white hover:bg-signal-dark">New task</button>
    </header>

    <x-stat-strip :items="[
        ['Open today', $stats['today'], false],
        ['In progress', $stats['in_progress'], false],
        ['Overdue', $stats['overdue'], true],
        ['Finished today', $stats['done_today'], false],
    ]" />

    <h2 class="mb-3 mt-10 font-display text-xl font-bold">Today</h2>
    @include('partials.task-table', ['tasks' => $todayTasks, 'manage' => true])

    <h2 class="mb-3 mt-10 font-display text-xl font-bold">All tasks</h2>
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 text-sm">
        <div>
            <label for="f-q" class="mb-1 block font-medium">Search</label>
            <input id="f-q" name="q" value="{{ request('q') }}" placeholder="Task title" class="rounded-md border border-ink-200 bg-white px-3 py-2">
        </div>
        <div>
            <label for="f-status" class="mb-1 block font-medium">Status</label>
            <select id="f-status" name="status" class="rounded-md border border-ink-200 bg-white px-3 py-2">
                <option value="">Any</option>
                @foreach (\App\Models\Task::STATUSES as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="f-priority" class="mb-1 block font-medium">Priority</label>
            <select id="f-priority" name="priority" class="rounded-md border border-ink-200 bg-white px-3 py-2">
                <option value="">Any</option>
                @foreach (\App\Models\Task::PRIORITIES as $k => $l)<option value="{{ $k }}" @selected(request('priority') === $k)>{{ $l }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="f-assignee" class="mb-1 block font-medium">Assigned to</label>
            <select id="f-assignee" name="assignee" class="rounded-md border border-ink-200 bg-white px-3 py-2">
                <option value="">Anyone</option>
                @foreach ($assignees as $u)<option value="{{ $u->id }}" @selected((string) request('assignee') === (string) $u->id)>{{ $u->name }}</option>@endforeach
            </select>
        </div>
        <button class="rounded-md bg-ink-900 px-4 py-2 font-medium text-white hover:bg-ink-700">Apply filters</button>
        @if (request()->hasAny(['q', 'status', 'priority', 'assignee']))
            <a href="{{ route('manager.dashboard') }}" class="py-2 text-signal-dark underline">Clear</a>
        @endif
    </form>

    @include('partials.task-table', ['tasks' => $tasks, 'manage' => true])
    <div class="mt-4">{{ $tasks->links() }}</div>

    @include('partials.task-modal', ['assignees' => $assignees])
@endsection
