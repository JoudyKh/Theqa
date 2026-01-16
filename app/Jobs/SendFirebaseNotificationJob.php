<?php

namespace App\Jobs;

use DB;
use Config;
use Closure;
use App\Models\Info;
use App\Traits\HasFcmToken;
use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;

class SendFirebaseNotificationJob implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasFcmToken;

    protected string $token;
    protected string $body;
    protected string $title;
    protected array|null $dataArray;

    /**
     * Create a new job instance.
     */
    public function __construct(string $_token, string $_title, string $_body, array|null $_dataArray = null)
    {
        $this->token = $_token;
        $this->title = $_title;
        $this->body = $_body;
        $this->dataArray = convertArrayToStrings($_dataArray) ;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = $this->sendFirebaseMessage($this->token, $this->title, $this->body, $this->dataArray);

        if( ! app()->isProduction()){
            Log::emergency('fcm response ' . $response);
        }
    }
}
