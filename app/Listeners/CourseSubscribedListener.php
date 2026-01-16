<?php

namespace App\Listeners;

use App\Events\CourseSubscribedEvent;
use App\Notifications\CourseSubsribedNotification;


class CourseSubscribedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CourseSubscribedEvent $event): void
    {
        $data = $event->data;
        $to = $event->user;
        
        if ($to) {
            $notification = new CourseSubsribedNotification();
            $notification->setAttribute('data', $data);
            $notification->setAttribute('user', $to);
            $notification->notify($to);
        }
    }
}
