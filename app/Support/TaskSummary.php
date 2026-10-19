<?php

namespace App\Support;

use App\Models\Task;
use App\Models\User;

class TaskSummary
{
    /**
     * Counts and the most urgent open tasks for one person.
     * Staff see their own tasks; managers and admins see the whole team.
     */
    public static function for(User $user): array
    {
        $boss = in_array($user->role, ['manager', 'admin'], true);

        $open = Task::query()->where('status', '!=', 'done');
        if (! $boss) {
            $open->where('assigned_to', $user->id);
        }

        return [
            'boss'       => $boss,
            'overdue'    => (clone $open)->overdue()->count(),
            'dueToday'   => (clone $open)->dueToday()->count(),
            'unassigned' => $boss ? (clone $open)->whereNull('assigned_to')->count() : 0,
            'items'      => (clone $open)->forToday()->byPriority()->orderBy('due_date')
                                ->limit(5)->get(['id', 'title', 'priority', 'due_date']),
        ];
    }
}
