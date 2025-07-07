<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

/**
 * @OA\Info(title="API Documentation", version="1.0.0")
 */
class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     summary="Liste des notifications de l'utilisateur connecté",
     *     tags={"Notification"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Liste des notifications")
     * )
     */
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();
        return response()->json($notifications);
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/{id}/read",
     *     summary="Marquer une notification comme lue",
     *     tags={"Notification"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Notification marquée comme lue")
     * )
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)->findOrFail($id);
        $notification->lu = true;
        $notification->save();
        return response()->json(['message' => 'Notification marquée comme lue']);
    }
}
