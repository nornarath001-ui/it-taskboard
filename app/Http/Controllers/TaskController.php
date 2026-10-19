<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    private function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category'    => ['required', Rule::in(array_keys(Task::CATEGORIES))],
            'priority'    => ['required', Rule::in(array_keys(Task::PRIORITIES))],
            'status'      => ['required', Rule::in(array_keys(Task::STATUSES))],
            'due_date'    => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ];
    }

    private function completionFields(array $data): array
    {
        $data['completed_at'] = $data['status'] === 'done' ? now() : null;

        return $data;
    }

    /** Send an alert, but never to the person who made the change. */
    private function alert(?User $to, User $actor, string $kind, string $title, string $message, string $level = 'info', ?Task $task = null): void
    {
        if (! $to || $to->is($actor)) {
            return;
        }

        $to->notify(new TaskAlert($kind, $title, $message, $level, $task?->id));
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = $request->user();
        $data = $this->completionFields($request->validate($this->rules()));
        $data['created_by'] = $actor->id;

        $task = Task::create($data);

        $this->alert(
            $task->assignee, $actor, 'assigned', 'New task assigned',
            "{$actor->name} assigned you: {$task->title}",
            $task->priority === 'critical' ? 'danger' : 'info',
            $task
        );

        return back()->with('status', 'Task created.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $actor = $request->user();
        $oldAssigneeId = $task->assigned_to;
        $data = $request->validate($this->rules());

        // Keep the original completion time if the task was already done.
        if ($data['status'] === 'done' && $task->completed_at) {
            $data['completed_at'] = $task->completed_at;
        } else {
            $data = $this->completionFields($data);
        }

        $task->update($data);

        if ($task->wasChanged('assigned_to')) {
            $this->alert(
                $task->assignee, $actor, 'assigned', 'Task assigned to you',
                "{$actor->name} assigned you: {$task->title}",
                $task->priority === 'critical' ? 'danger' : 'info',
                $task
            );

            if ($oldAssigneeId) {
                $this->alert(
                    User::find($oldAssigneeId), $actor, 'unassigned', 'Task reassigned',
                    "\"{$task->title}\" was moved to someone else.", 'info', $task
                );
            }
        } elseif ($task->wasChanged(['title', 'description', 'category', 'priority', 'due_date', 'status'])) {
            $this->alert(
                $task->assignee, $actor, 'updated', 'Task updated',
                "{$actor->name} updated: {$task->title}", 'info', $task
            );
        }

        return back()->with('status', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return back()->with('status', 'Task deleted.');
    }

    /** Staff can change the status of their own tasks; managers and admins can change any. */
    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $task->assigned_to === $user->id || $user->isManager() || $user->isAdmin(),
            403,
            'You can only update tasks assigned to you.'
        );

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Task::STATUSES))],
        ]);

        $oldStatus = $task->status;

        $task->update([
            'status'       => $data['status'],
            'completed_at' => $data['status'] === 'done' ? ($task->completed_at ?? now()) : null,
        ]);

        $label = Task::STATUSES[$data['status']];

        if ($oldStatus !== $data['status']) {
            $done = $data['status'] === 'done';
            $title = $done ? 'Task completed' : 'Task status changed';
            $message = "{$user->name} set \"{$task->title}\" to {$label}.";
            $level = $done ? 'success' : 'info';

            // Staff changing their own task -> tell whoever created it.
            // A manager or admin changing it -> tell the assignee.
            $recipient = $task->assigned_to === $user->id ? $task->creator : $task->assignee;
            $this->alert($recipient, $user, 'status', $title, $message, $level, $task);
        }

        return back()->with('status', 'Status changed to '.$label.'.');
    }
}
