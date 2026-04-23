<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ServiceProposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ServiceConversationController extends Controller
{
    public function store(ServiceProposal $serviceProposal): JsonResponse
    {
        $this->authorize('view', $serviceProposal);

        $user = request()->user();
        $serviceRequest = $serviceProposal->serviceRequest;

        $existing = Conversation::query()
            ->where('type', 'service')
            ->where('service_proposal_id', $serviceProposal->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Conversation already exists.',
                'data' => $existing->load([
                    'participants:id,name,account_type',
                    'lastMessage',
                    'serviceRequest:id,title,user_id',
                    'serviceProposal:id,service_request_id,user_id,status',
                ]),
            ]);
        }

        $conversation = DB::transaction(function () use ($serviceProposal, $serviceRequest, $user) {
            $conversation = Conversation::create([
                'type' => 'service',
                'service_request_id' => $serviceRequest->id,
                'service_proposal_id' => $serviceProposal->id,
                'created_by' => $user->id,
            ]);

            $conversation->participants()->attach([
                $serviceRequest->user_id => ['joined_at' => now()],
                $serviceProposal->user_id => ['joined_at' => now()],
            ]);

            return $conversation;
        });

        return response()->json([
            'message' => 'Service conversation created successfully.',
            'data' => $conversation->load([
                'participants:id,name,account_type',
                'serviceRequest:id,title,user_id',
                'serviceProposal:id,service_request_id,user_id,status',
            ]),
        ], 201);
    }
}
