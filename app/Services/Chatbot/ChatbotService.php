<?php

namespace App\Services\Chatbot;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Support\ApiLocale;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class ChatbotService
{
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
     * @return array{
     *     user_message: Message,
     *     assistant_message: Message,
     *     reply: array{
     *         content: string,
     *         provider: string,
     *         model: string,
     *         response_time_ms: int,
     *         usage: array<string, mixed>
     *     }
     * }
     */
    public function sendMessage(Conversation $conversation, User $user, string $body, ?string $sourceLanguage = null): array
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

        $reply = $this->requestReply($conversation, $user, $body, $sourceLanguage);

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
     * @return array{content: string, provider: string, model: string, response_time_ms: int, usage: array<string, mixed>}
     */
    public function requestReply(Conversation $conversation, User $user, string $body, ?string $sourceLanguage = null): array
    {
        $baseUrl = (string) config('chatbot.base_url', '');

        if ($baseUrl === '') {
            throw new RuntimeException('Chatbot API base URL is not configured.');
        }

        $payload = [
            'conversation_id' => $conversation->id,
            'provider' => $this->provider(),
            'model' => $this->model(),
            'locale' => ApiLocale::current(),
            'source_language' => ApiLocale::normalize((string) ($sourceLanguage ?: ApiLocale::current())),
            'system_prompt' => $this->systemPrompt(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'account_type' => $user->account_type,
            ],
            'messages' => $this->buildPayloadMessages($conversation, $body),
        ];

        $startedAt = microtime(true);

        try {
            $response = Http::baseUrl($baseUrl)
                ->acceptJson()
                ->asJson()
                ->connectTimeout((int) config('chatbot.connect_timeout', 5))
                ->timeout((int) config('chatbot.timeout', 15))
                ->retry((int) config('chatbot.retry_attempts', 2), (int) config('chatbot.retry_sleep', 250))
                ->post((string) config('chatbot.path', '/chatbot/respond'), $payload)
                ->throw();
        } catch (ConnectionException|RequestException|Throwable $throwable) {
            report($throwable);

            throw new RuntimeException('Unable to generate a chatbot reply right now.');
        }

        $responsePayload = $response->json();

        if (! is_array($responsePayload)) {
            throw new RuntimeException('Chatbot response payload is invalid.');
        }

        $content = $this->extractReplyContent($responsePayload);

        if ($content === '') {
            throw new RuntimeException('Chatbot response did not include a reply.');
        }

        return [
            'content' => $content,
            'provider' => (string) (data_get($responsePayload, 'provider') ?: $this->provider()),
            'model' => (string) (data_get($responsePayload, 'model') ?: $this->model()),
            'response_time_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'usage' => is_array(data_get($responsePayload, 'usage')) ? data_get($responsePayload, 'usage') : [],
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
     * @return array<int, array{role: string, content: string}>
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

    private function provider(): string
    {
        return (string) config('chatbot.provider', 'external-ai');
    }

    private function model(): string
    {
        return (string) config('chatbot.model', 'jobnest-assistant');
    }

    private function systemPrompt(): string
    {
        return (string) config('chatbot.system_prompt', 'You are the JobNest assistant.');
    }
}
