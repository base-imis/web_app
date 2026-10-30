<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    // Fetch all notifications
    public function fetch()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $unreadCount = $notifications->where('status', false)->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    // Mark a single notification as read
    public function markAsRead(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($validated['id']);

        $notification->update([
            'status' => true,
            'read_at' => now(),
        ]);

        return response()->json(['message' => 'Notification marked as seen']);
    }

    // Mark all notifications as read
    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->update(['status' => true, 'read_at' => now()]); // Mark as read and update read_at
        return response()->json(['success' => true]);
    }

    // Clear all notifications
    public function clearAll()
    {
        Notification::where('user_id', auth()->id())->delete();
        return response()->json(['success' => true]);
    }

    // Check for unread notifications older than 24 hours
   public function checkUnread(Request $request)
    {
        // Get the current user and their assigned provider
        $userId = auth()->id();
        $currentProviderId = auth()->user()->service_provider_id;

        // Get unread notifications from the last 24 hours
        $cutoff = now()->subHours(24);  // The cutoff time for checking 24 hours

        // Get all notifications for this user and provider
        $staleNotifications = DB::table('notifications as n')
            ->join('fsm.applications as a', 'a.id', '=', 'n.application_id')
            ->where('n.type', 'application_assigned')
            ->where('n.read_at', null)  // Not read
            ->where('n.created_at', '<=', $cutoff)  // Notification created 24 hours ago
            ->where('n.user_id', $userId)  // For the logged-in user
            ->where('a.service_provider_id', $currentProviderId)  // Assigned to current provider
            ->select('a.id as application_id', 'a.service_provider_id', 'n.created_at')
            ->get();

        // Compare today's date with notification's created_at and check if it's more than 24 hours
        foreach ($staleNotifications as $notification) {
            // Get the notification creation date and compare with current date
            $createdAt = Carbon::parse($notification->created_at);
            
            if ($createdAt->diffInHours(now()) >= 24) {
                // If more than 24 hours have passed, trigger reassignment
                $this->reassignApplication($notification);
            }
        }

        return response()->json(['status' => 'success']);
    }

    private function reassignApplication($notification)
    {
        $nextProviderId = $this->getNextServiceProviderId($notification->service_provider_id);
        if (!$nextProviderId) return;

        DB::transaction(function () use ($notification, $nextProviderId) {
            // Get the original notification message for the assigned application
            $originalMessage = $notification->message;

            // Update the application to the next provider
            DB::table('fsm.applications')
                ->where('id', $notification->application_id)
                ->update([
                    'service_provider_id' => $nextProviderId,
                    'updated_at' => now(),
                ]);

            // Notify the old provider (since 24 hours have passed without reading)
            $oldMessage = "Since you have not seen this within 24 hours, this has been assigned to another service provider.";
            $this->notifyProvider($notification->service_provider_id, $notification->application_id, 'application_reassigned', $oldMessage);

            // Notify the new provider (same message as the original "assigned" message)
            $this->notifyProvider($nextProviderId, $notification->application_id, 'application_assigned', $originalMessage);

            // Update the read_at field for the reassigned notifications
            DB::table('notifications')
                ->where('application_id', $notification->application_id)
                ->where('user_id', $notification->user_id)
                ->update(['read_at' => now()]);
        });
    }



    // Get the next service provider in the sequence
    private function getNextServiceProviderId(int $currentProviderId): ?int
    {
        // Your logic to get the next service provider id (as explained earlier)
        // This should return the next provider id in sequence
        // For simplicity, let's assume it returns null if no next provider is found
        return $nextProviderId ?? null;
    }

    // Notify provider (you can replace this with your actual notification sending logic)
    private function notifyProvider($providerId, $applicationId, $type, $message)
    {
        // Create the notification for the provider
        Notification::create([
            'user_id' => $providerId,  // Send to the assigned provider's user_id
            'mode' => 'web',  // Web notification
            'message' => $message,
            'status' => false,  // Not yet read
            'application_id' => $applicationId,
            'type' => $type,
            'read_at' => null,
        ]);
    }
}
