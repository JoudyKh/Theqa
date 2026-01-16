<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Broadcasting\DatabaseChannel;
use App\Broadcasting\FirebaseChannel;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionRejectedNotification extends BaseNotification
{
    use Queueable;
    const STATE = 4;
    protected $via = [
        FirebaseChannel::class,
        DatabaseChannel::class,
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
            'state' => $this->getAttribute('data')['state'],
            'params' => json_encode($this->getAttribute('data')['params'] ?? []),
            'clickable' => $this->getAttribute('data')['clickable'],
        ];
    }


    public function getTitle()
    {
        return __('messages.certificate_request_rejected_title');
    }

    public function getBody()
    {
        $params = $this->getAttribute('data')['params'] ;
        
        if(is_string($params)){
            $params = json_decode($params , true) ;
        }

        $course_name = $params['course']['name'] ;      

        return __('messages.subscription_request_rejected_body' , [
            'course_name' => $course_name ,
        ]);
    }
}
