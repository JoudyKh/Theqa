<?php

namespace App\Listeners;

use App\Events\CertificateAcceptedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\CertificateAcceptedNotification;

class CertificateAcceptedListener
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
    public function handle(CertificateAcceptedEvent $event): void
    {
        $data = $event->data;
        $to = $event->user;
        
        if ($to) {
            $notification = new CertificateAcceptedNotification();
            $notification->setAttribute('data', $data);
            $notification->setAttribute('user', $to);
            $notification->notify($to);
        }
    }
}
