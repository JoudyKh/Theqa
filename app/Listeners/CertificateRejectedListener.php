<?php

namespace App\Listeners;

use App\Events\CertificateRejectedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\CertificateRejectedNotification;

class CertificateRejectedListener
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
    public function handle(CertificateRejectedEvent $event): void
    {
        $data = $event->data;
        $to = $event->user;
        
        if ($to) {
            $notification = new CertificateRejectedNotification();
            $notification->setAttribute('data', $data);
            $notification->setAttribute('user', $to);
            $notification->notify($to);
        }
    }
}
