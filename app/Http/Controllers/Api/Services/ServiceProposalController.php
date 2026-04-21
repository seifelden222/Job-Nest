<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Services\StoreServiceProposalRequest;
use App\Http\Requests\Api\Services\UpdateServiceProposalRequest;
use App\Models\ServiceProposal;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;

class ServiceProposalController extends Controller
{
    public function index(ServiceRequest $serviceRequest): JsonResponse
    {
        if ((int) $serviceRequest->user_id !== (int) request()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $proposals = $serviceRequest->proposals()
            ->with('user:id,name,email,phone,account_type')
            ->latest()
            ->paginate(15);

        return response()->json([
            'message' => 'Service proposals fetched successfully.',
            'data' => $proposals,
        ]);
    }

    public function store(StoreServiceProposalRequest $request, ServiceRequest $serviceRequest): JsonResponse
    {
        if ($serviceRequest->status !== 'open') {
            return response()->json([
                'message' => 'You can only submit proposals to open service requests.',
            ], 422);
        }

        if ($serviceRequest->proposals()->where('user_id', $request->user()->id)->exists()) {
            return response()->json([
                'message' => 'You already submitted a proposal for this service request.',
            ], 409);
        }

        $proposal = $serviceRequest->proposals()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'status' => 'submitted',
        ]);

        return response()->json([
            'message' => 'Service proposal submitted successfully.',
            'data' => $proposal->load('user:id,name,account_type'),
        ], 201);
    }

    public function show(ServiceProposal $serviceProposal): JsonResponse
    {
        $userId = (int) request()->user()->id;
        $isOwner = (int) $serviceProposal->serviceRequest->user_id === $userId;
        $isProposer = (int) $serviceProposal->user_id === $userId;

        if (! $isOwner && ! $isProposer) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'message' => 'Service proposal fetched successfully.',
            'data' => $serviceProposal->load(['serviceRequest', 'user:id,name,email,phone,account_type']),
        ]);
    }

    public function update(UpdateServiceProposalRequest $request, ServiceProposal $serviceProposal): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $isOwner = (int) $serviceProposal->serviceRequest->user_id === $userId;
        $isProposer = (int) $serviceProposal->user_id === $userId;
        $status = $request->validated('status');

        if ($isProposer) {
            if ($status !== 'withdrawn') {
                return response()->json([
                    'message' => 'Proposer can only withdraw the proposal.',
                ], 422);
            }

            $serviceProposal->update([
                'status' => 'withdrawn',
            ]);

            return response()->json([
                'message' => 'Service proposal withdrawn successfully.',
                'data' => $serviceProposal->fresh(['serviceRequest', 'user:id,name,account_type']),
            ]);
        }

        if (! $isOwner) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        if (! in_array($status, ['accepted', 'rejected'], true)) {
            return response()->json([
                'message' => 'Owner can only accept or reject proposals.',
            ], 422);
        }

        $serviceProposal->update([
            'status' => $status,
        ]);

        if ($status === 'accepted') {
            $serviceProposal->serviceRequest()->update([
                'status' => 'in_progress',
            ]);
        }

        return response()->json([
            'message' => 'Service proposal updated successfully.',
            'data' => $serviceProposal->fresh(['serviceRequest', 'user:id,name,account_type']),
        ]);
    }
}
