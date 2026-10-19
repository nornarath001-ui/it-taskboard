<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Role-based redirect after login. */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->homeRoute());
    }

    /** STAFF: only their own tasks. */
    public function staff(Request $request): View
    {
        $user   = $request->user();
        $filter = $request->query('filter', 'today');
        $mine   = Task::where('assigned_to', $user->id);

        $stats = [
            'today'       => (clone $mine)->forToday()->where('status', '!=', 'done')->count(),
            'in_progress' => (clone $mine)->where('status', 'in_progress')->count(),
            'overdue'     => (clone $mine)->overdue()->count(),
            'done_today'  => (clone $mine)->whereDate('completed_at', today())->count(),
        ];

        $tasks = (clone $mine)
            ->with('assignee')
            ->when($filter === 'today', fn ($q) => $q->forToday())
            ->when($filter === 'open', fn ($q) => $q->where('status', '!=', 'done'))
            ->when($filter === 'done', fn ($q) => $q->where('status', 'done'))
            ->byPriority()
            ->orderBy('due_date')
            ->get();

        return view('staff.dashboard', compact('stats', 'tasks', 'filter'));
    }

    /** MANAGER (and admin): whole-team task board. */
    public function manager(Request $request): View
    {
        $stats = [
            'today'       => Task::forToday()->where('status', '!=', 'done')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'overdue'     => Task::overdue()->count(),
            'done_today'  => Task::whereDate('completed_at', today())->count(),
        ];

        $todayTasks = Task::with('assignee')->forToday()->byPriority()->orderBy('due_date')->get();

        $tasks = Task::with('assignee')
            ->when($request->query('q'), fn ($q, $v) => $q->where('title', 'like', '%'.$v.'%'))
            ->when($request->query('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->query('priority'), fn ($q, $v) => $q->where('priority', $v))
            ->when($request->query('assignee'), fn ($q, $v) => $q->where('assigned_to', $v))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $assignees = User::whereIn('role', ['staff', 'manager'])->orderBy('name')->get();

        return view('manager.dashboard', compact('stats', 'todayTasks', 'tasks', 'assignees'));
    }

    /** ADMIN: system overview. */
    public function admin(): View
    {
        $total = Task::count();
        $done  = Task::where('status', 'done')->count();

        $stats = [
            'users'    => User::count(),
            'open'     => Task::where('status', '!=', 'done')->count(),
            'overdue'  => Task::overdue()->count(),
            'rate'     => $total > 0 ? (int) round($done / $total * 100) : 0,
        ];

        $roleCounts = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')->pluck('total', 'role');

        $workload = User::whereIn('role', ['staff', 'manager'])
            ->withCount([
                'tasks as open_count' => fn ($q) => $q->where('status', '!=', 'done'),
                'tasks as done_count' => fn ($q) => $q->where('status', 'done'),
            ])
            ->orderByDesc('open_count')
            ->get();

        $byCategory = Task::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')->orderByDesc('total')->pluck('total', 'category');

        $recent = Task::with('assignee')->latest()->take(6)->get();

        return view('admin.dashboard', compact('stats', 'roleCounts', 'workload', 'byCategory', 'recent'));
    }
}
