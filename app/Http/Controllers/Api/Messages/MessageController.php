<?php

namespace App\Http\Controllers\Api\Messages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Messages\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\Messages\NewMessageReceivedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MessageController extends Controller
{
    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        $isParticipant = $conversation->participants()
            ->where('users.id', $user->id)
            ->exists();

        if (! $isParticipant) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $messages = $conversation->messages()
            ->with('sender:id,name,account_type')
            ->latest()
            ->paginate((int) $request->query('per_page', 30));

        return response()->json([
            'message' => 'Messages fetched successfully.',
            'data' => $messages,
        ]);
    }

    public function store(StoreMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        $isParticipant = $conversation->participants()
            ->where('users.id', $user->id)
            ->exists();

        if (! $isParticipant) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $validated = $request->validated();

        if (empty($validated['body']) && ! $request->hasFile('file')) {
            return response()->json([
                'message' => 'Message body or file is required.',
            ], 422);
        }

        $message = DB::transaction(function () use ($request, $conversation, $user, $validated) {
            $payload = [
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'message_type' => $validated['message_type'] ?? 'text',
                'body' => $validated['body'] ?? null,
            ];

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('messages', 'public');

                $payload['message_type'] = 'file';
                $payload['attachment_path'] = $path;
                $payload['attachment_name'] = $file->getClientOriginalName();
                $payload['attachment_mime_type'] = $file->getClientMimeType();
                $payload['attachment_size'] = $file->getSize();
            }

            $message = Message::create($payload);

            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            return $message;
        });

        $recipients = $conversation->participants()
            ->where('users.id', '!=', $user->id)
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new NewMessageReceivedNotification($message, $user));
        }

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => $message->load('sender:id,name,account_type'),
        ], 201);
    }
}
