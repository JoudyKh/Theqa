<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use App\Events\SubscriptionRejectedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\SubscriptionRejectedNotification;

class SubscriptionRejectedListener
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
    public function handle(SubscriptionRejectedEvent $event): void
    {
        $data = $event->data;
        $to = $event->user;
        
        if ($to) {
            $notification = new SubscriptionRejectedNotification();
            $notification->setAttribute('data', $data);
            $notification->setAttribute('user', $to);
            $notification->notify($to);
        }
    }
}
