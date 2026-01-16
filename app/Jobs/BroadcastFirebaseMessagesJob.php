<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\BroadCastNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class BroadcastFirebaseMessagesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Collection $users,
        protected string $title,
        protected string $body
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new BroadCastNotification())
        ->setAttribute('data' , [
            'state' => BroadCastNotification::STATE ,
            'params' => [
                'title' => $this->title ,
                'body' => $this->body ,
            ] ,
        ])
        ->notify($this->users);
    }
}
