<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Broadcasting\DatabaseChannel;
use App\Broadcasting\FirebaseChannel;

class BroadCastNotification extends BaseNotification
{
    use Queueable;
    const STATE = 1;
    protected $via = [
        DatabaseChannel::class,
        FirebaseChannel::class,
    ];

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        parent::__construct();
        //
    }

    public function toDatabase()
    {
        return [
            'state' => $this->getAttribute('data')['state'] ?? 0,
            'params' => json_encode($this->getAttribute('data')['params'] ?? []),
            'clickable' => $this->getAttribute('data')['clickable'] ?? 0,
        ];
    }


    public function getTitle()
    {
        return $this->getParams()['title'];
    }

    public function getBody()
    {
        return $this->getParams()['body'];
    }
}
