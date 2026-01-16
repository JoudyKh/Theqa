<?php

namespace App\Notifications;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class NotificationService
{

    public function driver($name)
    {
        return new $name;
    }

    public function send($notification, $users, $channel)
    {
        $driver = $this->driver($channel);
        $users = (is_array($users) || $users instanceof Collection) ? $users : [$users];

        foreach ($users as $user) {
            try {
                $driver->send($notification, $user);
            } catch (\Exception $ex) {
                Log::emergency('error sending:');
                Log::emergency(get_class($driver));
                Log::emergency($ex);
            }

        }
    }
}
