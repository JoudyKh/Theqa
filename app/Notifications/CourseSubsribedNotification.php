<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Broadcasting\DatabaseChannel;
use App\Broadcasting\FirebaseChannel;
use Illuminate\Notifications\Messages\MailMessage;

class CourseSubsribedNotification extends BaseNotification
{
    use Queueable;
    const STATE = 0;
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
        return __('messages.congratulations', ['name' => $this->getAttribute('user', 'default')->full_name]);
    }

    public function getBody()
    {
        return __('messages.you_have_been_added_to_a_new_course');
    }
}
