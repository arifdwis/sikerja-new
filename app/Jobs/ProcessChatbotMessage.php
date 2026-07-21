<?php

namespace App\Jobs;

use App\Models\ChatbotFeedbackLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessChatbotMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $requestId,
        public string $userMessage,
        public array $messages,
        public string $confidence,
        public array $contextIds
    ) {
    }

    public function handle(): void
    {
        $responseTtl = (int) config('services.groq.response_cache_ttl', 900);
        $groqUrl = rtrim(config('services.groq.url', 'https://api.groq.com/openai/v1'), '/');
        $groqModel = config('services.groq.model', 'llama-3.3-70b-versatile');
        $groqApiKey = config('services.groq.api_key');
        $timeout = (int) config('services.groq.timeout', 120);

        try {
            if (empty($groqApiKey)) {
                throw new \RuntimeException('GROQ_API_KEY is not configured.');
            }

            $response = Http::withToken($groqApiKey)->timeout($timeout)->post("{$groqUrl}/chat/completions", [
                'model' => $groqModel,
                'messages' => $this->messages,
                'stream' => false,
                'temperature' => 0.15,
                'top_p' => 0.9,
                'max_completion_tokens' => 500,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['choices'][0]['message']['content'] ?? 'Maaf, saya tidak bisa memproses permintaan Anda saat ini.';

                Cache::put("chatbot:response:{$this->requestId}", [
                    'status' => 'done',
                    'reply' => $reply,
                ], $responseTtl);

                if ($this->shouldLogAsFeedbackCandidate($this->confidence, $reply)) {
                    $this->storeFeedbackCandidate(
                        question: $this->userMessage,
                        answer: $reply,
                        confidence: $this->confidence,
                        contextIds: $this->contextIds,
                        failureReason: $this->confidence === 'rendah' ? 'low_confidence' : null
                    );
                }

                return;
            }

            Cache::put("chatbot:response:{$this->requestId}", [
                'status' => 'failed',
                'reply' => 'Maaf, layanan AI sedang tidak tersedia. Silakan coba lagi nanti.',
            ], $responseTtl);

            $this->storeFeedbackCandidate(
                question: $this->userMessage,
                answer: null,
                confidence: $this->confidence,
                contextIds: $this->contextIds,
                failureReason: 'groq_api_error_' . $response->status()
            );
        } catch (\Throwable $e) {
            Cache::put("chatbot:response:{$this->requestId}", [
                'status' => 'failed',
                'reply' => 'Maaf, layanan AI sedang tidak tersedia. Silakan coba lagi nanti.',
            ], $responseTtl);

            Log::error('Chatbot async error: ' . $e->getMessage());

            $this->storeFeedbackCandidate(
                question: $this->userMessage,
                answer: null,
                confidence: $this->confidence,
                contextIds: $this->contextIds,
                failureReason: 'chatbot_exception'
            );
        }
    }

    private function shouldLogAsFeedbackCandidate(string $confidence, string $reply): bool
    {
        if ($confidence === 'rendah') {
            return true;
        }

        $fallbackIndicators = [
            'data belum tersedia',
            'silakan hubungi bagian kerja sama',
            'tidak ada konteks',
            'tidak dapat saya jawab',
            'maaf, saya tidak bisa',
        ];

        $normalizedReply = Str::lower($reply);
        foreach ($fallbackIndicators as $indicator) {
            if (Str::contains($normalizedReply, $indicator)) {
                return true;
            }
        }

        return false;
    }

    private function storeFeedbackCandidate(
        string $question,
        ?string $answer,
        string $confidence,
        array $contextIds,
        ?string $failureReason
    ): void {
        try {
            ChatbotFeedbackLog::create([
                'question' => $question,
                'answer' => $answer,
                'confidence' => $confidence,
                'context_ids' => $contextIds,
                'status' => 'needs_review',
                'failure_reason' => $failureReason,
                'source' => 'landing_widget',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to store chatbot feedback candidate (async)', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
