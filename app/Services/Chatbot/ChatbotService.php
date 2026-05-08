<?php

namespace App\Services\Chatbot;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\Ai\ExternalAiClient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ChatbotService
{
    public function __construct(private readonly ExternalAiClient $externalAiClient) {}

    /**
     * @return array{conversation: Conversation, created: bool}
     */
    public function getOrCreateConversation(User $user): array
    {
        $conversation = Conversation::query()
            ->chatbot()
            ->where('created_by', $user->id)
            ->with($this->conversationRelations())
            ->first();

        if ($conversation) {
            return [
                'conversation' => $conversation,
                'created' => false,
            ];
        }

        $conversation = DB::transaction(function () use ($user): Conversation {
            $conversation = Conversation::create([
                'type' => Conversation::TYPE_CHATBOT,
                'created_by' => $user->id,
            ]);

            $conversation->participants()->attach([
                $user->id => ['joined_at' => now()],
            ]);

            return $conversation;
        });

        return [
            'conversation' => $conversation->load($this->conversationRelations()),
            'created' => true,
        ];
    }

    public function listConversations(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Conversation::query()
            ->chatbot()
            ->whereHas('participants', function ($query) use ($user): void {
                $query->where('users.id', $user->id);
            })
            ->with($this->conversationRelations())
            ->orderByDesc('last_message_at')
            ->paginate($perPage);
    }

    public function recentMessages(Conversation $conversation, int $perPage = 30): LengthAwarePaginator
    {
        return $conversation->messages()
            ->with('sender:id,name,account_type')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @return array{user_message: Message, assistant_message: Message, reply: array<string, mixed>}
     */
    public function sendMessage(Conversation $conversation, User $user, string $body, ?string $sourceLanguage = null, int $topN = 5): array
    {
        $this->ensureChatbotConversation($conversation);

        $body = trim($body);

        if ($body === '') {
            throw new RuntimeException('Chatbot message body cannot be empty.');
        }

        $userMessage = DB::transaction(function () use ($conversation, $user, $body): Message {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'message_role' => Message::ROLE_USER,
                'message_type' => 'text',
                'body' => $body,
            ]);

            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            return $message;
        });

        $reply = $this->requestReply($conversation, $user, $body, $sourceLanguage, $topN);

        $assistantMessage = DB::transaction(function () use ($conversation, $reply): Message {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => null,
                'message_role' => Message::ROLE_ASSISTANT,
                'message_type' => 'text',
                'body' => $reply['content'],
            ]);

            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            return $message;
        });

        return [
            'user_message' => $userMessage,
            'assistant_message' => $assistantMessage,
            'reply' => $reply,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function requestReply(Conversation $conversation, User $user, string $body, ?string $sourceLanguage = null, int $topN = 5): array
    {
        $payload = [
            'message' => $body,
            'user_id' => $user->ai_user_id,
            'top_n' => $topN,
            'context' => $this->buildPayloadMessages($conversation, $body),
        ];

        $startedAt = microtime(true);

        $responsePayload = $this->externalAiClient->chat($payload);

        $content = $this->extractReplyContent($responsePayload);

        if ($content === '') {
            throw new RuntimeException('Chatbot response did not include a reply.');
        }

        return [
            'content' => $content,
            'intent' => data_get($responsePayload, 'intent'),
            'type' => data_get($responsePayload, 'type'),
            'specialty' => data_get($responsePayload, 'specialty'),
            'count' => data_get($responsePayload, 'count'),
            'results' => is_array(data_get($responsePayload, 'results')) ? data_get($responsePayload, 'results') : [],
            'follow_up' => data_get($responsePayload, 'follow_up'),
            'confidence' => data_get($responsePayload, 'confidence'),
            'confidence_label' => data_get($responsePayload, 'confidence_label'),
            'response_time_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ];
    }

    public function ensureChatbotConversation(Conversation $conversation): void
    {
        if (! $conversation->isChatbot()) {
            throw new RuntimeException('This conversation is not a chatbot conversation.');
        }
    }

    /**
     * @return array<int, string>
     */
    private function conversationRelations(): array
    {
        return [
            'participants:id,name,account_type',
            'lastMessage',
            'lastMessage.sender:id,name,account_type',
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function buildPayloadMessages(Conversation $conversation, string $body): array
    {
        $historyLimit = max(1, (int) config('chatbot.history_limit', 8));

        $messages = $conversation->messages()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(max(0, $historyLimit - 1))
            ->get()
            ->reverse()
            ->values()
            ->map(function (Message $message): array {
                return [
                    'role' => $this->resolveMessageRole($message),
                    'content' => trim((string) $message->body),
                ];
            })
            ->all();

        $messages[] = [
            'role' => Message::ROLE_USER,
            'content' => trim($body),
        ];

        return $messages;
    }

    private function resolveMessageRole(Message $message): string
    {
        return in_array($message->message_role, [Message::ROLE_USER, Message::ROLE_ASSISTANT, Message::ROLE_SYSTEM], true)
            ? $message->message_role
            : Message::ROLE_USER;
    }

    private function extractReplyContent(array $payload): string
    {
        foreach ([
            'reply',
            'message',
            'data.reply',
            'data.message',
            'data.content',
            'choices.0.message.content',
            'choices.0.text',
        ] as $path) {
            $content = data_get($payload, $path);

            if (is_string($content) && trim($content) !== '') {
                return trim($content);
            }
        }

        return '';
    }
}
