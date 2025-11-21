<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get unread notifications for current user
     */
    public function getUnread()
    {
        $userId = Auth::id();
        $notifications = $this->notificationService->getUnreadNotifications($userId, 10);

        return response()->json([
            'success' => true,
            'count' => Notification::getUnreadCount($userId),
            'notifications' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'type' => $n->type,
                'type_icon' => $n->getTypeIcon(),
                'type_color' => $n->getTypeColor(),
                'action_url' => $n->action_url,
                'created_at' => $n->created_at->diffForHumans(),
                'ordem_servico_id' => $n->ordem_servico_id,
            ]),
        ]);
    }

    /**
     * Get all notifications for current user (paginated)
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->query('limit', 20);

        $notifications = $this->notificationService->getAllNotifications($userId, $limit);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $notification = Notification::findOrFail($id);

            // Check authorization
            if ($notification->user_id != Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $this->notificationService->markAsRead($id);

            return response()->json([
                'success' => true,
                'message' => 'Notificação marcada como lida',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $this->notificationService->markAllAsRead(Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Todas as notificações marcadas como lidas',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        try {
            $notification = Notification::findOrFail($id);

            // Check authorization
            if ($notification->user_id != Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $this->notificationService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Notificação deletada',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get notification count
     */
    public function getCount()
    {
        $userId = Auth::id();

        return response()->json([
            'success' => true,
            'unread_count' => Notification::getUnreadCount($userId),
        ]);
    }

    /**
     * Get notifications by type
     */
    public function getByType($type)
    {
        $userId = Auth::id();

        $notifications = Notification::where('user_id', $userId)
            ->where('type', $type)
            ->with('ordemServico')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'type' => $type,
            'data' => $notifications->items(),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
        ]);
    }
}
