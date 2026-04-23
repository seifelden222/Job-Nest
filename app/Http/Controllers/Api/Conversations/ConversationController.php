<?php

namespace App\Http\Controllers\Api\Conversations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Conversations\StoreConversationRequest;
use App\Models\Application;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Conversation::class);

        $user = $request->user();

        $conversations = Conversation::query()
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with([
                'participants:id,name,account_type',
                'lastMessage',
                'job:id,title,company_id',
                'application:id,job_id,user_id,status',
                'serviceRequest:id,title,user_id,status',
                'serviceProposal:id,service_request_id,user_id,status',
            ])
            ->orderByDesc('last_message_at')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'message' => 'Conversations fetched successfully.',
            'data' => $conversations,
        ]);
    }

    public function store(StoreConversationRequest $request): JsonResponse
    {
        $this->authorize('create', Conversation::class);

        $user = $request->user();
        $type = $request->validated('type');

        if ($type === 'application') {
            return $this->createApplicationConversation($request);
        }

        $otherUserId = (int) $request->validated('participant_id');

        $existing = Conversation::query()
            ->where('type', 'direct')
            ->whereHas('participants', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->whereHas('participants', function ($q) use ($otherUserId) {
                $q->where('users.id', $otherUserId);
            })
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Conversation already exists.',
                'data' => $existing->load(['participants:id,name,account_type', 'lastMessage']),
            ]);
        }

        $conversation = DB::transaction(function () use ($user, $otherUserId) {
            $conversation = Conversation::create([
                'type' => 'direct',
                'created_by' => $user->id,
            ]);

            $conversation->participants()->attach([
                $user->id => ['joined_at' => now()],
                $otherUserId => ['joined_at' => now()],
            ]);

            return $conversation;
        });

        return response()->json([
            'message' => 'Conversation created successfully.',
            'data' => $conversation->load(['participants:id,name,account_type']),
        ], 201);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $conversation->load([
            'participants:id,name,account_type',
            'lastMessage',
            'job:id,title,company_id',
            'application:id,job_id,user_id,status',
            'serviceRequest:id,title,user_id,status',
            'serviceProposal:id,service_request_id,user_id,status',
        ]);

        return response()->json([
            'message' => 'Conversation fetched successfully.',
            'data' => $conversation,
        ]);
    }

    private function createApplicationConversation(StoreConversationRequest $request): JsonResponse
    {
        $user = $request->user();
        $application = Application::query()->with('job')->findOrFail((int) $request->validated('application_id'));

        $this->authorize('createForApplication', [Conversation::class, $application]);

        $existing = Conversation::query()
            ->where('type', 'application')
            ->where('application_id', $application->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Conversation already exists.',
                'data' => $existing->load(['participants:id,name,account_type', 'job:id,title,company_id', 'application:id,job_id,user_id,status']),
            ]);
        }

        $conversation = DB::transaction(function () use ($application, $user) {
            $conversation = Conversation::create([
                'type' => 'application',
                'application_id' => $application->id,
                'job_id' => $application->job_id,
                'created_by' => $user->id,
            ]);

            $conversation->participants()->attach([
                $application->user_id => ['joined_at' => now()],
                $application->job->company_id => ['joined_at' => now()],
            ]);

            return $conversation;
        });

        return response()->json([
            'message' => 'Conversation created successfully.',
            'data' => $conversation->load(['participants:id,name,account_type', 'job:id,title,company_id', 'application:id,job_id,user_id,status']),
        ], 201);
    }
}
