<?php

namespace App\Broadcasting;

use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\MultiChannelNotification;
use App\Services\General\Notification\NotificationService;

class DatabaseChannel
{
    /**
     * Create a new channel instance.
     */

    public function __construct()
    {
        //
    }

    public function send(BaseNotification $notification, User $user)
    {
        $data = $notification->toDatabase();
        $data['type'] = $notification->getType();

        $notification = $user->notifications()->create($data);
                
        return $notification;
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user): array|bool
    {
        return true ;
    }
}
