<?php

namespace App\Http\Controllers\Api\Chatbot;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chatbot\StoreChatbotMessageRequest;
use App\Models\Conversation;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotMessageController extends Controller
{
    public function __construct(private readonly ChatbotService $chatbotService) {}

    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        abort_unless($conversation->isChatbot(), 404);

        $messages = $this->chatbotService->recentMessages(
            $conversation,
            (int) $request->query('per_page', 30),
        );

        return response()->json([
            'message' => 'Chatbot messages fetched successfully.',
            'data' => $messages,
        ]);
    }

    public function store(StoreChatbotMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        abort_unless($conversation->isChatbot(), 404);

        $result = $this->chatbotService->sendMessage(
            $conversation,
            $request->user(),
            (string) $request->validated('body'),
            $request->validated('source_language'),
        );

        return response()->json([
            'message' => 'Chatbot reply generated successfully.',
            'data' => [
                'conversation' => $conversation->fresh([
                    'participants:id,name,account_type',
                    'lastMessage',
                    'lastMessage.sender:id,name,account_type',
                ]),
                'user_message' => $result['user_message']->load('sender:id,name,account_type'),
                'assistant_message' => $result['assistant_message']->load('sender:id,name,account_type'),
                'reply' => $result['reply'],
            ],
        ], 201);
    }
}
