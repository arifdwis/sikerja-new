<?php

namespace App\Jobs;

use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
        try {
            $service = app(WhatsappService::class);
            $service->sendMessage($this->number, $this->message);
        } catch (\Throwable $e) {
            Log::error('SendWhatsAppJob Email dispatch error: ' . $e->getMessage());
        }
    }
}
