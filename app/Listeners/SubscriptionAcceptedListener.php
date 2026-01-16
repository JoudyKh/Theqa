<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use App\Events\SubscriptionAcceptedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\SubscriptionAcceptedNotification;

class SubscriptionAcceptedListener
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
    public function handle(SubscriptionAcceptedEvent $event): void
    {
        $data = $event->data;
        $to = $event->user;
        
        if ($to) {
            $notification = new SubscriptionAcceptedNotification();
            $notification->setAttribute('data', $data);
            $notification->setAttribute('user', $to);
            $notification->notify($to);
        }
    }
}
