<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Broadcasting\DatabaseChannel;
use App\Broadcasting\FirebaseChannel;
use Illuminate\Notifications\Messages\MailMessage;

class SectionExamAttachNotification extends BaseNotification
{
    use Queueable;
    const STATE = 6;
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

    public function toDatabase(): array
    {
        return [
            'state' => $this->getAttribute('data')['state'],
            'params' => json_encode($this->getAttribute('data')['params'] ?? []),
            'clickable' => $this->getAttribute('data')['clickable'],
        ];
    }

    public function getTitle()
    {
        return __('messages.section_exam_attach_title');
    }

    public function getBody()
    {
        $params = $this->getParams();

        $examName = $params['exam']['name'] ;
        $sectionName = $params['section']['name'] ;

        return __('messages.section_exam_attach_body' , [
            'exam_name' => $examName ,
            'section_name' => $sectionName ,
        ]);
    }
}
