<?php
namespace App\Http\Controllers;

use App\Models\InboxNotification;
use Illuminate\Http\JsonResponse;

class InboxController extends Controller
{
    /**
     * Get all notifications for the authenticated user AND mark them all as read.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        // 1. Mark all unread notifications as read immediately upon viewing the inbox
        $user->notificationsReceived()->where('is_read', false)->update(['is_read' => true]);

        // 2. Fetch all notifications to display (ordered newest first)
        $notifications = $user->notificationsReceived()
            ->with(['sender:id,name', 'paws:paws_id,title'])
            ->get();

        return response()->json($notifications);
    }

    /**
     * Optional: Get only the count of unread notifications for a bell icon badge.
     */
    public function unreadCount(): JsonResponse
    {
        $count = auth()->user()->notificationsReceived()->where('is_read', false)->count();

        return response()->json(['unread_count' => $count]);
    }
}
