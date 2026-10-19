<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · IT Taskboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-paper font-sans text-ink-900 antialiased">
@php
    $me = auth()->user();
    $unread = $me->unreadNotifications()->count();
    $nav = match ($me->role) {
        'admin'   => [['Overview', 'admin.dashboard'], ['Task board', 'manager.dashboard'], ['Users', 'admin.users.index']],
        'manager' => [['Task board', 'manager.dashboard']],
        default   => [['My tasks', 'staff.dashboard']],
    };
@endphp

<aside class="bg-ink-900 text-white lg:fixed lg:inset-y-0 lg:flex lg:w-60 lg:flex-col">
    <div class="flex items-center justify-between gap-4 px-5 py-4 lg:block lg:py-6">
        <a href="{{ route('dashboard') }}" class="font-display text-xl font-bold tracking-tight">IT Taskboard</a>
        <p class="hidden text-sm text-ink-300 lg:mt-1 lg:block">{{ now()->format('l, j F') }}</p>
    </div>

    <nav class="flex gap-1 overflow-x-auto px-3 pb-3 lg:flex-1 lg:flex-col lg:pb-0" aria-label="Main">
        @foreach ($nav as [$label, $route])
            <a href="{{ route($route) }}"
               @class([
                   'whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium',
                   'bg-signal text-white' => request()->routeIs($route),
                   'text-ink-200 hover:bg-ink-800' => ! request()->routeIs($route),
               ])>{{ $label }}</a>
        @endforeach

        <a href="{{ route('notifications.index') }}"
           @class([
               'flex items-center justify-between gap-3 whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium',
               'bg-signal text-white' => request()->routeIs('notifications.*'),
               'text-ink-200 hover:bg-ink-800' => ! request()->routeIs('notifications.*'),
           ])>
            Alerts
            <span data-bell-count @class(['min-w-5 rounded-full bg-flare px-1.5 py-0.5 text-center text-xs font-semibold text-white', 'hidden' => $unread === 0])>{{ $unread > 99 ? '99+' : $unread }}</span>
        </a>
    </nav>

    <div class="hidden border-t border-ink-800 p-4 lg:block">
        <p class="truncate text-sm font-medium">{{ $me->name }}</p>
        <p class="truncate text-xs text-ink-300">{{ $me->roleLabel() }}@if($me->job_title) · {{ $me->job_title }}@endif</p>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button class="w-full rounded-md border border-ink-700 px-3 py-1.5 text-sm text-ink-100 hover:bg-ink-800">Sign out</button>
        </form>
    </div>
</aside>

<main class="lg:pl-60">
    <div class="flex items-center justify-between gap-3 border-b border-ink-200 bg-white px-5 py-3 lg:hidden">
        <p class="truncate text-sm"><span class="font-medium">{{ $me->name }}</span> <span class="text-ink-500">({{ $me->roleLabel() }})</span></p>
        <div class="flex items-center gap-4">
            <a href="{{ route('notifications.index') }}" class="flex items-center gap-1.5 text-sm font-medium text-signal-dark">
                Alerts
                <span data-bell-count @class(['min-w-5 rounded-full bg-flare px-1.5 py-0.5 text-center text-xs font-semibold text-white', 'hidden' => $unread === 0])>{{ $unread > 99 ? '99+' : $unread }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm font-medium text-signal-dark underline">Sign out</button>
            </form>
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-5 py-8 lg:px-10">
        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                <p class="font-medium">That didn't save. Fix the following and try again:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</main>

@php
    // Once per sign-in (per day) remind the person what needs doing.
    $summary = null;

    if (session('summary_seen_on') !== today()->toDateString()) {
        session(['summary_seen_on' => today()->toDateString()]);
        $s = \App\Support\TaskSummary::for($me);
        $tasksCount = $s['overdue'] + $s['dueToday'];

        if ($tasksCount + $unread > 0) {
            $parts = [];
            if ($s['overdue'])  { $parts[] = '<b style="color:#d9480f">'.$s['overdue'].' overdue</b>'; }
            if ($s['dueToday']) { $parts[] = '<b>'.$s['dueToday'].' due today</b>'; }
            if ($s['boss'] && $s['unassigned']) { $parts[] = $s['unassigned'].' unassigned'; }

            $html = '';
            if ($parts) {
                $html .= '<p>'.($s['boss'] ? 'Across the team: ' : 'You have ').implode(', ', $parts).'.</p>';
            }

            if ($s['items']->isNotEmpty()) {
                $html .= '<ul style="text-align:left;margin-top:12px;padding-left:18px;list-style:disc">';
                foreach ($s['items'] as $t) {
                    $when = $t->due_date->isToday() ? 'due today' : 'overdue since '.$t->due_date->format('j M');
                    $html .= '<li>'.e($t->title).' <small style="color:#5c6e75">('.$when.')</small></li>';
                }
                $html .= '</ul>';
            }

            if ($unread > 0) {
                $html .= '<p style="margin-top:12px">You also have <b>'.$unread.' unread alert'.($unread > 1 ? 's' : '').'</b>.</p>';
            }

            $summary = [
                'icon'   => $s['overdue'] > 0 ? 'warning' : 'info',
                'title'  => $tasksCount === 0 ? 'You have new alerts' : ($s['boss'] ? 'Team tasks need attention' : 'Your tasks for today'),
                'html'   => $html,
                'button' => $tasksCount === 0 ? 'View alerts' : ($s['boss'] ? 'Open task board' : 'Open my tasks'),
                'url'    => $tasksCount === 0
                    ? route('notifications.index', [], false)
                    : route($s['boss'] ? 'manager.dashboard' : 'staff.dashboard', [], false),
            ];
        }
    }
@endphp

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.Swal) return; // the CSS/JS build is not ready yet

        const badges = document.querySelectorAll('[data-bell-count]');
        const icons = { info: 'info', success: 'success', warning: 'warning', danger: 'error' };
        let last = {{ $unread }};

        // 1) Message after saving something (task created, role changed, ...)
        @if (session('status'))
            window.toast('success', @js(session('status')));
        @endif

        // 2) Reminder popup right after signing in
        const summary = @js($summary);
        if (summary) {
            Swal.fire({
                icon: summary.icon,
                title: summary.title,
                html: summary.html,
                showCancelButton: true,
                confirmButtonText: summary.button,
                cancelButtonText: 'Later',
            }).then((result) => {
                if (result.isConfirmed) window.location.href = summary.url;
            });
        }

        // 3) New alerts while the page is open
        function announce(title, message, level, url) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icons[level] || 'info',
                title: title,
                text: message,
                showConfirmButton: false,
                timer: level === 'danger' ? 15000 : 8000,
                timerProgressBar: true,
                didOpen: (el) => {
                    el.style.cursor = 'pointer';
                    el.addEventListener('click', () => { window.location.href = url; });
                },
            });
        }

        async function poll() {
            try {
                const res = await fetch(@js(route('notifications.unread', [], false)), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const data = await res.json();

                badges.forEach((b) => {
                    b.textContent = data.count > 99 ? '99+' : data.count;
                    b.classList.toggle('hidden', data.count === 0);
                });

                const added = data.count - last;
                if (added > 1) {
                    announce(added + ' new alerts', 'Click to read them.', 'info', @js(route('notifications.index', [], false)));
                } else if (added === 1 && data.latest) {
                    announce(data.latest.title, data.latest.message, data.latest.level, data.latest.url);
                }
                last = data.count;
            } catch (e) { /* offline or signed out: ignore */ }
        }

        poll();
        setInterval(poll, 20000);
    });
</script>
</body>
</html>
