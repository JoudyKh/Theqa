<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\WhatsAppMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class WhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $details;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        sleep(8);
        $res = $this->sendWhatsAppMessage(
            $this->details['recipient'],
            $this->details['message'],
            $this->details['receiver_id'] ?? null
        );
    }
    public function sendWhatsAppMessage($recipient, $message, $receiver_id = null)
    {
        $encodedMessage = urlencode($message);
        $apiKey = env('WHATSAPP_API_KEY');

        $url = "http://api.textmebot.com/send.php?recipient=$recipient&apikey=$apiKey&text=$encodedMessage&json=yes";
        $response = $this->callEndPointWithCurl($url);
        
        WhatsAppMessage::firstOrCreate([
            'message' => $message,
            'receiver_id' => $receiver_id ?? null,
            'api_response' => $response,
        ]);

        return $response;
    }
    public function callEndPointWithCurl($url)
    {
        $curlHandle = curl_init($url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curlHandle);
        return $response;
    }
}
