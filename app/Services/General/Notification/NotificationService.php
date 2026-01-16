<?php

namespace App\Services\General\Notification;

use App\Models\User;
use App\Constants\Constants;
use App\Models\Notification;
use App\Constants\Notifications;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserRecourse;
use App\Http\Resources\NotificationResource;

class NotificationService
{
    protected ?User $user;

    public function __construct()
    {
        $this->user = auth('sanctum')->user();
    }

    public function getAllNotifications($hasRead = null, $countOnly = null, $is_broadcast = null, string|int|null $user_id = null , $paginate = true , $returnJsonResponse = true)
    {
        $extraData = [];

        if ($this->user and !$this->user->hasRole(Constants::ADMIN_ROLE)) {
            $user_id = $this->user->id;
        }

        $notifications = Notification::query()->with(['user']);

        if ($user_id != null) {
            $notifications->where('user_id', $user_id);
            
            $user = User::with(['images', 'roles'])->findOrFail($user_id);
            
            $extraData['user'] = UserRecourse::make($user);
        } else {
            $notifications->with(['user.roles', 'user.images']);
        }

        if ($hasRead !== null) {
            $notifications->where('has_read', $hasRead);
        }

        if ($is_broadcast !== null) {
            $notifications->where('is_broadcast', $is_broadcast);
        }

        if (request()->has('user_role')) {
            $notifications->whereHas('user.roles', function ($query) {
                $query->where('name', request()->input('user_role'));
            });
        }

        $notificationsCountQuery = clone $notifications;
        $notificationsCount = $notificationsCountQuery->where(['has_read' => 0])->count();

        if ($countOnly) {
            return success(['notifications_count' => $notificationsCount]);
        }

        $extraData['notifications_count'] = $notificationsCount ?? 0;

        $notificationsToUpdate = null;

        if (request()->boolean('mark_read')) {
            $notificationsToUpdate = clone $notifications;
        }

        if($paginate){
            $notifications = $notifications->orderByDesc('id')->paginate(config('app.pagination_limit'));
        }else{
            $notifications = $notifications->orderByDesc('id')->get();
        }

        if ($notificationsToUpdate !== null) {
            $notificationsToUpdate->where('has_read', 0)->update(['has_read' => 1]);
        }

        if($returnJsonResponse)
        {
            return success(
                NotificationResource::collection($notifications),
                200,
                $extraData
            );
        }
        
        return [
            'notifications' => NotificationResource::collection($notifications) ,
            'extraData' => $extraData
        ] ;
    }
    public function getNotificationTypeStatistics($hasRead = null)
    {
        $stats = $this->user->notifications();
        if ($hasRead !== null) {
            $stats->where('has_read', $hasRead);
        }
        return $stats->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type');
    }


    public function readAllNotifications()
    {
        return $this->user->notifications()->update(['has_read' => 1]);
    }

    public function pushNotification(string $title, string $description, string $type, $state, User &$user, string|null $modelType, int|string|null $modelId, bool $checkDuplicated = false, string|array|null $additional_data = null, bool $is_broadcast = false, bool $clickable = false)
    {
        $data = [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'state' => $state,
            'model_id' => $modelId,
            'model_type' => $modelType,
            'additional_data' => is_array($additional_data) ? json_encode($additional_data) : $additional_data,
            'is_broadcast' => $is_broadcast,
            'clickable' => $clickable
        ];

        if ($checkDuplicated) {
            $filteredData = array_diff_key($data, array_flip(['title', 'description']));
            $user->notifications()->firstOrCreate($filteredData, $data);
        } else {
            $notification = $user->notifications()->create($data);

            pushFirebaseNotification($user->fcmTokens()->pluck('fcm_token')->toArray(), $title, $description, $notification->toArray());
        }
    }

    public function optimizedPushNotification($state, User &$user, array $params, bool $clickable = false)
    {
        $data = [
            'state' => $state,
            'params' => json_encode($params),
            'clickable' => $clickable,
        ];

        return $notification = $user->notifications()->create($data);
    }



}
