@props(['type' => 'priority', 'value'])
@php
    $styles = [
        'priority' => [
            'low'      => 'bg-ink-100 text-ink-700',
            'medium'   => 'bg-sky-100 text-sky-800',
            'high'     => 'bg-amber-100 text-amber-900',
            'critical' => 'bg-flare text-white',
        ],
        'status' => [
            'todo'        => 'bg-ink-100 text-ink-700',
            'in_progress' => 'bg-sky-100 text-sky-800',
            'done'        => 'bg-emerald-100 text-emerald-800',
        ],
    ];
    $labels = $type === 'priority' ? \App\Models\Task::PRIORITIES : \App\Models\Task::STATUSES;
@endphp
<span {{ $attributes->class(['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium', $styles[$type][$value] ?? 'bg-ink-100 text-ink-700']) }}>{{ $labels[$value] ?? $value }}</span>
