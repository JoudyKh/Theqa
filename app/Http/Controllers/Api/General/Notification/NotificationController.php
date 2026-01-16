<?php

namespace App\Http\Controllers\Api\General\Notification;

use App\Enums\ViewEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\General\Notification\NotificationService;
use App\Http\Requests\Api\General\Notification\GetAllNotificationRequest;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }
    /**
     * @OA\Get(
     *     path="/notifications",
     *     tags={"App", "App - Notifications" , "App - Auth"},
     *     summary="Get all notifications",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="is_read",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             description="Filter notifications by read status (0: unread, 1: read)"
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="is_broadcast",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             description="Filter notifications by broadcast status (0: not broadcasted, 1: broadcasted)"
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"NEW_REGISTRATION", "NEW_STUDENT_EXAM", "NEW_HOMEWORK_STUDENT", "UPDATE_HOMEWORK_STUDENT", "NEW_ATTENDANCE"},
     *             description="The type of notification"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     * 
     * @OA\Get(
     *     path="/admin/notifications",
     *     tags={"Admin", "Admin - Notifications" , "Admin - Auth"},
     *     summary="Get all notifications",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="is_read",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             description="Filter notifications by read status (0: unread, 1: read)"
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="is_broadcast",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             description="Filter notifications by broadcast status (0: not broadcasted, 1: broadcasted)"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function index(GetAllNotificationRequest $request)
    {        
        return $this->notificationService->getAllNotifications(
            $request->input('is_read', null),
            $request->input('count_only' , null),
            $request->input('is_broadcast', null),
            $request->validated('user_id' , null) ,
        );
    }
}