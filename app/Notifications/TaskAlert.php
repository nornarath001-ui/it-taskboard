<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class TaskAlert extends Notification
{
    /**
     * @param string $kind    assigned | unassigned | updated | status | due_today | overdue | summary
     * @param string $level   info | success | warning | danger (controls the colour)
     */
    public function __construct(
        public string $kind,
        public string $title,
        public string $message,
        public string $level = 'info',
        public ?int $taskId = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        // Relative link, so it works from any address (localhost or your IP).
        $url = in_array($notifiable->role, ['manager', 'admin'], true)
            ? route('manager.dashboard', [], false)
            : route('staff.dashboard', [], false);

        return [
            'kind'    => $this->kind,
            'title'   => $this->title,
            'message' => $this->message,
            'level'   => $this->level,
            'task_id' => $this->taskId,
            'url'     => $url,
        ];
    }
}
