<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Liste toutes les notifications de l'utilisateur connecté
     * GET /api/notifications
     */
    public function index(Request $request)
    {
        // notifications() est une relation fournie automatiquement par Laravel
        // quand le modèle User utilise le trait Notifiable (déjà présent)
        $notifications = $request->user()
            ->notifications()       // toutes (lues + non lues)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * Nombre de notifications non lues (pour le badge rouge)
     * GET /api/notifications/unread-count
     */
    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Marquer une notification comme lue
     * PATCH /api/notifications/{id}/read
     */
    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        // read_at = null → non lue | read_at = timestamp → lue
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marquée comme lue.']);
    }

    /**
     * Tout marquer comme lu
     * PATCH /api/notifications/read-all
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Toutes les notifications ont été lues.']);
    }
}