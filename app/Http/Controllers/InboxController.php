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

    // 1. Mark as read
    $user->notificationsReceived()->where('is_read', false)->update(['is_read' => true]);

    // 2. Fetch with FULL post details needed for ViewPost
    $notifications = $user->notificationsReceived()
        ->with([
            'sender:id,name', 
            'paws.user',     // Needed for "Unknown User" fix
            'paws.photos',   // Needed for Image Gallery
            'paws.reactions' // Needed for Like status
        ])
        ->latest() // Standard newest first
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
