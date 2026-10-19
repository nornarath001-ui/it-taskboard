<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /** Polled by the browser every 20 seconds to update the bell. */
    public function unread(Request $request): JsonResponse
    {
        $this->sendDailyReminders();

        $user = $request->user();
        $latest = $user->unreadNotifications()->latest()->first();

        return response()->json([
            'count'  => $user->unreadNotifications()->count(),
            'latest' => $latest ? [
                'id'      => $latest->id,
                'title'   => $latest->data['title'] ?? 'Alert',
                'message' => $latest->data['message'] ?? '',
                'level'   => $latest->data['level'] ?? 'info',
                'url'     => route('notifications.open', $latest->id, false),
            ] : null,
        ]);
    }

    /** Mark one alert as read, then go to the page it points to. */
    public function open(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        return redirect(is_string($url) && str_starts_with($url, '/') ? $url : route('dashboard'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    /**
     * No cron needed: the first visitor each day after 08:00 triggers the reminders.
     * (Duplicates are prevented by the task_reminder_log table.)
     */
    private function sendDailyReminders(): void
    {
        if (now()->hour < 8) {
            return;
        }

        if (! Cache::add('task-reminders:'.today()->toDateString(), true, now()->endOfDay())) {
            return;
        }

        try {
            Artisan::call('tasks:remind');
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
