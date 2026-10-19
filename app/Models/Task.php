<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    public const STATUSES = [
        'todo'        => 'To do',
        'in_progress' => 'In progress',
        'done'        => 'Done',
    ];

    public const PRIORITIES = [
        'low'      => 'Low',
        'medium'   => 'Medium',
        'high'     => 'High',
        'critical' => 'Critical',
    ];

    public const CATEGORIES = [
        'helpdesk' => 'Helpdesk',
        'network'  => 'Network',
        'hardware' => 'Hardware',
        'software' => 'Software',
        'security' => 'Security',
        'servers'  => 'Servers & cloud',
    ];

    protected $fillable = [
        'title', 'description', 'category', 'priority', 'status',
        'due_date', 'assigned_to', 'created_by', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date'     => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Due today. */
    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereDate('due_date', today());
    }

    /** Past its due date and not finished. */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereDate('due_date', '<', today())->where('status', '!=', 'done');
    }

    /** Today's work: due today, or overdue and still open. */
    public function scopeForToday(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereDate('due_date', today())
              ->orWhere(function (Builder $q2) {
                  $q2->whereDate('due_date', '<', today())->where('status', '!=', 'done');
              });
        });
    }

    /** Critical first, then high, medium, low. */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')");
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->status !== 'done'
            && $this->due_date->isBefore(today());
    }
}
