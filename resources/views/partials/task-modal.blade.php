{{-- Create / edit task dialog. Expects: $assignees --}}
<dialog id="task-modal" class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-xl bg-white p-0 shadow-xl">
    <form id="task-form" method="POST" action="{{ route('tasks.store') }}" class="p-6">
        @csrf
        <input type="hidden" name="_method" id="task-method" value="POST" disabled>

        <h2 id="task-modal-title" class="font-display text-xl font-bold">New task</h2>

        <div class="mt-5 space-y-4 text-sm">
            <div>
                <label for="t-title" class="mb-1 block font-medium">Title</label>
                <input id="t-title" name="title" required maxlength="190" class="w-full rounded-md border border-ink-200 px-3 py-2">
            </div>
            <div>
                <label for="t-description" class="mb-1 block font-medium">Details <span class="font-normal text-ink-500">(optional)</span></label>
                <textarea id="t-description" name="description" rows="3" maxlength="2000" class="w-full rounded-md border border-ink-200 px-3 py-2"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="t-category" class="mb-1 block font-medium">Category</label>
                    <select id="t-category" name="category" class="w-full rounded-md border border-ink-200 bg-white px-3 py-2">
                        @foreach (\App\Models\Task::CATEGORIES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="t-priority" class="mb-1 block font-medium">Priority</label>
                    <select id="t-priority" name="priority" class="w-full rounded-md border border-ink-200 bg-white px-3 py-2">
                        @foreach (\App\Models\Task::PRIORITIES as $k => $l)<option value="{{ $k }}" @selected($k === 'medium')>{{ $l }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="t-status" class="mb-1 block font-medium">Status</label>
                    <select id="t-status" name="status" class="w-full rounded-md border border-ink-200 bg-white px-3 py-2">
                        @foreach (\App\Models\Task::STATUSES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="t-due" class="mb-1 block font-medium">Due date</label>
                    <input id="t-due" name="due_date" type="date" class="w-full rounded-md border border-ink-200 px-3 py-2">
                </div>
            </div>
            <div>
                <label for="t-assignee" class="mb-1 block font-medium">Assign to</label>
                <select id="t-assignee" name="assigned_to" class="w-full rounded-md border border-ink-200 bg-white px-3 py-2">
                    <option value="">Unassigned</option>
                    @foreach ($assignees as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->roleLabel() }})</option>@endforeach
                </select>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('task-modal').close()" class="rounded-md border border-ink-200 px-4 py-2 text-sm font-medium hover:bg-ink-50">Cancel</button>
            <button id="task-submit" class="rounded-md bg-signal px-4 py-2 text-sm font-medium text-white hover:bg-signal-dark">Create task</button>
        </div>
    </form>
</dialog>

<script>
    const taskModal  = document.getElementById('task-modal');
    const taskForm   = document.getElementById('task-form');
    const taskMethod = document.getElementById('task-method');
    const fields = ['title', 'description', 'category', 'priority', 'status', 'assigned_to'];

    function openNewTask() {
        taskForm.reset();
        taskForm.action = @json(route('tasks.store'));
        taskMethod.disabled = true;
        document.getElementById('task-modal-title').textContent = 'New task';
        document.getElementById('task-submit').textContent = 'Create task';
        taskModal.showModal();
    }

    function openTaskModal(button) {
        const t = JSON.parse(button.dataset.task);
        taskForm.reset();
        taskForm.action = @json(url('/tasks')) + '/' + t.id;
        taskMethod.value = 'PUT';
        taskMethod.disabled = false;
        fields.forEach(f => { taskForm.elements[f].value = t[f] ?? ''; });
        taskForm.elements['due_date'].value = t.due_date ?? '';
        document.getElementById('task-modal-title').textContent = 'Edit task';
        document.getElementById('task-submit').textContent = 'Save changes';
        taskModal.showModal();
    }
</script>
