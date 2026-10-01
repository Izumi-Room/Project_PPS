<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display listing of user's notifications.
     */
    public function index(): View
    {
        $notifications = Auth::user()->appNotifications()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark single notification as read and redirect to its action URL if present.
     */
    public function markAsRead(AppNotification $notification): RedirectResponse
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return back();
    }

    /**
     * Mark all notifications as read for current user.
     */
    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->appNotifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai sudah dibaca.');
    }

    /**
     * API for unread notifications count.
     */
    public function unreadCount(): JsonResponse
    {
        $count = Auth::user()->appNotifications()->unread()->count();

        return response()->json(['unread_count' => $count]);
    }
}
