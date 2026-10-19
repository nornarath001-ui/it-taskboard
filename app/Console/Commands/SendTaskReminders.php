<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendTaskReminders extends Command
{
    protected $signature = 'tasks:remind {--force : Send now, even before 08:00 or if already sent today}';

    protected $description = 'Alert people about tasks that are due today or overdue';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        if (! $force && now()->hour < 8) {
            $this->info('Before 08:00, nothing sent.');

            return self::SUCCESS;
        }

        $sent = 0;

        // 1) Each assignee: their own open tasks that are due today or overdue.
        $tasks = Task::with('assignee')
            ->whereNotNull('assigned_to')
            ->where('status', '!=', 'done')
            ->whereDate('due_date', '<=', today())
            ->get();

        foreach ($tasks as $task) {
            if (! $task->assignee || ! $this->claim($task->id, $task->assigned_to, $force)) {
                continue;
            }

            $overdue = $task->due_date->isBefore(today());

            $task->assignee->notify(new TaskAlert(
                kind: $overdue ? 'overdue' : 'due_today',
                title: $overdue ? 'Task overdue' : 'Task due today',
                message: $overdue
                    ? "Overdue since {$task->due_date->format('j M')}: {$task->title}"
                    : "Due today: {$task->title}",
                level: $overdue ? 'danger' : 'warning',
                taskId: $task->id,
            ));
            $sent++;
        }

        // 2) Managers and admins: one daily summary for the whole team.
        $overdueCount = Task::overdue()->count();
        $dueTodayCount = Task::dueToday()->where('status', '!=', 'done')->count();

        if ($overdueCount + $dueTodayCount > 0) {
            foreach (User::whereIn('role', ['manager', 'admin'])->get() as $boss) {
                if (! $this->claim(0, $boss->id, $force)) {
                    continue;
                }

                $boss->notify(new TaskAlert(
                    kind: 'summary',
                    title: 'Daily task summary',
                    message: "{$dueTodayCount} due today, {$overdueCount} overdue across the team.",
                    level: $overdueCount > 0 ? 'warning' : 'info',
                ));
                $sent++;
            }
        }

        $this->info("Sent {$sent} alert(s).");

        return self::SUCCESS;
    }

    /** True the first time a reminder is sent to this person for this task today. */
    private function claim(int $taskId, int $userId, bool $force): bool
    {
        if ($force) {
            return true;
        }

        return DB::table('task_reminder_log')->insertOrIgnore([
            'task_id' => $taskId,
            'user_id' => $userId,
            'sent_on' => today()->toDateString(),
        ]) === 1;
    }
}
