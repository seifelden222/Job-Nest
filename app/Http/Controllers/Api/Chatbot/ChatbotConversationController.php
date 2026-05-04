<?php

namespace App\Http\Controllers\Api\Chatbot;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotConversationController extends Controller
{
    public function __construct(private readonly ChatbotService $chatbotService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Conversation::class);

        $conversations = $this->chatbotService->listConversations(
            $request->user(),
            (int) $request->query('per_page', 20),
        );

        return response()->json([
            'message' => 'Chatbot conversations fetched successfully.',
            'data' => $conversations,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Conversation::class);

        $result = $this->chatbotService->getOrCreateConversation($request->user());

        return response()->json([
            'message' => $result['created']
                ? 'Chatbot conversation created successfully.'
                : 'Chatbot conversation reused successfully.',
            'data' => $result['conversation'],
        ], $result['created'] ? 201 : 200);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        abort_unless($conversation->isChatbot(), 404);

        $conversation->load([
            'participants:id,name,account_type',
            'lastMessage',
            'lastMessage.sender:id,name,account_type',
        ]);

        return response()->json([
            'message' => 'Chatbot conversation fetched successfully.',
            'data' => $conversation,
        ]);
    }
}
