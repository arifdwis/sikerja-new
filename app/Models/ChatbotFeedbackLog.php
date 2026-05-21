<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotFeedbackLog extends Model
{
    protected $table = 'chatbot_feedback_logs';

    protected $fillable = [
        'question',
        'answer',
        'confidence',
        'context_ids',
        'status',
        'failure_reason',
        'source',
    ];

    protected $casts = [
        'context_ids' => 'array',
    ];
}
