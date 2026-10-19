{{-- Expects: $tasks (collection), $manage (bool: show assignee + edit/delete) --}}
<div class="overflow-x-auto rounded-lg border border-ink-200 bg-white">
    <table class="w-full min-w-[720px] text-left text-sm">
        <thead class="border-b border-ink-200 bg-ink-50 text-ink-500">
            <tr>
                <th class="px-4 py-3 font-medium">Task</th>
                @if ($manage)<th class="px-4 py-3 font-medium">Assigned to</th>@endif
                <th class="px-4 py-3 font-medium">Priority</th>
                <th class="px-4 py-3 font-medium">Due</th>
                <th class="px-4 py-3 font-medium">Status</th>
                @if ($manage)<th class="px-4 py-3"><span class="sr-only">Actions</span></th>@endif
            </tr>
        </thead>
        <tbody class="divide-y divide-ink-100">
            @forelse ($tasks as $task)
                <tr>
                    <td class="px-4 py-3">
                        <p class="font-medium">{{ $task->title }}</p>
                        <p class="text-xs text-ink-500">{{ \App\Models\Task::CATEGORIES[$task->category] ?? $task->category }}</p>
                    </td>
                    @if ($manage)
                        <td class="px-4 py-3">{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                    @endif
                    <td class="px-4 py-3"><x-badge type="priority" :value="$task->priority" /></td>
                    <td class="px-4 py-3 {{ $task->isOverdue() ? 'font-medium text-flare' : '' }}">
                        @if ($task->due_date)
                            {{ $task->due_date->isToday() ? 'Today' : $task->due_date->format('j M') }}
                            @if ($task->isOverdue())<span class="block text-xs">Overdue</span>@endif
                        @else
                            <span class="text-ink-500">No date</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('tasks.status', $task) }}">
                            @csrf @method('PATCH')
                            <label class="sr-only" for="status-{{ $task->id }}">Status for {{ $task->title }}</label>
                            <select id="status-{{ $task->id }}" name="status" onchange="this.form.submit()"
                                    class="rounded-md border border-ink-200 bg-white px-2 py-1 text-sm">
                                @foreach (\App\Models\Task::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @selected($task->status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    @if ($manage)
                        @php
                            $payload = [
                                'id' => $task->id, 'title' => $task->title, 'description' => $task->description,
                                'category' => $task->category, 'priority' => $task->priority, 'status' => $task->status,
                                'due_date' => $task->due_date?->format('Y-m-d'), 'assigned_to' => $task->assigned_to,
                            ];
                        @endphp
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <button type="button" data-task="{{ json_encode($payload) }}" onclick="openTaskModal(this)"
                                    class="rounded px-2 py-1 text-signal-dark hover:bg-ink-100">Edit</button>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline"
                                  data-confirm="Delete this task? This cannot be undone.">
                                @csrf @method('DELETE')
                                <button class="rounded px-2 py-1 text-flare hover:bg-ink-100">Delete</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $manage ? 6 : 4 }}" class="px-4 py-10 text-center text-ink-500">
                        Nothing here yet. Tasks that match will show up in this list.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
