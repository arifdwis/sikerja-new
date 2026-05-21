<?php

namespace App\Http\Controllers;

use App\Models\ChatbotFeedbackLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ChatbotFeedbackLogController extends Controller implements HasMiddleware
{
    protected string $title = 'AI Feedback Log';
    protected string $view = 'Backend/Settings/ChatbotFeedback';
    protected string $prefix = 'settings.ai-feedback';

    public static function middleware(): array
    {
        return [
            new Middleware('role:superadmin'),
        ];
    }

    public function index(Request $request)
    {
        $query = ChatbotFeedbackLog::query()->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%")
                    ->orWhere('failure_reason', 'like', "%{$search}%");
            });
        }

        if ($request->filled('confidence')) {
            $query->where('confidence', $request->confidence);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) ($request->input('per_page', 20));
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $datas = $query->paginate($perPage)->withQueryString();

        return Inertia::render("{$this->view}/Index", [
            'datas' => $datas,
            'share' => [
                'title' => $this->title,
                'view' => $this->view,
                'prefix' => $this->prefix,
            ],
            'filters' => $request->only(['search', 'confidence', 'status']),
            'confidenceOptions' => [
                ['label' => 'Tinggi', 'value' => 'tinggi'],
                ['label' => 'Sedang', 'value' => 'sedang'],
                ['label' => 'Rendah', 'value' => 'rendah'],
            ],
            'statusOptions' => [
                ['label' => 'Needs Review', 'value' => 'needs_review'],
                ['label' => 'Converted To FAQ', 'value' => 'converted_to_faq'],
                ['label' => 'Ignored', 'value' => 'ignored'],
            ],
        ]);
    }
}
