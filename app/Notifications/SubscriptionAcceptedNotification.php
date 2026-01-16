<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Broadcasting\DatabaseChannel;
use App\Broadcasting\FirebaseChannel;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionAcceptedNotification extends BaseNotification
{
    use Queueable;
    const STATE = 3;
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
        return __('messages.subscription_request_accepted_title');
    }

    public function getBody()
    {
        $params = $this->getAttribute('data')['params'] ;
        
        if(is_string($params)){
            $params = json_decode($params , true) ;
        }
    
        $course_name = $params['course']['name'] ;
        
        return __('messages.subscription_request_accepted_body' , [
            'course_name' => $course_name ,
        ]);
    }
}
