<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $number;
    public string $message;

    /**
     * Create a new job instance.
     */
    public function __construct(string $number, string $message)
    {
        $this->number = $number;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->waRequest($this->number, $this->message);
    }

    /**
     * Send WhatsApp message via Gateway
     */
    private function waRequest(string $number, string $message): array
    {
        $url = config('services.whatsapp.url');
        $username = config('services.whatsapp.username');
        $password = config('services.whatsapp.password');

        try {
            $client = new Client([
                'verify' => false,
                'timeout' => 15,
                'http_errors' => false,
            ]);

            $res = $client->post($url, [
                'auth' => [$username, $password],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'number' => $number,
                    'message' => $message,
                ],
            ]);

            $status = $res->getStatusCode();
            $bodyRaw = (string) $res->getBody();
            $bodyJson = json_decode($bodyRaw, true);

            Log::info('WhatsApp sent', [
                'number' => $number,
                'status' => $status,
                'success' => $status === 200,
            ]);

            return [
                'success' => $status === 200,
                'status' => $status,
                'body_raw' => $bodyRaw,
                'body_json' => $bodyJson,
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp failed', [
                'number' => $number,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }
}
